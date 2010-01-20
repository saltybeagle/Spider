<?php
abstract class Spider_Logger
{
    abstract public function log($uri, DOMXPath $xpath);
}