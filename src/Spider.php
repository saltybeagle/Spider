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
 * @author    Michael Fairchild <mfairchild365@gmail.com>
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
    protected $loggers    = array();
    protected $filters    = array();
    protected $downloader = null;
    protected $parser     = null;
    protected $start_base = null;
    protected $visited    = array();

    protected $options = array('page_limit'         => 500,
                               'max_depth'          => 50,
                               'curl_options'       => array(),
                               'crawl_404_pages'    => false,
                               'use_effective_urls' => true);

    public function __construct(
        Spider_Downloader $downloader,
        Spider_Parser $parser,
        $options = array())
    {
        $this->options = $options + $this->options;
        $this->setDownloader($downloader);
        $this->setParser($parser);
    }

    /**
     * Set the downloader object for the spider (used to download pages)
     * 
     * @param Spider_Downloader $downloader
     */
    public function setDownloader(Spider_Downloader $downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * Set the parser object for the spider (used to parse downloaded pages)
     * 
     * @param Spider_ParserInterface $parser
     */
    public function setParser(Spider_ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Add a logger object to the spider
     * 
     * @param Spider_LoggerAbstract $logger
     */
    public function addLogger(Spider_LoggerAbstract $logger)
    {
        if (!in_array($logger, $this->loggers)) {
            $this->loggers[] = $logger;
        }
    }

    /**
     * Add a filter object to the spider
     * Filters are used to filter out pages before attempting to spider them
     * 
     * @param string $filterClass - the class name of the filter
     */
    public function addUriFilter($filterClass)
    {
        if (!in_array($filterClass, $this->filters)) {
            $this->filters[] = $filterClass;
        }
    }

    /**
     * Spider a site
     * Will spider an entire site, including all pages under the baseUri (as long as it is linked to)
     * 
     * @param string $baseUri - The base url for the site
     *
     * @throws Exception
     */
    public function spider($baseUri)
    {
        if (!filter_var($baseUri, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            throw new Exception('Invalid URI: ' . $baseUri);
        }
        $this->start_base = self::getUriBase($baseUri);
        $this->spiderPage($this->start_base, $baseUri);
    }

    /**
     * Spider a specific page
     * 
     * @param string $baseUri - The base url for the page (if http://www.testsite.com/test/index.php,
     *                          it would be http://www.testsite.com/test/)
     * @param string $uri     - The current uri to spider
     * @param int    $depth   - The current recursion depth
     *
     * @return null
     */
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
        $subUris = $this->getCrawlableUris($this->start_base, $baseUri, $uri, $xpath);

        foreach ($this->filters as $filter_class) {
            $subUris = new $filter_class($subUris);
        }

        foreach ($subUris as $subUri) {
            if (!array_key_exists($subUri, $this->visited)) {
                $this->spiderPage(self::getURIBase($subUri), $subUri, $depth + 1);
            }
        }
    }

    /**
     * Get all crawlable uris for a page
     * crawlable uris are URIs that that the spider can crawl
     * 
     * This removes anchors, empty uris, javascipr and mailto calls, external uris, and uris that return a 404
     * 
     * It will also get the effective URLs for a uri (the final url if it redirects)
     * 
     * @param          $startUri   - the base uri for the site
     * @param string   $baseUri    - the base uri for the page
     * @param string   $currentUri - the current uri to get URIs from
     * @param DOMXPath $xpath      - the DOMXPath object for the current uri
     *
     * @return Spider_UriIterator - a list of uris
     */
    public function getCrawlableUris($startUri, $baseUri, $currentUri, DOMXPath $xpath)
    {
        $uris = self::getUris($baseUri, $currentUri, $xpath);
        
        //remove anchors
        $uris = new Spider_Filter_Anchor($uris);

        //remove empty uris
        $uris = new Spider_Filter_Empty($uris);

        //remove javascript
        $uris = new Spider_Filter_JavaScript($uris);
        
        //remove mailto links
        $uris = new Spider_Filter_Mailto($uris);
        
        //Filter external links out. (do now to reduce the number of HTTP requests that we have to make)
        $uris = new Spider_Filter_External($uris, $startUri);
        
        if (!$this->options['crawl_404_pages']) {
            //Filter out pages that returned a 404
            $uris = new Spider_Filter_HttpCode404($uris, $this->options['curl_options']);
        }

        if ($this->options['use_effective_urls']) {
            //Get the effective URLs
            $uris = new Spider_Filter_EffectiveURL($uris, $this->options['curl_options']);

            //Filter external links again as they may have changed due to the effectiveURL filter.
            $uris = new Spider_Filter_External($uris, $startUri);
        }
        
        return $uris;
    }

    /**
     * Returns all valid uris for a page
     * 
     * @param string   $baseUri     - the base uri for the page (NOT the site base)
     * @param string   $currentUri  - the uri of the document
     * @param DOMXPath $xpath       - the xpath for the document
     *
     * @return Spider_UriIterator - a list of uris
     */
    public static function getUris($baseUri, $currentUri, DOMXPath $xpath)
    {
        $uris = array();

        $baseHrefNodes = $xpath->query(
            "//xhtml:base/@href"
        );

        if ($baseHrefNodes->length > 0) {
            $baseUri = (string)$baseHrefNodes->item(0)->nodeValue;
        }

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

    /**
     * Get information about a url.
     *
     * returns an associative array with
     * 'http_code', 'curl_code' and 'effective_url' as keys.
     *
     * @param string $url     - the absolute url to get information for
     * @param array  $options - an associative array of options, including curl options for
     *                          CURLOPT_SSL_VERIFYPEER
     *                          CURLOPT_MAXREDIRS
     *                          CURLOPT_TIMEOUT
     *                          CURLOPT_FOLLOWLOCATION
     *
     * @return array()
     */
    public static function getURLInfo($url, $options = array())
    {
        $options = $options += array(CURLOPT_SSL_VERIFYPEER => false,
                                     CURLOPT_MAXREDIRS      => 5,
                                     CURLOPT_TIMEOUT        => 5,
                                     CURLOPT_FOLLOWLOCATION => true,
                                     CURLOPT_NOBODY         => true);
        
        static $urls;

        if ($urls == null) {
            $urls = array();
        }

        if (isset($urls[$url])) {
            return $urls[$url];
        }

        $curl = curl_init($url);

        curl_setopt_array($curl, $options);

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

    /**
     * Get the absolute path of a uri
     * 
     * @param string $relativeUri - the uri to get the absolute path for
     * @param string $currentUri  - the uri of the page that the $relativeUri was found on
     * @param string $baseUri     - the base uri of the site
     *
     * @return string - absolute uri
     */
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

    /**
     * Get the base uri from a uri
     * 
     * @param string $uri
     *
     * @return string - the base uri
     */
    public static function getUriBase($uri)
    {
        $base_url_parts = parse_url($uri);

        $trimLength = 0;
        
        if (isset($base_url_parts['query'])) {
            $trimLength = strlen($base_url_parts['query']) + 1;  //+1 for the ? chacter
        }
        
        $new_base_url = $uri;

        if (substr($uri, -1) != '/') {
            $path = pathinfo($base_url_parts['path']);
            
            $trimLength += strlen($path['basename']);
            
            $new_base_url = substr($uri, 0, strlen($uri)-$trimLength);
        }

        return $new_base_url;
    }
}
