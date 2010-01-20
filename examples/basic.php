<?php

function autoload($class)
{
    $class = str_replace('_', '/', $class);
    if (file_exists(dirname(__FILE__) . '/../src/' . $class . '.php')) {
        include dirname(__FILE__) . '/../src/' . $class . '.php';
    }
}

spl_autoload_register("autoload");

unlink('results.db');

$dsn              = 'sqlite:results.db';
$db               = new PDO($dsn);
$db->exec('create table SpiderPage (
    id serial,
    uri varchar(255),
    primary key(id)
);');
$db->exec(
'create table SpiderJavaScript (
    id serial,
    uri varchar(255),
    script varchar(255),
    primary key(id)
);');

$db->exec('create table SpiderStyleSheet (
    id serial,
    uri varchar(255),
    style varchar(255),
    primary key(id)
);');
//$pageLogger       = new Spider_PageLogger($db);
//$javaScriptLogger = new Spider_JavaScriptLogger($db);
//$styleSheetLogger = new Spider_StyleSheetLogger($db);
$logger           = new Spider_Logger();
$downloader       = new Spider_Downloader();
$parser           = new Spider_Parser();
$spider           = new Spider($downloader, $parser);

$spider->addLogger($logger);
$spider->addUriFilter('Spider_AnchorFilter');
$spider->addUriFilter('Spider_MailtoFilter');
//$spider->addLogger($pageLogger);
//$spider->addLogger($styleSheetLogger);
//$spider->addLogger($javaScriptLogger);


$spider->spider('http://www.unl.edu/fwc/');
