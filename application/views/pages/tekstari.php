<div id="huom"><p>Huom! Puhelinnumero(t) tulee syöttää kansainvälisessä muodossa ilman etuplussaa ja välilyöntejä.</p></div>
<div id="saldo-container">Saldo: <span id="saldo"></span> &euro;</div>
<div id="tekstari-accord">
    <h3><a href="#" class="head-links">Yksittäinen viesti</a></h3>
    <div>
        <form action="" method="post" id="tekstari" accept-charset="utf-8">
            <table>
                <tr>
                    <td><label for="number">Puhelinnumero:</label></td>
                    <td><input type="text" name="number" value="" size="35" /></td>
                </tr>
                <tr>
                    <td><label for="message">Viesti:</label></td>
                    <td><input type="text" name="message" value="" size="35" /></td>
                </tr>
            </table>
            <input type="submit" value="Lähetä" onclick="send('tekstari');return false;" />
        </form>
    </div>
    <h3><a href="#" class="head-links">Ryhmäviesti</a></h3>
    <div>
        <p>Erota puhelinnumerot toisistaan ei-numeerisella merkillä (rivinvaihto, pilkku, välilyönti tms).<br/>
        <strong>HUOM!</strong> Lähetys kestää sitä kauemmin, mitä useammalle viestiä lähetät, koska nexmo sallii 5 viestiä sekunnissa. Voit seurata viestien lähetyksen edistymistä alta.</p>
        <form action="" method="post" id="ryhmatekstari" accept-charset="utf-8">
            <table>
                <tr>
                    <td><label for="number">Puhelinnumerot:</label></td>
                    <td><textarea name="number" cols="12" rows="10"></textarea></td>
                </tr>
                <tr>
                    <td><label for="message">Viesti:</label></td>
                    <td><input type="text" name="message" value="" size="55" /></td>
                </tr>
            </table>
            <input type="submit" value="Lähetä" onclick="send('ryhmatekstari');return false;" />
        </form>
    </div>
    <h3><a href="#" class="head-links">CSV-lähetys</a></h3>
    <div>
        <p>Hyväksytty syntaksi on /(\d{9,16})[;,](.*)$/U eli riveittäin on ensin numero, jonka jälkeen on pilkku tai puolipiste, ja tämän jälkeen on viesti. Esimerkiksi Excelin CSV-export tuottaa tässä muodossa kun numero on A-sarakkeessa ja viesti B-sarakkeessa. Ennen numeroa olevaa tekstiä ei huomioida.</p>
        <p><strong>HUOM!</strong> Upload ainoastaan lisää viestit jonoon. Voit seurata viestien lähetyksen edistymistä alta.</p>
        <div id="fileupload"></div>
        <br/><br/>
    </div>
</div>
<br/><br/>
<div style="min-height:20px"><div id="feedback" style="display:none;"></div></div>
<div id="valitystieto-accord">
    <h3><a href="#" class="head-links">Lähetetyt tekstiviestit statuksineen</a></h3>
    <div id="valitystiedot"><?php echo $valitystiedot; ?></div>
</div>
<div id="inbox-accord">
    <h3><a href="#" class="head-links">Vastaanotetut tekstiviestit</a></h3>
    <div id="inbox"><?php echo $inbox; ?></div>
</div>