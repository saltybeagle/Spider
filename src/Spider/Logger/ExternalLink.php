<?php
class Spider_Logger_ExternalLink extends Spider_LoggerAbstract
{
    public function log($uri, $depth, DOMXPath $xpath)
    {
        $links = $this->getLinks($xpath);

        foreach ($links as $link) {
            $this->checkLink($uri, $link);
        }
    }

    protected function getLinks(DOMXPath $xpath)
    {
        $links = array();

        $nodes = $xpath->query(
            "//xhtml:a[@href]/@href | //a[@href]/@href"
        );

        foreach ($nodes as $node) {
            $links[] = (string)$node->nodeValue;
        }

        return $links;
    }

    protected function checkLink($uri, $link)
    {
        $link = spider::absolutePath($link, $uri);
        if ($contents = @file_get_contents($link)) {
            // All ok.
        } else {
            echo "$uri => $link is a broken link!<br>";
        }
    }
}