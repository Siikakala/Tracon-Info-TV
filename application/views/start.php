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
            <script type="text/javascript">
                new TWTR.Widget({
                  version: 2,
                  type: 'search',
                  rpp: 15,
                  search: '#tracon',
                  interval: 4500,
                  title: '',
                  subject: 'Tracon @ Twitter',
                  width: 'auto',
                  height: 400,
                  theme: {
                    shell: {
                      background: '#880000',
                      color: '#ffffff'
                    },
                    tweets: {
                      background: '#ffffff',
                      color: '#444444',
                      links: '#800000'
                    }
                  },
                  features: {
                    scrollbar: false,
                    loop: true,
                    live: true,
                    hashtags: true,
                    timestamp: true,
                    avatars: true,
                    toptweets: false,
                    behavior: 'default'
                  }

                }).render().start();
            </script>
        </div>
   		<div id="footer">
    		<p><span id="client"></span></p>
		</div>
	</div>
</body>
</html>
