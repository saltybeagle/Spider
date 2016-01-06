--TEST--
testing spider::getUris()
--FILE--
<?php
require_once dirname(__FILE__) . '/config.sample.php';

$parser = new Spider_Parser();
$xpath  = $parser->parse(file_get_contents(dirname(__FILE__) . '/data/examplePage1.html'));
$uris   = Spider::getUris('http://wwww.basepage.com/spidertest/',
                          'http://wwww.basepage.com/spidertest/index.php',
                          $xpath);

foreach ($uris as $uri) {
    echo $uri . PHP_EOL;
}
?>
--EXPECT--
http://www.google.com
https://www.arstechnica.com/
http://wwww.basepage.com/spidertest/examplePage2.html
http://wwww.basepage.com/spidertest/examplePage2.html
http://wwww.basepage.com/spidertest/example/page2.html
http://wwww.basepage.com/index.html
http://wwww.basepage.com/spidertest/page1.html
http://wwww.basepage.com/spidertest/directory/
http://wwww.basepage.com/spidertest/directory1/doNotCrawl.html
http://wwww.basepage.com/spidertest/directory2/doNotCrawlWildcard.html
snapchat://?u=test
http://wwww.basepage.com/spidertest/index.php
mailto:test@example.com
javascript:void(0)
tel:411
http://wwww.basepage.com/spidertest/doNotCrawl.html
