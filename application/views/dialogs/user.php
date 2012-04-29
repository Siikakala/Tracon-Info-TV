
<ul id="myMenu" class="contextMenu" style="width:180px;">
    <li class="kuittaa">
        <a href="#pass">Vaihda salasana</a>
    </li>
    <li class="chg separator">
        <a href="#chg">Muuta käyttäjätasoa</a>
    </li>
    <li class="del separator">
        <a href="#del">Poista</a>
    </li>
</ul>

<div id="dialog-confirm-del" title="Poista käyttäjä?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:5px 14px 20px 5px; -moz-transform: scale(2, 2); -webkit-transform: scale(2, 2);"></span>Oletko varma että haluat poistaa käyttäjän <span class="useri"></span>?</p>
</div>

<div id="dialog-pass" title="Vaihda salasana.">
	<p><span class="ui-icon ui-icon-person" style="float:left; margin:0 7px 20px 0;"></span>Anna käyttäjän <span class="useri"></span> uusi salasana:</p>
	<form action="#" id="passchange">
        <table>
            <tr>
                <td><label for="pass">Salasana:</label></td>
                <td><input type="password" name="pass" id="pass1"></td>
            </tr>
            <tr>
                <td><label for="confirm">Salasana uudelleen:</label></td>
                <td><input type="password" name="confirm" id="pass2"></td>
            </tr>
        </table>
    </form>
	<span id="dialog-pass-feedback" style="min-height:10px; margin-left:25px;"></span>
</div>

<div id="dialog-newuser" title="Lisää uusi käyttäjä.">
	<p><span class="ui-icon ui-icon-person" style="float:left; margin:0 7px 20px 0;"></span>Anna uuden käyttäjän tiedot:</p>
	<form action="#" id="newuser">
        <table>
            <tr>
                <td><label for="user">Käyttäjätunnus:</label></td>
                <td><input type="text" name="user" id="user"></td>
            </tr>
            <tr>
                <td><label for="pass">Salasana:</label></td>
                <td><input type="password" name="pass" id="u_pass1"></td>
            </tr>
            <tr>
                <td><label for="confirm">Salasana uudelleen:</label></td>
                <td><input type="password" name="confirm" id="u_pass2"></td>
            </tr>
            <tr>
                <td><label for="leveli">Käyttäjätaso:</label></td>
                <td><?php print $newuserselect; ?></td>
            </tr>
        </table>
    </form>
	<span id="dialog-newuser-feedback" style="min-height:10px; margin-left:25px;"></span>
</div>

<div id="dialog-level" title="Vaihda käyttäjätasoa.">
	<p><span class="ui-icon ui-icon-person" style="float:left; margin:0 7px 20px 0;"></span>Valitse käyttäjän <span class="useri"></span> uusi käyttäjätaso:</p>
	<form action="#" style="margin-left:25px;">
        <?php print $levelselect; ?>
    </form>
	<span id="dialog-level-feedback" style="min-height:10px"></span>
</div>
