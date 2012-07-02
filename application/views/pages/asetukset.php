<p>
Vaihda käytössä oleva datasetti:<?php print $dataset; ?>
</p>
<table class="stats">
 <thead>
     <th class="ui-state-default">Instanssin nimi</th>
     <th class="ui-state-default">Selite</th>
 </thead>
 <?php print $instanssit; ?>
</table>
<button name="add_instance" onclick="$(&quot;#dialog-instance&quot;).dialog(&#039;open&#039;);">Lisää instanssi</button><button name="add_dataset" onclick="$(&quot;#dialog-dataset&quot;).dialog(&#039;open&#039;);">Lisää datasetti</button>