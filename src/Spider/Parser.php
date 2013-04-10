<?php
class Spider_Parser implements Spider_ParserInterface
{
    protected $options = array('tidy' => true);

    function __construct($options = array())
    {
        $this->options += $options;
    }

    public function parse($content)
    {
        return $this->getXPath($content);
    }

    protected function getXPath($content)
    {
        $document = new DOMDocument();
        $document->strictErrorChecking = false;

        if ($this->options['tidy'] && extension_loaded('tidy')) {
            //Convert and repair as xhtml
            $tidy     = new tidy;
            $content = $tidy->repairString($content, array(
                'output-xhtml' => true, //Ensure that void elements are closed (html5 void elements do not require closing)
                'numeric-entities' => true, //Translate named entities to numeric entities (html5 does not have a dtd and chokes on named entities)
                'char-encoding' => 'utf8', //Enforce utf8 encoding
                'hide-comments' => true, //don't output comments
            ));
        }

        $document->loadXML(
            $content,
            LIBXML_NOERROR | LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOCDATA
        );

        $xpath = new DOMXPAth($document);
        $xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
        return $xpath;
    }
}