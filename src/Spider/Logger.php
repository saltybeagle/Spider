<?php
class Spider_Logger extends Spider_LoggerAbstract
{
    public function log($uri, $depth, DOMXPath $xpath)
    {
        echo $uri . PHP_EOL;
    }
}