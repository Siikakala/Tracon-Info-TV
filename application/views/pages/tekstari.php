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
    <input type="submit" value="Lähetä" onclick="send();return false;" />
</form>

<div id="feedback" style="display:none;"></div>