<?php
class Spider_Logger_Phpt extends Spider_LoggerAbstract
{
    public function log($uri, $depth, DOMXPath $xpath)
    {
        echo str_ireplace($GLOBALS['baseurl'], '', $uri) . PHP_EOL;
    }
}