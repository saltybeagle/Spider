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
    const MAX_DEPTH = 50;

    protected $loggers = array();
    protected $filters = array();
    protected $downloader = null;
    protected $parser = null;
    protected $start_base = null;
    protected $visited = array();

    public function __construct(
        Spider_Downloader $downloader,
        Spider_Parser $parser)
    {
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

        $this->visited[$uri] = true;

        $content = $this->downloader->download($uri);
        $xpath   = $this->parser->parse($content, $uri);

        foreach ($this->loggers as $logger) {
            $logger->log($uri, $depth, $xpath);
        }

        // spider sub-pages
        if ($depth < self::MAX_DEPTH) {
            $subUris = $this->getUris($baseUri, $xpath);
            
            foreach ($this->filters as $filter_class) {
                $subUris = new $filter_class($subUris);
            }
            
            foreach ($subUris as $subUri) {
                if (!array_key_exists($subUri, $this->visited)) {
                    try {
                        $this->spiderPage(self::getURIBase($subUri), $subUri, $depth + 1);
                    } catch(Exception $e) {
                        echo "\nThe page, ".$uri.' linked to a page that could not be accessed: ' . $subUri.PHP_EOL;
                    }
                }
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
            
            if (substr($uri, 0, 7) != 'mailto:'
                && substr($uri, 0, 11) != 'javascript:') {
            
                $uri = self::absolutePath($uri, $baseUri);
                
                if (!empty($uri)) {
                    if (strncmp($this->start_base, $uri, strlen($this->start_base)) === 0) {
                        $uris[] = $uri;
                    } elseif (
                           $uri != '.'
                        && preg_match('!^(https?|ftp)://!i', $uri) === 0
                    ) {
                        $uris[] = $baseHref . $uri;
                    }
                }
            }
        }
        
        sort($uris);

        return new Spider_UriIterator($uris);
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
