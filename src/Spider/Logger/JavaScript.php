<?php
class Spider_Logger_JavaScript extends Spider_DbLogger
{
    public function log($uri, $depth, DOMXPath $xpath)
    {
        $scripts = $this->getScripts($xpath);
        foreach ($scripts as $script) {
            $this->saveScript($uri, $script);
        }
    }

    protected function getScripts(DOMXPath $xpath)
    {
        $scripts = array();

        $nodes = $xpath->query(
            "//xhtml:script[@type='text/javascript' and @src]/@src"
        );

        foreach ($nodes as $node) {
            $scripts[] = (string)$node->nodeValue;
        }

        return $scripts;
    }

    protected function saveScript($uri, $script)
    {
        $statement = $this->db->prepare(
            'insert into SpiderJavaScript (uri, script) ' .
            'values (:uri, :script)'
        );

        $statement->execute(array($uri, $script));
    }
}