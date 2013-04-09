--TEST--
testing spider::spider()
--FILE--
<?php
if (file_exists(dirname(__FILE__) . '/config.inc.php')) {
    require_once dirname(__FILE__) . '/config.inc.php';
} else {
    require_once dirname(__FILE__) . '/config.sample.php';
}

$parser = new Spider_Parser();
$xpath  = $parser->parse(file_get_contents(dirname(__FILE__) . '/data/examplePage1.html'));
$spider = new Spider(new Spider_downloader(), new Spider_parser());
$spider->addLogger(new Spider_Logger_Phpt());


$spider->spider($GLOBALS['baseurl']);
?>
--EXPECT--

directory1/
examplePage1.html
directory1/page1.php
