<?php
class Spider_Parser
{
    public function parse($content)
    {
        $content = $this->tidy($content);
        return $this->getXPath($content);
    }

    protected function tidy($content)
    {
        $config = array(
            'output-xhtml' => true,
        );

        $tidy = tidy_parse_string($content, $config, 'utf8');
        $tidy->cleanRepair();

        return (string)$tidy;
    }

    protected function getXPath($content)
    {
        $document = new DOMDocument();
        $document->strictErrorChecking = false;
        $document->loadXML(
            $content,
            LIBXML_NOERROR | LIBXML_NONET | LIBXML_NOWARNING
        );
        $xpath = new DOMXPAth($document);
        $xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
        return $xpath;
    }
}