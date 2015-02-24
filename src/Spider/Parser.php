<?php
class Spider_Parser implements Spider_ParserInterface
{
    protected $options = array(
        'tidy' => true,
        'tidy_config' => array(
            'output-xhtml'     => true, //Ensure that void elements are closed (html5 void elements do not require closing)
            'numeric-entities' => true, //Translate named entities to numeric entities (html5 does not have a dtd and chokes on named entities)
            'char-encoding'    => 'utf8', //Enforce utf8 encoding
            'hide-comments'    => true, //don't output comments
            'fix-uri'          => false, //we want the raw values of URLs at this point.  Don't try to fix them.
            'fix-backslash'    => false,//we want the raw values of URLs at this point.  Don't try to fix them.
        ),
    );

    public function __construct($options = array())
    {
        $this->options = array_replace_recursive($this->options, $options);
    }

    public function parse($content)
    {
        return $this->getXPath($content);
    }

    protected function getXPath($content)
    {
        $document = new DOMDocument();
        $document->strictErrorChecking = false;

        if (function_exists('mb_convert_encoding')) {
            //Ensure content is UTF-8, if it isn't, loadXML might not work.
            $content = mb_convert_encoding($content, 'UTF-8');
        }

        if ($this->options['tidy'] && extension_loaded('tidy')) {
            //Convert and repair as xhtml
            $tidy     = new tidy;
            $content = $tidy->repairString($content, $this->options['tidy_config']);
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
