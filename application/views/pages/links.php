<div id="accord">
    <h3><a href="#" class="head-links">TV-ylläpito:</a></h3>
    <div>
        <ul>
            <button name="scroller" value="<?php print $baseurl; ?>admin/face/scroller" class="btn">Scroller</button><br/>
            <button name="rulla" value="<?php print $baseurl; ?>admin/face/rulla" class="btn">Rulla</button><br/>
            <button name="dia" value="<?php print $baseurl; ?>admin/face/dia" class="btn">Diat</button><br/>
            <button name="streams" value="<?php print $baseurl; ?>admin/face/streams" class="btn">Streamit</button><br/>
            <button name="frontends" value="<?php print $baseurl; ?>admin/face/frontends" class="btn">Frontendit</button><br/>
            <button name="video" value="<?php print $baseurl; ?>admin/face/video" class="btn">Videolähetys</button><br/>
        </ul>
    </div>
    <h3><a href="#" class="head-links">Info:</a></h3>
    <div>
        <ul>
            <button name="logi" value="<?php print $baseurl; ?>admin/face/logi" class="btn">Lokikirja</button><br/>
            <?php if(__db == "dev"): ?><button name="lipunmyynti" value="<?php print $baseurl; ?>admin/face/lipunmyynti" class="btn">Lipunmyynti</button><br/><?php endif ?>
            <?php if(__db == "dev"): ?><button name="tiedotteet" value="<?php print $baseurl; ?>admin/face/tiedotteet" class="btn">Tiedotteet</button><br/><?php endif ?>
            <button name="tuotanto" value="<?php print $baseurl; ?>admin/face/tuotanto" class="btn">Tuotantosuunnit.</button><br/>
            <button name="ohjelma" value="<?php print $baseurl; ?>admin/face/ohjelma" class="btn">Ohjelma</button><br/>
            <button name="ohjelma" value="<?php print $baseurl; ?>admin/face/tekstarit" class="btn">Tekstiviestit</button><br/>
        </ul>
    </div>
    <?php if($level >= 3): ?>
    <h3><a href="#" class="head-links">BOFH:</a></h3>
    <div>
        <ul>
            <?php if(__db == "dev"): ?><button name="clients" value="<?php print $baseurl; ?>admin/face/clients" class="btn">Clientit</button><br/> <?php endif ?>
            <button name="users" value="<?php print $baseurl; ?>admin/face/users" class="btn">Käyttäjät</button><br/>
            <button name="settings" value="<?php print $baseurl; ?>admin/face/settings" class="btn">Asetukset</button><br/>
        </ul>
    </div>
    <?php endif ?>
</div>
<br/>
<ul>
    <button name="dashboard" value="<?php print $baseurl; ?>admin/face/dashboard" class="btn">Dashboard</button><br/><br/>
    <button name="logout" value="<?php print $baseurl; ?>admin/logout" class="btn">Kirjaudu ulos</button><br/>
    <button name="infotv" value="<?php print $baseurl; ?>" class="btn">Info-TV</button><br/>
</ul>