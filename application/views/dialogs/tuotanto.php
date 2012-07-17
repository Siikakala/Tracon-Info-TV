<div id="dialog-confirm-del" title="Poista rivi">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:5px 14px 20px 5px; -moz-transform: scale(2, 2); -webkit-transform: scale(2, 2);"></span>Oletko varma että haluat poistaa tapahtuman <span class="drowi"></span>?</p>
</div>

<div id="dialog-edit" title="Muokkaa riviä">
    <form id="tuotanto_edit" action="#" onsubmit="return false;">
    <table>
        <tr><td><label for="priority">Prioriteetti:</label></td><td width="100"><?php print $priority; ?></td></tr>
        <tr><td><label for="category">Kategoria:</label></td><td width="120"><?php print $category; ?></td></tr>
        <tr><td><label for="type">Tyyppi:</label></td><td width="120"><?php print $type; ?></td></tr>
        <tr><td><label for="start">Alkuaika:</label></td><td width="180" nowrap><input id="datestart" type="text" name="start" class="datepick" id="start_date" size="8" />&nbsp;&nbsp;<?php print $hours; ?>:<?php print $mins; ?></td></tr>
        <tr><td><label for="length">Pituus:</label></td><td width="75" nowrap><input id="pituus" type="text" name="length" size="2" /> min</td></tr>
        <tr><td><label for="event">Tapahtuma:</label></td><td width="98%"><input id="event" type="text" name="event" style="width:98%;" maxlength="300" /></td></tr>
        <tr><td><label for="notes">Lisätietoja:</label></td><td width="100%"><textarea id="lisat" name="notes" style="width:100%;" cols="3" rows="5"></textarea></td></tr>
        <tr><td><label for="vastuu">Vastuuhenkilö:</label></td><td><input id="vastuu" type="text" name="vastuu" style="width:98%;" /></td></tr>
        <tr><td><label for="duunarit">Tekijät:</label></td><td><input id="tekijat" type="text" name="duunarit" style="width:98%;" /></td></tr>
        <input type="hidden" id="edit-id" name="row" value="" />
    </table>
    </form>
    <div style="min-heigth:18px;"><div id="dialog-edit-feedback" style="display:none;"></div></div>
</div>

<ul id="myMenu" class="contextMenu" style="width:100px;">
    <li class="muokkaa">
        <a href="#edit">Muokkaa</a>
    </li>
    <li class="del separator">
        <a href="#del">Poista</a>
    </li>
</ul>