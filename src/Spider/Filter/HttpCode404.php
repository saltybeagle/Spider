<?php
class Spider_Filter_HttpCode404 extends Spider_UriFilterInterface
{
    function accept()
    {
        $urlInfo = Spider::getURLInfo($this->current());

        //Don't check if it 404s or we can't connect.
        if ($urlInfo['http_code'] == 404) {
            return false;
        }

        return true;
    }
}