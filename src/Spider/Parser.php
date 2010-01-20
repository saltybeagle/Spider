<?php
class Spider_Parser implements Spider_ParserInterface
{
    public function parse($content)
    {
        return $this->getXPath($content);
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