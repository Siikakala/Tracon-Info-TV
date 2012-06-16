<div id="formidata">
   <form action="" method="post" id="form" accept-charset="utf-8" onsubmit="return false;">
       <table id="streamit" class="stats" style="border-right:0px; border-top:0px; border-bottom:0px;">
           <thead>
               <tr>
                   <th class="ui-state-default">Streamin tunniste</th>
                   <th class="ui-state-default">URL</th>
                   <th class="ui-state-default">Järjestysnro</th>
               </tr>
           </thead>
           <tbody>
               <?php print $tablebody; ?>
           </tbody>
       </table>
   </form>
</div>
<p>
   <button id="lisarivi" name="moar" onclick="addrow();">Lisää rivi</button>
   <button id="saev" name="saev" ="" onclick="save();">Tallenna</button>
   <div id="feedback_container" style="min-height:20px;">
       <div id="feedback" style="display:none;"></div>
   </div>
   Esikatselu:
</p>
<div id="stream_content" style="display:none;"></div>