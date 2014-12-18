<?php

require_once 'protected/core.php';

if (App::$user->isGuest) {
    App::redirectTo('auth.php?login');
}

list($id, $action) = explode('/', $_SERVER['QUERY_STRING']);
//list($action, $param) = explode(':', $action);

if((int)$id == 0) {
    $action = $id;
    $id = 0;
} else {
    $id = (int)$id;
}

$storage = new Message(App::$user->uid);

switch($action) {
    case 'reply':
        if($id != 0) {
            if( ($msg = $storage->findById($id)) == false) {
                App::error('messages', "Сообщение #$id не найдено");
            } else {
                $form = array(
                    'recipient'=>$msg->sender,
                    'title'=>'Re: ' . $msg->title,
                    // Сюда можно добавить цитирование сообщения
                );
            }
        }
        
    case 'send':        // Отправить сообщение или вывести форму ввода сообещния
        if(!isset($form))
            $form = array();

        // Имена полей, содержащие ошибки будут сохраняться здесь
        $errornous = array();

        if (App::isPost()) {
            // Все поля заполнены, продолжаем проверку
            
            // Убрать потенциально опасные теги. Однако, функция пропустит
            // инструкции вроде "javascript:..."
            $_POST['body'] = strip_tags_attributes($_POST['body'], '<a><p><ul><li><b><u>', 'href');

            if (! $storage->check('body', $_POST['body'], 'messages/send')) {
                $errornous[] = 'body';
                App::error('messages/send', 'Слишком длинное сообщение. Разрешено не более 2000 символов');
                
            } else {
                $form['body'] = $_POST['body'];
            }
            
            if (! $storage->check('recipient', $_POST['recipient'], 'messages/send')) {
                $errornous[] = 'recipient';
                App::error('register', 'Введите допустимое имя учётной записи. '.
                        'Длина от 4 до 20 символов из набора a-z, A-Z, 0-9, ".", "_"');
            } else {
                $form['login'] = $_POST['login'];
            }

            App::notify('notify', array(
                'message'=>"Сообщение отправлено"
            ));
            goto list_msgs;
        }
        
        App::render('messages/send', array(
            'users'=> User::listUsers(true),
            'form'=>$form,
        ));


    case 'read':        // Прочесть сообщение
        if( ($message = $storage->read($id)) != false) {
            App::render('messages/read', array(
                'msg'=>$message,
            ));
        } else {
            App::error('messages', 'Сообщения с таким ID не найдено');
        };
        // Отобразить список сообщений

    case 'outgoing':     // Только исходящие
        $outgoing = true;
    case '':            // Показать только входящие
    list_msgs:
        $list = $storage->listMessages($outgoing);

        App::render('messages/list', array(
            'list'=>$list,
            'empty'=>(! $list),
        ));
}


?>
