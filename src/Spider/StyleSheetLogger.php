<?php
class Spider_StyleSheetLogger extends Spider_DbLogger
{
    public function log($uri, DOMXPath $xpath)
    {
        $styles = $this->getStyles($xpath);
        foreach ($styles as $style) {
            $this->saveStyle($uri, $style);
        }
    }

    protected function getStyles(DOMXPath $xpath)
    {
        $styles = array();

        $nodes = $xpath->query(
            "//xhtml:link[@rel='stylesheet' and @type='text/css' and @href]/" .
            "@href"
        );

        foreach ($nodes as $node) {
            $styles[] = (string)$node->nodeValue;
        }

        return $styles;
    }

    protected function saveStyle($uri, $style)
    {
        $statement = $this->db->prepare(
            'insert into SpiderStyleSheet (uri, style) ' .
            'values (:uri, :style)'
        );

        $statement->execute(array($uri, $style));
    }
}