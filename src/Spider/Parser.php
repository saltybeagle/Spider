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

        //Convert and repair as xhtml
        $tidy     = new tidy;
        $filtered = $tidy->repairString($content, array(
            'output-xhtml' => true, //Ensure that void elements are closed (html5 void elements do not require closing)
            'numeric-entities' => true, //Translate named entities to numeric entities (html5 does not have a dtd and chokes on named entities)
        ));

        $document->loadXML(
            $filtered,
            LIBXML_NOERROR | LIBXML_NONET | LIBXML_NOWARNING
        );

        $xpath = new DOMXPAth($document);
        $xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
        return $xpath;
    }
}