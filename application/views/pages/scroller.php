<p>Instanssi: <?php print $instances; ?></p>
<div id="formidata">
     <form action="" method="post" id="form" accept-charset="utf-8" onsubmit="return false;">
         <table id="scroller" class="stats" style="border-right:0px; border-top:0px; border-bottom:0px;">
             <thead>
                 <tr>
                     <th class="ui-state-default">Kohta</th>
                     <th class="ui-state-default">Teksti</th>
                     <th class="ui-state-default">Piilotettu?</th>
                 </tr>
             </thead>
             <tbody>
                 <?php print $tablebody; ?>
             </tbody>
         </table>
     </form>
</div>
<button id="lisarivi" name="moar" onclick="addrow();">Lisää rivi</button>
<br/><br/>
<p>
 <strong>MUISTA TALLENTAA MUUTOKSESI!</strong>
</p>
<button name="submit" onclick="return save();">Tallenna</button>
<div id="feed_cont" style="min-height:20px";>
     <div id="feedback" style="display:none;"></div>
</div>