--TEST--
testing spider::getUris()
--FILE--
<?php
require_once dirname(__FILE__) . '/config.sample.php';

$parser = new Spider_Parser();
$xpath  = $parser->parse(file_get_contents(dirname(__FILE__) . '/data/baseTag.html'));
$uris   = Spider::getUris('http://wwww.basepage.com/spidertest/directory1/',
                          'http://wwww.basepage.com/spidertest/directory1/baseTag.html',
                          $xpath);

foreach ($uris as $uri) {
    echo $uri . PHP_EOL;
}
?>
--EXPECT--
http://wwww.basepage.com/spidertest/examplePage1.html
http://wwww.basepage.com/