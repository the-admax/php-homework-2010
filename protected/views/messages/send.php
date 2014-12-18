<form action="messages.php?<?=$action?>" method="POST" accept-charset="utf-8">
<table>
    <tr class="<?=(in_array('title', $errornous) ? 'error' : '');?>">
    <td><label for="title">Имя учётной записи (логин):</label></td>
    <td><input type="text" name="title" size="50" maxlength="100" value="<?=$form['title']?>" /></td>
    </tr>

    <tr class="<?=(in_array('recipient', $errornous) ? 'error' : '');?>">
    <td>Адресат:</td>
    <td><select name="recipient">
        <? foreach($users as $user): ?>
            <option value="<?=$user->id?>" <?=($user->id == $form['recipient'] ? 'selected="selected"' : '')?>><?=$user->name?></option>
        <? endforeach; ?>
        </select>
    </td>
    </tr>

    <tr class="<?=(in_array('body', $errornous) ? 'error' : '');?>">
    <td><label for="body">Сообщение:</label></td>
    <td><textarea cols="40" rows="20" name="body"><?=$form['body'];?></textarea></td>
    </tr>

    <tr><td colspan="2">
        <input type="submit" value="Отправить" />
    </td></tr>
</table>
</form>