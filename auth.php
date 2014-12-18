<?php

require_once 'protected/core.php';

switch($_SERVER['QUERY_STRING']) {
    default:
    case 'login':
        if(! App::$user->isGuest()) {
            App::redirectTo('index.php');
        }

        if(App::isPost()) {
            // Обработать введённые данные
            try {
                if ($_POST['login'] == '' || $_POST['password'] == '')
                    App::error('auth','Введите свои учётные данные (имя и пароль)');
                
                $login = $_POST['login'];
                $password = $_POST['password'];
                if (!App::$user->authenicate($login, $password)) {
                    App::error('auth', 'Неверное имя пользователя и/или пароль');
                }

                App::$user->login(isset($_POST['remember']));
            } catch(Exception $e) {
                App::render('login');
            }
        } else {
            App::render('login');
        }

        break;
        
    case 'logout':
        App::$user->logout();

        break;
}

App::redirectTo('index.php');

?>
