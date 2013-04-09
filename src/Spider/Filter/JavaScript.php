<?php
class Spider_Filter_JavaScript extends Spider_UriFilterInterface
{
    function accept()
    {
        if (stripos($this->current(), 'javascript:') !== false) {
            return false;
        }

        return true;
    }
}