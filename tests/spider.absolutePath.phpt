--TEST--
testing spider::getUriBase()
--FILE--
<?php
require_once dirname(__FILE__) . '/config.sample.php';

$currentURI = 'http://www.example.com/test/index.php';
$baseURI    = 'http://www.example.com/test/';

echo Spider::absolutePath('./test.php', $currentURI, $baseURI) . PHP_EOL;
echo Spider::absolutePath('test.php', $currentURI, $baseURI) . PHP_EOL;
echo Spider::absolutePath('./test/./test.php', $currentURI, $baseURI) . PHP_EOL;
echo Spider::absolutePath('../test.php', $currentURI, $baseURI) . PHP_EOL;
echo Spider::absolutePath('test/test.php', $currentURI, $baseURI) . PHP_EOL;
echo Spider::absolutePath('test/test.php#test', $currentURI, $baseURI) . PHP_EOL;
echo Spider::absolutePath('/test/test.php', $currentURI, $baseURI) . PHP_EOL;
echo Spider::absolutePath('http://www.example.com/path/', $currentURI, $baseURI) . PHP_EOL;
echo Spider::absolutePath('path/?url=directory/../.././stuff', $currentURI, $baseURI) . PHP_EOL;
echo Spider::absolutePath('path/?test=true', $currentURI, $baseURI) . PHP_EOL;
?>
--EXPECT--
http://www.example.com/test/test.php
http://www.example.com/test/test.php
http://www.example.com/test/test/test.php
http://www.example.com/test.php
http://www.example.com/test/test/test.php
http://www.example.com/test/test/test.php#test
http://www.example.com/test/test.php
http://www.example.com/path/
http://www.example.com/test/path/?url=directory/../.././stuff
http://www.example.com/test/path/?test=true
