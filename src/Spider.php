<?php
/**
 * pear2\Spider\Main
 *
 * PHP version 5
 *
 * @category  Tools
 * @package   PEAR2_Spider
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Brett Bieber <saltybeagle@php.net>
 * @copyright 2010 silverorange Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/repository/pear2/PEAR2_Spider
 */

/**
 * Main class for PEAR2_Spider
 *
 * @category  Tools
 * @package   PEAR2_Spider
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Brett Bieber <saltybeagle@php.net>
 * @author    Michael Fairchild <mfairchild365@gmail.com>
 * @copyright 2010 silverorange Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/repository/pear2/PEAR2_Spider
 */
class Spider
{
    protected $loggers = array();
    protected $filters = array();
    protected $downloader = null;
    protected $parser = null;
    protected $start_base = null;
    protected $visited = array();

    protected $options = array('page_limit' => 500,
                               'max_depth' => 50);

    public function __construct(
        Spider_Downloader $downloader,
        Spider_Parser $parser,
        $options = array())
    {
        $this->options = $options + $this->options;
        $this->setDownloader($downloader);
        $this->setParser($parser);
    }

    public function setDownloader(Spider_Downloader $downloader)
    {
        $this->downloader = $downloader;
    }

    public function setParser(Spider_ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function addLogger(Spider_LoggerAbstract $logger)
    {
        if (!in_array($logger, $this->loggers)) {
            $this->loggers[] = $logger;
        }
    }
    
    public function addUriFilter($filterClass)
    {
        if (!in_array($filterClass, $this->filters)) {
            $this->filters[] = $filterClass;
        }
    }

    public function spider($baseUri)
    {
        if (!filter_var($baseUri, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            throw new Exception('Invalid URI: ' . $baseUri);
        }
        $this->start_base = self::getUriBase($baseUri);
        $this->spiderPage($this->start_base, $baseUri);
    }

    protected function spiderPage($baseUri, $uri, $depth = 1)
    {
        //Stop spidering if we have reached the page_limit
        if ($this->options['page_limit'] > 0 && count($this->visited) >= $this->options['page_limit']) {
            return null;
        }

        $this->visited[$uri] = true;

        try {
            $content = $this->downloader->download($uri);
        } catch(Exception $e) {
            //Couldn't get the page, so don't process it.
            return null;
        }
        
        $xpath = $this->parser->parse($content, $uri);

        foreach ($this->loggers as $logger) {
            $logger->log($uri, $depth, $xpath);
        }

        //Stop spidering if we have reached the max_depth
        if ($depth > $this->options['max_depth']) {
            return;
        }

        //spider sub-pages
        $subUris = $this->getCrawlableUris($baseUri, $uri, $xpath);

        foreach ($this->filters as $filter_class) {
            $subUris = new $filter_class($subUris);
        }

        foreach ($subUris as $subUri) {
            if (!array_key_exists($subUri, $this->visited)) {
                $this->spiderPage(self::getURIBase($subUri), $subUri, $depth + 1);
            }
        }
    }

    public function getCrawlableUris($baseUri, $currentUri, DOMXPath $xpath)
    {
        
        $uris = self::getUris($baseUri, $currentUri, $xpath);
        
        //remove anchors
        $uris = new Spider_filter_Anchor($uris);

        //remove empty uris
        $uris = new Spider_Filter_Empty($uris);

        //remove javascript
        $uris = new Spider_Filter_JavaScript($uris);
        
        //remove mailto links
        $uris = new Spider_Filter_Mailto($uris);
        
        //Filter external links out. (do now to reduce the number of HTTP requests that we have to make)
        $uris = new Spider_Filter_External($uris, $baseUri);
        
        //Filter out pages that returned a 404
        $uris = new Spider_Filter_HttpCode404($uris);
        
        //Get the effective URLs
        $uris = new Spider_Filter_EffectiveURL($uris);

        //Filter external links again as they may have changed due to the effectiveURL filter.
        $uris = new Spider_Filter_External($uris, $baseUri);
        
        return $uris;
    }

    public static function getUris($baseUri, $currentUri, DOMXPath $xpath)
    {
        $uris = array();

        $nodes = $xpath->query(
            "//xhtml:a[@href]/@href | //a[@href]/@href"
        );

        foreach ($nodes as $node) {
            $uri = trim((string)$node->nodeValue);
            $uri = self::absolutePath($uri, $currentUri, $baseUri);

            $uris[] = $uri;
        }

        return new Spider_UriIterator($uris);
    }

    public static function getURLInfo($url)
    {
        static $urls;

        if ($urls == null) {
            $urls = array();
        }

        if (isset($urls[$url])) {
            return $urls[$url];
        }

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        curl_exec($curl);

        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $effectiveURL = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
        $curlErrorNo = curl_errno($curl);

        curl_close($curl);

        $urls[$url] = array('http_code' => $httpStatus,
                            'curl_code' => $curlErrorNo,
                            'effective_url' => $effectiveURL);

        return $urls[$url];
    }
    
    public static function absolutePath($relativeUri, $currentUri, $baseUri)
    {
        if (filter_var($relativeUri, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            // URL is already absolute
            return $relativeUri;
        }
        
        //return the current uri if the relativeUri is an anchor
        if (strpos($relativeUri, '#') === 0) {
            return $currentUri;
        }
        
        $relativeUri_parts = parse_url($relativeUri);
        
        if (isset($relativeUri_parts['scheme']) && !in_array($relativeUri_parts['scheme'], array('http', 'https'))) {
            return $relativeUri;
        }
        
        $new_base_url = $baseUri;
        $base_url_parts = parse_url($baseUri);
        
        if (substr($baseUri, -1) != '/') {
            $path = pathinfo($base_url_parts['path']);
            $new_base_url = substr($new_base_url, 0, strlen($new_base_url)-strlen($path['basename']));
        }
        
        $new_txt = '';
        
        if (substr($relativeUri, 0, 1) == '/') {
            $new_base_url = $base_url_parts['scheme'].'://'.$base_url_parts['host'];
        }
        $new_txt .= $new_base_url;
        
        $absoluteUri = $new_txt.$relativeUri;
        
        // Convert /dir/../ into /
        while (preg_match('/\/[^\/]+\/\.\.\//', $absoluteUri)) {
            $absoluteUri = preg_replace('/\/[^\/]+\/\.\.\//', '/', $absoluteUri);
        }

        
        return $absoluteUri;
    }
    
    public static function getUriBase($uri)
    {
        $base_url_parts = parse_url($uri);

        $new_base_url = $uri;

        if (substr($uri, -1) != '/') {
            $path = pathinfo($base_url_parts['path']);
            $new_base_url = substr($uri, 0, strlen($uri)-strlen($path['basename']));
        }

        return $new_base_url;
    }
}
