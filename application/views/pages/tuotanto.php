<br/><br/>
<table class="stats" width="100%">
    <thead>
        <tr>
            <th class="ui-state-default">Tärkeys</th>
            <th class="ui-state-default">Kategoria</th>
            <th class="ui-state-default">Alkuaika</th>
            <th class="ui-state-default">Kesto</th>
            <th class="ui-state-default">Tapahtuma</th>
            <th class="ui-state-default">Vastuuhenkilö</th>
            <th class="ui-state-default">Tekijät</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <form id="tuotanto_add" action="#">
            <td><select style="width:100%;" name="priority"><?php print $priority; ?></select></td>
            <td><select style="width:100%;" name="category"><?php print $category; ?></select></td>
            <td nowrap><input type="text" name="start" class="datepick" id="start_date" style="width:70%;" />&nbsp;&nbsp;<?php print $hours; ?>:<?php print $mins; ?></td>
            <td nowrap><input type="text" name="lenght" style="width:90%;" /> min</td>
            <td><input type="text" name="event" style="width:100%;" /></td>
            <td><input type="text" name="vastuu" style="width:100%;" /></td>
            <td><input type="text" name="duunarit" style="width:100%;" /></td>
            </form>
        </tr>
        <?php print $tablebody; ?>
    </tbody>
</table>