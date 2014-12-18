<?php

require_once 'protected/core.php';

/* Контролер регистрации на сайте.
 *  Когда пользователь введёт свои данные  */

if (! App::$user->isGuest()) {
    App::error('register', 'Вы уже зарегистрированы');
    App::render();
}

if (App::isPost()) {
    $user = new User();

    // Для удобства пользователя сохраним в $form ранее введённые данные, чтобы
    // в случае ошибки восстановить их на странице
    $form = array();
    // Имена полей, содержащие ошибки будут сохраняться здесь
    $errornous = array();
    
    if ($_POST['login'] == ''
        || $_POST['password'] == ''
        || $_POST['password1'] == ''
        || $_POST['email'] == ''
    ) {
        $errornous = array('login', 'password', 'email');
        App::error('register', 'Заполните все обязательные поля', true);
    } else {
        // Все поля заполнены, продолжаем проверку
        if (! $user->check('login', $_POST['login'], 'register')) {
            $errornous[] = 'login';
            App::error('register', 'Введите допустимое имя учётной записи. '.
                    'Длина от 4 до 20 символов из набора a-z, A-Z, 0-9, ".", "_"');
        } else {
            $form['login'] = $_POST['login'];
        }
        
        if (! $user->check('email', $_POST['email'], 'register')) {
            $errornous[] = 'email';
            App::error('register', 'Неверный адрес электронной почты. Введите '.
                    'действующий адрес для связи с Вами в случае утери пароля');
        } else {
            $form['email'] = $_POST['email'];
        }

        $_POST['name'] = htmlspecialchars($_POST['name'] == null ? $_POST['login'] : $_POST['name']);
        if (! $user->check('name', $_POST['name'], 'register')) {
            $errornous[] = 'name';
            App::error('register', 'Слишком длинное имя для отображения. Разрешено не более 30 знаков');
        } else {
            $form['name'] = $_POST['name'];
        }

        // И если этот пароль удовлетворяет всем условиям
        if (! $user->check('password', $_POST['password'], 'register')) {
            $errornous []= 'password';
            App::error('register', 'Длина пароля должна быть меньше 24, но больше 4 символов');
        } else {
            if( $_POST['password'] != $_POST['password1']) {
                $errornous []= 'password';
                App::error('register', 'Введённые пароли не совпадают');
            } else {
                $form['password'] = $_POST['password'];
            }

        }


        if ( empty($errornous)) {
            foreach ($form as $key=>$value) {
                $user->{$key} = $value;
            }

            if (! ($uid = $user->create())) {
                $errornous[] = 'login';
                $errornous[] = 'email';
                App::error('register', 'Пользователь с таким именем или адресом электронной почты уже существует. Выберите другое');
            } else {
                // УРА! Пользователь успешно прошёл регистрацию. Поведаем ему об этом
                $user->uid = $uid;
                App::$user->assign($user);
                App::$user->login();

                App::render('registration-ok', array(
                    'user'=>$user
                ));
            }
        }
    }
}

App::render('register', array(
    '$errornous'=>$errornous,
    'form'=>$form
));


?>
