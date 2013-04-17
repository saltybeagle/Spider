--TEST--
testing spider::getCrawlableUris()
--FILE--
<?php
require_once dirname(__FILE__) . '/config.sample.php';

$parser = new Spider_Parser();
$xpath  = $parser->parse(file_get_contents(dirname(__FILE__) . '/data/examplePage1.html'));
$spider = new Spider(new Spider_downloader(), new Spider_parser(), array('respect_robots_txt'=>false));

$uris   = $spider->getCrawlableUris('http://wwww.basepage.com/spidertest/',
                                    'http://wwww.basepage.com/spidertest/',
                                    'http://wwww.basepage.com/spidertest/index.php',
                                    $xpath);

foreach ($uris as $uri) {
    echo $uri . PHP_EOL;
}
?>
--EXPECT--
http://wwww.basepage.com/spidertest/examplePage2.html
http://wwww.basepage.com/spidertest/example/page2.html
http://wwww.basepage.com/spidertest/page1.html
http://wwww.basepage.com/spidertest/directory/
http://wwww.basepage.com/spidertest/index.php
