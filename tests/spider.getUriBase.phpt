--TEST--
testing spider::getUriBase()
--FILE--
<?php
require_once dirname(__FILE__) . '/config.sample.php';

echo Spider::getUriBase('http://www.example.com/test') . PHP_EOL;
echo Spider::getUriBase('http://www.example.com/test/') . PHP_EOL;
echo Spider::getUriBase('http://www.example.com/test/index.html') . PHP_EOL;
echo Spider::getUriBase('http://www.example.com/test/index.php?action=test') . PHP_EOL;
?>
--EXPECT--
http://www.example.com/
http://www.example.com/test/
http://www.example.com/test/
http://www.example.com/test/