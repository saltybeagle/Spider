<?php

function autoload($class)
{
    $class = str_replace('_', '/', $class);
    if (file_exists(dirname(__FILE__) . '/../src/' . $class . '.php')) {
        include dirname(__FILE__) . '/../src/' . $class . '.php';
    }
}

spl_autoload_register("autoload");

$dsn              = 'sqlite:results.db';
$db               = new PDO($dsn);
$pageLogger       = new Spider_PageLogger($db);
$javaScriptLogger = new Spider_JavaScriptLogger($db);
$styleSheetLogger = new Spider_StyleSheetLogger($db);
$downloader       = new Spider_Downloader();
$parser           = new Spider_Parser();
$spider           = new Spider($downloader, $parser);

$spider->addLogger($pageLogger);
$spider->addLogger($styleSheetLogger);
$spider->addLogger($javaScriptLogger);


$spider->spider('http://www.unl.edu/fwc/');
