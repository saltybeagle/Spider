<?php
class Spider_Filter_EffectiveURL extends Spider_UriFilterInterface
{
    protected $options = array();

    public function __construct(Iterator $iterator, $options = array())
    {
        $this->options = $options;

        parent::__construct($iterator);
    }

    public function accept()
    {
        return true;
    }

    public function current()
    {
        $urlInfo = Spider::getURLInfo(parent::current(), $this->options);

        return $urlInfo['effective_url'];
    }
}
