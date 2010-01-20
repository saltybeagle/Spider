<?php
class Spider_Logger extends Spider_LoggerAbstract
{
    public function log($uri, DOMXPath $xpath)
    {
        echo $uri . PHP_EOL;
    }
}