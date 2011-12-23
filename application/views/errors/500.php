<?php defined('SYSPATH') OR die('No direct access allowed.');
print"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fi" lang="fi">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link type="text/css" href="<?php echo URL::site('/', true) ?>css/admin_small.css" rel="stylesheet">
    <title>500, Sisäinen virhe</title>
</head>
<body>
<div id="main" style="width:800px;">
<div id="header"><h1><strong>500</strong> Sisäinen virhe</h1></div>
<div id="text" style="padding:0;margin:25px 25px 25px 25px;border-left:none;">
<p><img src="<?php echo $image; ?>"></p>

<p>Sivulla <?php echo $requested_page; ?> tapahtui sisäinen virhe. Voit yrittää seuraavaa:</p>
<ul>
 <li>Ladata sivun uudelleen</li>
 <li>Palata edelliselle sivulle.</li>
</ul>
<p>Ongelma on kirjattu ja olemme jo tietoisia siitä. Otamme selvää ongelmasta.</p>

<p>Tarkennus: <?php echo $error_message; ?></p>

<p><a href="<?php echo URL::site('/', true) ?>">Jos halusit etusivulle, klikkaa tästä.</a></p>
</div>
</div>
</body>
</html>
