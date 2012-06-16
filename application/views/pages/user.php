<button name="add" onclick="$(&quot;#dialog-newuser&quot;).dialog(&#039;open&#039;);">Lisää uusi käyttäjä</button>
<br/><br/>
<table class="stats">
    <thead>
        <tr>
            <th>ID</th>
            <th>Käyttäjätunnus</th>
            <th>Taso</th>
            <th>Edellinen kirjautuminen</th>
            <th>Viimeisin IP</th>
        </tr>
    </thead>
    <tbody>
        <?php print $tablebody; ?>
    </tbody>
</table>