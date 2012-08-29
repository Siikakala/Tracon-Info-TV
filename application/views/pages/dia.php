<div id="select">
     <p>Valitse muokattava dia:
         <?php print $select; ?>
         tai <button name="uusi" onclick="return uusi();">Luo uusi</button>
         &nbsp;&nbsp;&nbsp;&nbsp;
         <span id="ident_" style="display:none;">Tunniste:<input type="text" id="ident" name="ident" value="" /></span>
     </p>
</div>
<div id="edit" style="display:none;"></div>
<p>
   Kopioi haluamasi nuoli tästä: → ➜ ➔ ➞ ➨ ➧ ➩ ➭ ➼<br/>
   Voit käyttää [salinnimi-nyt] , [salinnimi-next] ja [aika] -tageja tekstin seassa,
   nyt; mitä tällä hetkellä salissa tapahtuu (- jos ei mitään), next; mitä tapahtuu
   seuraavaksi, kellonaikoineen (esim. 15 - 18 Cosplay-kisat (WCS ja pukukisa)), aika;
   tuottaa tämänhetkisen tunnin (esim 10 - 11). Esim. [iso_sali-nyt] tuottaa lauantaina
   klo 11:30 tekstin "Avajaiset".
</p>
<p>
   Eri salilyhenteet: <?php print $salit; ?>
</p>
<p>
   Muista käyttää esikatselutoimintoa ennen tallennusta. Tallennus tapahtuu levykkeestä,
   esikatselu sen oikealla puolella olevasta napista! Dian voi poistaa oikeassa reunassa
   olevasta napista.<br/>
   <strong>MUISTA TALLENTAA MUUTOKSESI!</strong>
</p>