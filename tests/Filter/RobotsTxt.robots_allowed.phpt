--TEST--
testing spider::getCrawlableUris()
--FILE--
<?php
require_once dirname(__FILE__) . '/../config.sample.php';

$robots_txt = new Spider_Filter_RobotsTxt(new ArrayIterator(array()));

//Test multiple domain support for RobotsTxt
echo (string)$robots_txt->robots_allowed('http://example.net/') . PHP_EOL;
echo (string)$robots_txt->robots_allowed('http://example.org/') . PHP_EOL;
?>
--EXPECT--
1
1