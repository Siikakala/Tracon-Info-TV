<div id="dialog-add" title="Lisää uusi ohjelmanumero">
    <form action="" method="post" id="ohjelma_add" accept-charset="utf-8">
        <table>
            <tr>
                <td><label for="otsikko">Ohjelmanumero:</label></td>
                <td><input type="text" name="otsikko" value="" size="35" /></td>
            </tr>
            <tr>
                <td><label for="pitaja">Pitäjä:</label></td>
                <td><input type="text" name="pitaja" value="" size="35" /></td>
            </tr>
            <tr>
                <td><label for="kategoria">Kategoria:</label></td>
                <td><?php print $kategoria; ?></td>
            </tr>
            <tr>
                <td><label for="pituus">Pituus:</label></td>
                <td><?php print $pituus; ?>
                    &nbsp;&nbsp;&nbsp;
                    <div id="mp-cont" style="height:16px;width:100px;margin-left:80px;margin-top:-19px;">
                        <span id="muupituus" style="display:none;">
                            <input type="text" name="muupituus" value="" size="5" /> min
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="kuvaus">Ohjelmakuvaus:</label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"><textarea name="kuvaus" cols="80" rows="15"></textarea></td>
            </tr>
        </table>
    </form>
</div>

<div id="dialog-kategoria-add" title="Lisää uusi kategoria">
    <p>Tunniste on järjestelmän sisäiseen käyttöön, nimi näkyy näkymissä</p>
    <form action="" method="post" id="kategoria_add" accept-charset="utf-8">
        <table>
            <tr>
                <td><label for="tunniste">Tunniste:</label></td>
                <td><input type="text" name="tunniste" value="" size="20" /></td>
            </tr>
            <tr>
                <td><label for="nimi">Nimi:</label></td>
                <td><input type="text" name="nimi" value="" size="20" /></td>
            </tr>
        </table>
    </form>
</div>

<div id="dialog-slot-add" title="Lisää uusi aikaslotti">
    <p>Minuuttimäärä on järjestelmän sisäiseen käyttöön, selite näkyy näkymissä</p>
    <form action="" method="post" id="slot_add" accept-charset="utf-8">
        <table>
            <tr>
                <td><label for="pituus">Pituus:</label></td>
                <td><input type="text" name="pituus" value="" size="20" /> minuuttia</td>
            </tr>
            <tr>
                <td><label for="selite">Selite:</label></td>
                <td><input type="text" name="selite" value="" size="20" /></td>
            </tr>
        </table>
    </form>
</div>

<div id="dialog-sali-add" title="Lisää uusi sali">
    <p>Tunniste on järjestelmän sisäiseen käyttöön, nimi näkyy näkymissä</p>
    <form action="" method="post" id="sali_add" accept-charset="utf-8">
        <table>
            <tr>
                <td><label for="tunniste">Tunniste:</label></td>
                <td><input type="text" name="tunniste" value="" size="20" /></td>
            </tr>
            <tr>
                <td><label for="nimi">Nimi:</label></td>
                <td><input type="text" name="nimi" value="" size="20" /></td>
            </tr>
        </table>
    </form>
</div>