
<ul id="myMenu" class="contextMenu">
    <li class="edit">
        <a href="#edit">Muokkaa</a>
    </li>
    <li class="kuittaa separator">
        <a href="#check">Kuittaa</a>
    </li>
    <li class="del separator">
        <a href="#del">Poista</a>
    </li>
</ul>

<div id="dialog-confirm" title="Poista rivin kuittaus?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Oletko varma että haluat poistaa tämän rivin kuittauksen?</p>
</div>

<div id="dialog-confirm-del" title="Poista rivi?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Oletko varma että haluat poistaa tämän rivin?</p>
</div>

<div id="dialog-edit" title="Muokkaa riviä">
    <form id="logi_edit" action="#" onsubmit="return false;">
    <table>
        <tr><td><label for="edittypes">Tyyppi:</label></td><td><?php print $types; ?></td></tr>
        <tr><td><label for="editmessage">Viesti:</label></td><td><input id="editmessage" type="text" name="editmessage" size="50" /></td></tr>
        <tr><td><label for="editadder">Lisääjä:</label></td><td><input id="editadder" type="text" name="editadder" size="12" /></td></tr>
    </table>
    <input type="hidden" name="editrow" id="editrow" value="" />
    </form>
    <div style="min-heigth:18px;width:300px"><div id="dialog-edit-feedback" style="display:none;"></div></div>
</div>