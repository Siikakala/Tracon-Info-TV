<h2>Lokikirja</h2>
<div id="filter_cont" style="float:right;margin-top:-30px;">
    Suodatus/haku:
    <input type="text" id="filter" name="filter" size="35" title="OR-haku: hakusana1|hakusana2
        (&quot;Hae kaikki rivit, joiden kentistä löytyy joko hakusana1 tai hakusana2&quot;)
        AND-haku: hakusana1 hakusana2
        (&quot;Hae kaikki rivit, joiden kentistä löytyy kaikki hakusanat&quot;)
        Yhdistelmä: hakusana1|hakusana2 hakusana3
        (&quot;Hae kaikki rivit, joiden kentistä löytyy joko hakusana1 tai hakusana2, mutta myös hakusana3&quot;)" />
    <span class="ui-icon ui-icon-circle-close" style="float:right; margin:2px 0 20px 0;" onclick="$('#filter').val('');search();"></span>
</div>
<div id="add">
    <form action="" method="post" id="form" accept-charset="utf-8" onsubmit="save(); return false;">
        Lisää rivi:<br />
        <label for="tag"> Tyyppi:</label>
        <?php print $select; ?>
        <label for="comment"> Viesti:</label>
        <input type="text" id="com" name="comment" size="56" />
        <label for="adder"> Lisääjä:</label>
        <input type="text" id="adder" name="adder" value="<?php print $user; ?>" size="5" />
        <input type="submit" value="Lisää" />
    </form>
</div>
<div id="feed_cont" style="min-height:20px;">
    <div id="feedback"></div>
</div>
<div id="table">
    <table id="taulu" class="stats tablesorter">
        <thead>
            <tr>
                <th>Aika</th>
                <th>Tyyppi</th>
                <th>Viesti</th>
                <th>Lisääjä</th>
            </tr>
        </thead>
        <tbody>
            <?php print $tablebody; ?>
        </tbody>
    </table>
</div>