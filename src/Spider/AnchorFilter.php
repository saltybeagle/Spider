<?php
class Spider_AnchorFilter extends Spider_UriFilterInterface
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