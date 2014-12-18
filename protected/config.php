<?php

/* Конфигурация приложения */

$config = array(
    // Настройки приложения
    'app'=>array(
        // Некоторый уникальный ключ, который будет использоваться для подтвеждения транзакций.
        // Должен быть скрыт от клиента
        'secret'=>'89723jkh87js78',
        'title'=>'Домашняя работа',
    ),
    // Настройки подключения к СУБД
    'db' => array(
        'dsn'=>'mysql:host=127.0.0.1;port=3306;dbname=auth_test',
        'username'=>'auth_test',
        'password'=>'KVJhpdusQBN7uELP',
    ),
    'user'=>array(
        'anonymousName'=>'Гость',
        'sessionName'=>'auth_sid',
        'allowAutoLogin'=>true,
        'longInterval'=>30*24,
        'shortInterval'=>1
    )
);

?>
