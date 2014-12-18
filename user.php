<?php

require_once 'protected/core.php';

if(App::$user->isGuest()) {
    App::redirectTo('auth.php?login');
    die();
}

list($id, $action) = explode('/', $_SERVER['QUERY_STRING']);
list($action, $param) = explode(':', $action);

if((int)$id == 0) {
    $action = $id;
    $id = null;
} else {
    $id = (int)$id;
}


if (! in_array($action, array('list'))) {
    if($id == 0) {
        $id = App::$user->uid;
    }
    
    if ( ($user = User::findById($id)) == false) {
        App::error('user', 'Пользователя с таким ID не существует');
        App::render();
    }
}

switch($action) {
    case 'delete':
        /* Удаление пользователя.
         * Удалить пользователя может только администратор */
        if (App::$user->class == 'admin') {
            // Удалить только после подтверждения
            if (App::isValidKey($param, "$id/delete")) {
                if($user->delete()) {
                    App::notify('Пользователь удалён');
                } else {
                    App::error('user/delete','Не удалось удалить пользователя. Вероятно, он не существует');
                }
            } else {
                // предупредим, если администратор хочет удалить себя. Тут можно запретить такую операцию
                if($id == App::$user->uid) {
                    App::error('user/delete','Самоубийство - грех :)');
                }

                $confirmKey = App::getKey("$id/delete");
                App::notify("Вы действительно хотите удалить пользователя {$user->name}?",
                    array(
                        "user.php?$id/delete:$confirmKey"=>'Да',
                        "user.php?list"=>'Нет'
                    ));
            }
        } else {
            App::error('user/delete', 'Недостаточно прав для совершения операции');
        }

        // !!!!
        // НЕ прерывая программу, продолжаем выполнение, выводя список пользователей

    case 'list':
        $users = User::listUsers(false);

        App::render('user/list', array(
            //'pages'=>$pages,
            'nousers'=>($users == false),
            'users'=>$users,
        ));
        
    case 'edit':
        /* Изменение профиля пользователя.
         * Пользователь может изменять только своё отображаемое имя,
         * Email. Если поле оставить пустым, значение соотв. атрибута не
         * изменится */
        
        $errornous = array();
        $form = array();
        
        if(App::isPost() && $user) {
            if(App::$user->class == 'admin') {
                // Изменяем административные настройки пользователя
                if (! $user->check('class', $_POST['class'], 'user/edit')) {
                    $errornous[] = 'class';
                    App::error('user/edit', 'Недопустимый класс пользователя');
                } else {
                    $form['class'] = $_POST['class'];
                }
            }

            if ($_POST['email'] != '') {
                if (! $user->check('email', $_POST['email'], 'user/edit')) {
                    $errornous[] = 'email';
                    App::error('user/edit', 'Неверный адрес электронной почты. Введите '.
                            'действующий адрес для связи с Вами в случае утери пароля');
                } else {
                    $form['email'] = $_POST['email'];
                }
            }

            if ($_POST['name'] != '') {
                $_POST['name'] = htmlspecialchars($_POST['name']);
                if (! $user->check('name', $_POST['name'], 'user/edit')) {
                    $errornous[] = 'name';
                    App::error('user/edit', 'Слишком длинное имя для отображения. Разрешено не более 30 знаков');
                } else {
                    $form['name'] = $_POST['name'];
                }
            }

            if ( empty($errornous) ) {
                foreach ($form as $key=>$value) {
                    $user->{$key} = $value;
                }

                if ($user->update(array_keys($form))) {
                    if ($user->uid == App::$user->uid) {
                        App::$user->assign($user);
                        App::$user->refresh();
                    }
                    App::notify('Профиль успешно изменён');
                }
                // Выполнить изменение атрибутов пользователя
            } 
        }

        // отобразить окно редактирования настроек
        // и сопутствующие сообщения об ошибках (если есть)
        App::render('user/edit', array(
            'id'=>$id,
            'errornous'=>$errornous,
            'user'=>$user,
        ));

        break;

    default:        
        App::render('user/default', array(
            'canEdit'=>(App::$user->class == 'admin' || App::$user->uid == $id),
            'id'=>$id,
            'user'=>$user,
        ));
        break;
}

//App::redirectTo('auth.php?login');
?>
