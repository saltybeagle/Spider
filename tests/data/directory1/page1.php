<?php
if (file_exists(dirname(__FILE__) . '/../../config.inc.php')) {
    require_once dirname(__FILE__) . '/../../config.inc.php';
} else {
    require_once dirname(__FILE__) . '/../../config.sample.php';
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <title>directory1/Index</title>
    <base href='http://wwww.basepage.com/spidertest/' />
</head>
<body>
<div>
    These are some links to other pages.
    <a href='<?php echo $baseurl ?>' title='link to baseurl'>baseurl</a>
    <a href='examplePage1.html' title='link to baseurl'>examplepage1</a>
</div>
</body>
</html>

    