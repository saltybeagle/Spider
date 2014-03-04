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
echo Spider::absolutePath('', $currentURI, $baseURI) . PHP_EOL;

$currentURI = 'http://www.example.com/dir1/dir2/dir3/dir4/dir5/dir6/index.php';
$baseURI    = 'http://www.example.com/dir1/dir2/dir3/dir4/dir5/dir6/';

echo Spider::absolutePath('../../../../test.php', $currentURI, $baseURI) . PHP_EOL;
echo Spider::absolutePath('./../../../../test.php', $currentURI, $baseURI) . PHP_EOL;

$currentURI = 'http://www.example.com/';
$baseURI    = 'http://www.example.com/';
echo Spider::absolutePath('//unl.edu/', $currentURI, $baseURI) . PHP_EOL;
$currentURI = 'https://www.example.com/';
$baseURI    = 'https://www.example.com/';
echo Spider::absolutePath('//unl.edu/', $currentURI, $baseURI) . PHP_EOL;
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
http://www.example.com/test/index.php
http://www.example.com/dir1/dir2/test.php
http://www.example.com/dir1/dir2/test.php
http://unl.edu/
https://unl.edu/