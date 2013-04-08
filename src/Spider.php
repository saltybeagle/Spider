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
        $subUris = $this->getUris($baseUri, $xpath);

        foreach ($this->filters as $filter_class) {
            $subUris = new $filter_class($subUris);
        }

        foreach ($subUris as $subUri) {
            if (!array_key_exists($subUri, $this->visited)) {
                $this->spiderPage(self::getURIBase($subUri), $subUri, $depth + 1);
            }
        }
    }

    protected function getUris($baseUri, DOMXPath $xpath)
    {
        $uris = array();

        $baseHrefNodes = $xpath->query(
            "//xhtml:base/@href"
        );

        if ($baseHrefNodes->length > 0) {
            $baseHref = (string)$baseHrefNodes->item(0)->nodeValue;
        } else {
            $baseHref = '';
        }

        $nodes = $xpath->query(
            "//xhtml:a[@href]/@href | //a[@href]/@href"
        );

        foreach ($nodes as $node) {
            
            $uri = trim((string)$node->nodeValue);

            //trim off hashes
            if (stripos($uri, '#') !== false) {
                $uri = substr($uri, 0, stripos($uri, '#'));

                //Skip if it is now an empty uri, as the will make something in 'test/test.php' with a href like '#' go to 'test/', which it shouldn't.
                if ($uri == '') {
                    continue;
                }
            }

            if (substr($uri, 0, 7) == 'mailto:'
                || substr($uri, 0, 11) == 'javascript:') {
                continue;
            }

            $uri = self::absolutePath($uri, $baseUri);

            if (empty($uri)) {
                continue;
            }

            if ($uri != '.'&& preg_match('!^(https?|ftp)://!i', $uri) === 0) {
                $uri = $baseHref . $uri;
            }

            //Only get sub-pages of the baseuri
            if (strncmp($this->start_base, $uri, strlen($this->start_base)) !== 0) {
                continue;
            }

            //Make sure that we get the final url (it might redirect, and we don't want to crawl pages on another site).
            $urlInfo = self::getURLInfo($uri);

            //Don't check if it 404s or we can't connect.
            if ($urlInfo['http_code'] == 404) {
                continue;
            }

            $uri = $urlInfo['effective_url'];

            //check again, because it might have changed...
            if (strncmp($this->start_base, $uri, strlen($this->start_base)) !== 0) {
                continue;
            }

            $uris[] = $uri;
        }

        sort($uris);

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
    
    public static function absolutePath($relativeUri, $baseUri)
    {

        if (filter_var($relativeUri, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            // URL is already absolute
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
