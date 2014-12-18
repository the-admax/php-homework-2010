<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        @import "css/design.css";
        @import "css/controls.css";
    </style>

    <title><?=App::$title; ?></title>
</head>
<body>
    <div class="header">
        <h1><?=App::$title; ?></h1>
    </div>

    <div class="menu">
        <a href="index.php">главная</a>
        <? if(App::$user->isGuest()): ?>
        <a href="register.php">регистрация</a>
        <a href="auth.php?login">войти</a>
        <? else: ?>
        <a href="user.php?list">пользователи</a>
        <a href="user.php">профиль</a>
        <a href="auth.php?logout">выйти</a>
        <? endif; ?>
    </div>


    <?if(!empty($errors)): ?>
        <div class="errors">
            <ul>
            <? foreach($errors as $errorSource=>$errors): ?>
                <? foreach($errors as $error): ?>
                    <li><?=$error;?></li>
                <? endforeach; ?>
            <? endforeach; ?>
            </ul>
        </div>
    <? endif; ?>

    <?if(!empty($messages)): ?>
        <div class="messages">
            <ul>
            <? foreach($messages as $message): ?>
                <p><?=$message[0]?></p>
                <? if($message[1] != null): ?>
                    &emsp;<?=implode(' | ', $message[1]);?>
                <? endif; ?>
            <? endforeach; ?>
            </ul>
        </div>
    <? endif; ?>

    <div id="content">
        <?=$content;?>
    </div>
</body>
</html>
