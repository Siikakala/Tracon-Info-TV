<h4>Globaali hallinta:</h4>
<br/>
Näytä:<?php print $select; ?>
<button name="apply" onclick="show_save();">Vaihda</button>
<br/>
<div id="span_cont" style="min-height:25px;">
     <div id="show_feed"></div>
</div>
<hr><br/>
<div id="formidata">
     <form action="" method="post" id="form" accept-charset="utf-8" onsubmit="return false;">
         <table id="frontendit" class="stats" style="border-right:0px; border-top:0px; border-bottom:0px;">
             <thead>
                 <tr>
                     <th class="ui-state-default">Frontend</th>
                     <th class="ui-state-default">Näytä</th>
                     <th class="ui-state-default">Käytä globaalia?</th>
                 </tr>
             </thead>
             <tbody>
                 <?php print $tablebody; ?>
             </tbody>
         </table>
     </form>
</div>
<p>
     <button id="saev" name="saev" onclick="save();">Tallenna</button>
     <div id="feedback_container" style="min-height:20px;">
         <div id="feedback" style="display:none;"></div>
     </div>
</p>