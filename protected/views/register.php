<form action="register.php" method="POST" accept-charset="utf-8">
<table>
    <tr class="<?=(in_array('login', $errornous) ? 'error' : '');?>">
    <td><label for="login">Имя учётной записи (логин):</label></td>
    <td><input type="text" name="login" size="24" maxlength="16" value="<?=$form['login']?>" /></td>
    </tr>

    <tr class="<?=(in_array('password', $errornous) ? 'error' : '');?>">
    <td><label for="password">Пароль:</label></td>
    <td><input type="password" name="password" size="24" value="" /></td>
    </tr>

    <tr class="<?=(in_array('password', $errornous) ? 'error' : '');?>">
    <td><label for="password1">Повторите ввод пароля:</label></td>
    <td><input type="password" name="password1" size="24" value="" /></td>
    </tr>

    <tr class="<?=(in_array('name', $errornous) ? 'error' : '');?>">
    <td><label for="name">Отображаемое имя:</label></td>
    <td><input type="text" name="name" maxlength="30" size="24" value="<?=$form['name'];?>" /></td>
    </tr>

    <tr class="<?=(in_array('email', $errornous) ? 'error' : '');?>">
    <td><label for="email">Адрес электронной почты:</label></td>
    <td><input type="text" name="email" maxlength="64" size="24" value="<?=$form['email'];?>" /></td>
    </tr>
    
    <tr><td colspan="2">
        <input type="submit" value="Зарегистрироваться" />
    </td></tr>
</table>
</form>
