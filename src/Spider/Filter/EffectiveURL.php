<?php
class Spider_Filter_EffectiveURL extends Spider_UriFilterInterface
{
    function accept()
    {
        return true;
    }
    
    function current()
    {
        $urlInfo = Spider::getURLInfo(parent::current());

        return $urlInfo['effective_url'];
    }
}