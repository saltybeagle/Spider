<?php
class Spider_Filter_EffectiveURL extends Spider_UriFilterInterface
{
    protected $options = array();

    function __construct(Iterator $iterator, $options = array())
    {
        $this->options = $options;

        parent::__construct($iterator);
    }
    
    function accept()
    {
        return true;
    }
    
    function current()
    {
        $urlInfo = Spider::getURLInfo(parent::current(), $this->options);

        return $urlInfo['effective_url'];
    }
}