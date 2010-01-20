<?php
abstract class Spider_LoggerAbstract
{
    abstract public function log($uri, DOMXPath $xpath);
}