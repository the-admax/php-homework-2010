<form action="auth.php?login" method="POST" accept-charset="utf-8">
<table>
    <tr>
    <td><label for="login">Имя учётной записи (логин)</label></td>
    <td><input type="text" name="login" size="16" value="" /></td>
    </tr>

    <tr>
    <td><label for="password">Пароль</label></td>
    <td><input type="password" name="password" size="16" value="" /></td>
    </tr>

    <tr>
    <td><label for="password">Запомнить меня</label></td>
    <td><input type="checkbox" name="remember" checked="checked" /></td>
    </tr>

    <tr><td colspan="2">
        <input type="submit" value="Войти" />
    </td></tr>
</table>
</form>
