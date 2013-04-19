<?php
class Spider_Filter_Anchor extends Spider_UriFilterInterface
{
    public function accept()
    {
        return true;
    }

    public function current()
    {
        return preg_replace('/#(.*)/', '', parent::current());
    }
}
