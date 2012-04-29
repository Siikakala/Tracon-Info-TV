<h2>Rulla-hallinta</h2>
<p>aka. Diashow-hallinta</p>
<div id="formidata">
 <form action="" method="post" id="form" accept-charset="utf-8" onsubmit="return false;">
     <table id="rulla" class="stats" style="border-right:0px; border-top:0px; border-bottom:0px;">
         <thead>
             <tr>
                 <th class="ui-state-default">Kohta</th>
                 <th class="ui-state-default">Dia</th>
                 <th class="ui-state-default">Aika (~s)</th>
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
<p>
  <strong>HUOM!</strong>
  <ul>
      <li>Numerointi voi hyppiä numeroiden yli, ne näytetään pienimmästä suurimpaan.</li>
      <li><strong>Älä käytä kohtaa 0!</strong> Rivi ei tallennu tällöin.</li>
      <li>Twitter-feediä ei voi olla kuin yksi. Ensimmäisen jälkeiset ovat vain tyhjiä dioja.</li>
      <li>Diat näkyvät noin sekunnin pidempään kuin määrität tässä.</li>
  </ul>
</p>
<button name="submit" onclick="return save();">Tallenna</button>
<div id="feed_cont" style="min-height:20px;">
  <div id="feedback" style="display:none;"></div>
</div>