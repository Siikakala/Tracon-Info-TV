<?php defined('SYSPATH') OR die('No direct access allowed.');
print"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fi" lang="fi">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="<?php print URL::base('http',true); ?>favicon.gif" />
	<title><?php print __title; ?></title>
	<?php print $css; ?>
	<?php print $js; ?>
</head>
<body>
     <img id="taustakuva" src="imgs/tracon7_tahtitausta_2560x1600_jpeg70.jpg" />
     <div id="swirlhack">
			<div id="swirl"></div>
		</div>
	<div id="main">
		<div id="header">
			<h1><div id="marquee" class="marquee" width="690"></div></h1>
    		<div id="kello"></div>
        </div>
        <div id="text_cont">
    		<div id="text">
    			<?php print $text; ?>
    		</div>
		</div>
		<div id="twitter" style="display:none;">
    		<div id="twitter_cont" style="display:block;position:relative;margin: -30px auto 0 auto">
                <a class="twitter-timeline" data-dnt="true" width="900" height="500" data-chrome="nofooter" href="https://twitter.com/search?q=%23tracon" data-widget-id="348032480055529472">Tweets about "#tracon"</a>
                <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>

            </div>
        </div>
   		<div id="footer">
    		<p><span id="client"></span></p>
		</div>
	</div>
	<div id="foot"></div>
</body>
</html>
