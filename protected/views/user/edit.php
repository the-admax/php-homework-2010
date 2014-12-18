<h3>Профиль
    (<a href="user.php<?=($id ? '?'.$id : '');?>">обзор</a>)
</h3> 

<form action="user.php?<?=($id ? $id.'/':'');?>edit" method="POST" accept-charset="utf-8">
<table>
    <tr class="<?=(in_array('name', $errornous) ? 'error' : '');?>">
    <td><label for="name">Отображаемое имя:</label></td>
    <td><input type="text" name="name" maxlength="30" size="24" value="<?=$user->name;?>" /></td>
    </tr>

<? if(App::$user->class == 'admin'): ?>
    <tr><td>Класс доступа:</td>
    <td><select name="class">
        <? foreach(User::userClasses() as $class=>$title): ?>
            <option value="<?=$class?>" <?=($user->class == $class ? 'selected="selected"' : '')?>><?=$title?></option>
        <? endforeach; ?>
        </select>
    </td>
    </tr>
<? endif; ?>

    <tr class="<?=(in_array('email', $errornous) ? 'error' : '');?>">
    <td><label for="email">Адрес электронной почты:</label></td>
    <td><input type="text" name="email" maxlength="32" size="24" value="<?=$user->email;?>" /></td>
    </tr>

    <tr><td colspan="2">
        <input type="submit" value="Сохранить" />
    </td></tr>
</table>
</form>
