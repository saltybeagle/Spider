<?php
class Spider_Filter_Anchor extends Spider_UriFilterInterface
{
    function accept()
    {
        return true;
    }
    
    function current()
    {
        return preg_replace('/#(.*)/', '', parent::current());
    }
}