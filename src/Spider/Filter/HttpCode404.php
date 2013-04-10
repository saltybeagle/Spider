<?php
class Spider_Filter_HttpCode404 extends Spider_UriFilterInterface
{
    protected $options = array();
    
    function __construct(Iterator $iterator, $options = array())
    {
        $this->options = $options;

        parent::__construct($iterator);
    }
    
    function accept()
    {
        $urlInfo = Spider::getURLInfo($this->current(), $this->options);

        //Don't check if it 404s or we can't connect.
        if ($urlInfo['http_code'] == 404) {
            return false;
        }

        return true;
    }
}