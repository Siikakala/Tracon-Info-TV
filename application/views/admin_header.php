<?php defined('SYSPATH') OR die('No direct access allowed.');
print"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fi" lang="fi">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="<?php print URL::base('http',true); ?>favicon.gif" />
	<title><?php print __title . $title; ?></title>
	<?php print $css; ?>
	<?php print $js; ?>
</head>
<body>
    <div id="help">
        <div id="helpimg">
            <img alt="Halp!" title="Halp!" src="<?php print URL::base('http',true); ?>imgs/question-mark-icon.png" />
        </div>
        <div id="helptext" style="display:none;">
        <?php print $helppi; ?>
        </div>
    </div>
	<div id="main">
		<div id="header">
			<p id="login"><?php print $login; ?></p>
			<p id="show"><?php print $show; ?></p>
			<h1><?php print __title . $title; ?></h1>
			<div id="kello_container"><p id="kello"></p></div>
		</div>
