Привет, <?=App::$user->name; ?>!
<ul><? if(App::$user->isGuest()): ?>
    <li><a href="auth.php?login">Войти</a></li>
    <li><a href="register.php">Зарегистрироваться</a></li>
<? else: ?>
    <li><a href="auth.php?logout">Выйти</a></li>
    <li><a href="user.php">Просмотреть свой профиль на сайте</a></li>
<? endif; ?>
</ul>