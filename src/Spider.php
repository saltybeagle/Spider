<?php
/**
 * pear2\Spider\Main
 *
 * PHP version 5
 *
 * @category  Yourcategory
 * @package   PEAR2_Spider
 * @author    Your Name <handle@php.net>
 * @copyright 2010 Your Name
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/repository/pear2/PEAR2_Spider
 */

/**
 * Main class for PEAR2_Spider
 *
 * @category  Yourcategory
 * @package   PEAR2_Spider
 * @author    Your Name <handle@php.net>
 * @copyright 2010 Your Name
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/repository/pear2/PEAR2_Spider
 */
class Spider
{
    const MAX_DEPTH = 50;

    protected $loggers = array();
    protected $downloader = null;
    protected $parser = null;
    protected $visited = array();

    public function __construct(
        SpiderDownloader $downloader,
        SpiderParser $parser)
    {
        $this->setDownloader($downloader);
        $this->setParser($parser);
    }

    public function setDownloader(Spider_Downloader $downloader)
    {
        $this->downloader = $downloader;
    }

    public function setParser(Spider_Parser $parser)
    {
        $this->parser = $parser;
    }

    public function addLogger(Spider_Logger $logger)
    {
        if (!in_array($logger, $this->loggers)) {
            $this->loggers[] = $logger;
        }
    }

    public function spider($baseUri)
    {
        $this->spiderPage($baseUri, $baseUri);
    }

    protected function spiderPage($baseUri, $uri, $depth = 1)
    {
        echo $uri, "\n";

        $this->visited[$uri] = true;

        $content = $this->downloader->download($uri);
        $xpath   = $this->parser->parse($content);

        foreach ($this->loggers as $logger) {
            $logger->log($uri, $xpath);
        }

        // spider sub-pages
        if ($depth < self::MAX_DEPTH) {
            $subUris = $this->getUris($baseUri, $xpath);
            foreach ($subUris as $subUri) {
                if (!array_key_exists($subUri, $this->visited)) {
                    $this->spiderPage($baseUri, $subUri, $depth + 1);
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
            "//xhtml:a[@href]/@href"
        );

        foreach ($nodes as $node) {
            $uri = (string)$node->nodeValue;
            if (strncmp($baseUri, $uri, strlen($baseUri)) === 0) {
                $uris[] = $uri;
            } elseif (
                   $uri != '.'
                && preg_match('!^(https?|ftp)://!i', $uri) === 0
            ) {
                $uris[] = $baseHref . $uri;
            }
        }

        return $uris;
    }
}
