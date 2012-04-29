<h2>Ohjelmakartan hallinta</h2>
<button name="add" onclick="$(&quot;#dialog-add&quot;).dialog(&#039;open&#039;);">Lisää uusi ohjelmanumero</button>
<br/><br/>
<div id="tabit">
    <ul style="height:29px">
        <li><a href="#kartta">Ohjelmakartta</a></li>
        <li><a href="#ohjelmat">Ohjelmien muokkaus</a></li>
        <?php if($level >= 3): ?>
        <li><a href="#asetukset">Asetukset</a></li>
        <?php endif ?>
    </ul>
    <div id="kartta" style="height:600px;">
        <div id="salit">
            Valitse salit: <?php print $salit; ?>
        </div>
        <br/>
        <div style="float:left;">
            <ol id="ohjelmanumerot" style="list-style-type: none;position:relative">
                <?php print $ohjelmat; ?>
            </ol>
        </div>
        <div style="height: 500px; max-width:100%; width:auto; min-width:118px; left: 280px; position:absolute; overflow-y:auto">
            <div style="position:relative; width=100%; overflow:hidden;" id="cal-cont">
                <?php print $timetable; ?>
            </div>
        </div>
    </div>
    <div id="ohjelmat">
        <ol id="ohjelmanumerot" style="list-style-type: none;">
            <?php print $ohjelmamuoks; ?>
        </ol>
    </div>
    <?php if($level >= 3): ?>
    <div id="asetukset">
        <table>
            <tr>
                <td><label for="alku">Tapahtuman alkuaika</label></td>
                <td><input type="text" id="from" name="alku" value="<?php print $alkupaiva; ?>" size="8" /> klo <?php print $alkuselect; ?></td>
            </tr>
            <tr>
                <td><label for="loppu">Tapahtuman päättymisaika</label></td>
                <td><input type="text" id="to" name="loppu" value="<?php print $loppupaiva; ?>" size="8" /> klo <?php print $alkuselect; ?></td>
            </tr>
        </table>
        <button name="save" onclick="save();">Tallenna</button><br/><br/>
        <div style="min-height:20px;">
            <div id="asetus_feedback" style="display:none;"></div>
        </div>
        <div id="kategoriat_acc">
            <h3><a href="#">Kategoriat</a></h3>
            <div>
                <p>
                    <button name="add_kategoria" onclick="$(&quot;#dialog-kategoria-add&quot;).dialog(&#039;open&#039;);">Lisää kategoria</button>
                    <br/>
                    Tunniste on vain järjestelmää itseään varten. Varsinainen nimi näkyy eri näkymissä.
                </p>
                <table class="stats">
                    <thead style="color:black;">
                        <tr>
                            <th>Tunniste</th>
                            <th>Nimi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php print $kategoriat_acc; ?>
                    </tbody>
                </table>
                <br/><br/>
            </div>
        </div>
        <div id="slotit_acc">
            <h3><a href="#">Aikaslotit</a></h3>
            <div>
                <p>
                    <button name="add_slot" onclick="$(&quot;#dialog-slot-add&quot;).dialog(&#039;open&#039;);">Lisää aikaslotti</button>
                    <br/>
                    Minuuttimäärä on vain järjestelmää itseään varten. Tunniste on vain helpompaa hahmottamista varten.
                </p>
                <table class="stats">
                    <thead style="color:black;">
                        <tr>
                            <th>Pituus (minuuttia)</th>
                            <th>Selite</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php print $timeslots_acc; ?>
                    </tbody>
                </table>
                <br/><br/>
            </div>
        </div>
        <div id="salit_acc">
            <h3><a href="#">Salit</a></h3>
            <div>
                <p>
                    <button name="add_sali" onclick="$(&quot;#dialog-sali-add&quot;).dialog(&#039;open&#039;);">Lisää sali</button>
                    <br/>
                    Tunniste on vain järjestelmää itseään varten. Varsinainen nimi näkyy eri näkymissä. Tunnistetta käytetään tosin myös ohjelmatageissa, joten "kaikki pienellä, välit alaviivoiksi" (esim: Iso sali -> iso_sali) voi olla ihan fiksu käytäntö.
                </p>
                <table class="stats">
                    <thead style="color:black;">
                        <tr>
                            <th>Tunniste</th>
                            <th>Nimi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php print $salit_acc; ?>
                    </tbody>
                </table>
                <br/><br/>
            </div>
        </div>
    </div>
    <?php endif ?>
</div>
