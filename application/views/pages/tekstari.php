<p>Huom! Puhelinnumero(t) tulee syöttää kansainvälisessä muodossa ilman etuplussaa ja välilyöntejä. Ainoastaan tasan 12 numeroa pitkät puhelinnumerot hyväksytään siis.</p>
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
        <strong>HUOM!</strong> Upload kestää sitä kauemmin, mitä enemmän viestejä lähetät kerralla, koska nexmo sallii 5 viestiä sekunnissa. Odota rauhassa.</p>
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
        <p>Hyväksytty syntaksi on /^(\d{12})[;,](.*)$/U eli riveittäin on ensin numero, jonka jälkeen on pilkku tai puolipiste, ja tämän jälkeen on viesti. Esimerkiksi Excelin CSV-export tuottaa tässä muodossa kun numero on A-sarakkeessa ja viesti B-sarakkeessa.</p>
        <p><strong>HUOM!</strong> Upload kestää sitä kauemmin, mitä enemmän viestejä lähetät kerralla, koska nexmo sallii 5 viestiä sekunnissa. Odota rauhassa.</p>
        <div id="fileupload"></div>
    </div>
</div>
<br/><br/>
<div style="min-height:20px"><div id="feedback" style="display:none;"></div></div>