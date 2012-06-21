<div style="min-height:18px;"><div id="feedback" style="display:none;"></div></div>
<br/>
<table class="stats" width="100%">
    <thead>
        <tr>
            <th class="ui-state-default">Tärkeys</th>
            <th class="ui-state-default">Kategoria</th>
            <th class="ui-state-default">Tyyppi</th>
            <th class="ui-state-default">Alkuaika</th>
            <th class="ui-state-default">Kesto</th>
            <th class="ui-state-default">Tapahtuma</th>
            <th class="ui-state-default">Lisätietoja</th>
            <th class="ui-state-default">Vastuuhenkilö</th>
            <th class="ui-state-default">Tekijät</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <form id="tuotanto_add" action="#" onsubmit="add();return false;">
            <td width="100"><?php print $priority; ?></td>
            <td width="120"><?php print $category; ?></td>
            <td width="120"><?php print $type; ?></td>
            <td width="180" nowrap><input type="text" name="start" class="datepick" id="start_date" size="8" />&nbsp;&nbsp;<?php print $hours; ?>:<?php print $mins; ?></td>
            <td width="75" nowrap><input type="text" name="length" size="2" /> min</td>
            <td width="330"><input type="text" name="event" style="width:100%;" maxlength="300" /></td>
            <td><textarea name="notes" style="width:100%;" cols="3" rows="3"></textarea></td>
            <td><input type="text" name="vastuu" style="width:100%;" /></td>
            <td><input type="text" name="duunarit" style="width:100%;" /></td>
            </form>
        </tr>
    </tbody>
    <tbody id="refresh">
    <?php print $tablebody; ?>
    </tbody>
</table>