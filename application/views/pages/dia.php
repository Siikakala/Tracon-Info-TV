<div id="select">
     <p>Valitse muokattava dia:
         <?php print $select; ?>
         tai <button name="uusi" onclick="return uusi();">Luo uusi</button>
         &nbsp;&nbsp;&nbsp;&nbsp;
         <span id="ident_" style="display:none;">Tunniste:<input type="text" id="ident" name="ident" value="" /></span>
     </p>
</div>
<div id="edit" style="display:none;width:940px"></div>
<p>
   Kopioi haluamasi nuoli tästä: → ➜ ➔ ➞ ➨ ➧ ➩ ➭ ➼<br/><br/>
   <strong>Eri salilyhenteet</strong>: <?php print $salit; ?><br/><br/>
   <strong>MUISTA TALLENTAA MUUTOKSESI!</strong>
</p>