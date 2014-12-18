<?php

class WebUser extends User {
    public $state;
    private $_isGuest = true;
    //private $_autoLogin;
    private $_sessionStarted;
    // Параметры по умолчанию
    private $_config = array(
        // Разрешить автоматический вход по сохранённым куки
        'allowAutoLogin'=>true,
        // Период времени, на который сохраняется авторизация при запоминании сессии
        'longInterval'=>720,
        // Минимальный период хранения кода авторизации у клиента
        'shortInterval'=>1,
        // Имя куки, в котором сохраняется код сессии
        'sessionName'=>'sid',
        // Отображаемое имя анонимного пользователя
        'anonymousName'=>'Гость',
    );

    function __construct($config) {
        // Объединить массивы конфигурации, переписывая значения массива
        // $this->_config переопределёнными из $config
        $this->_config = array_merge($this->_config, $config);

        $this->name = $this->_config['anonymousName'];
        
        session_name($this->_config['sessionName']);
        if (isset($this->_config['allowAutoLogin']) && $this->_config['allowAutoLogin']) {
            // Если у пользователя оказались куки, загружаем его сессию
            if( ($sid = $_COOKIE[$this->_config['sessionName']]) != NULL) {
                session_id($sid);
                $this->_sessionStarted = session_start();
                $newUser = unserialize($_SESSION['user']);
                if (is_a($newUser, 'User')) {
                    $this->assign($newUser);
                    $this->_isGuest = $this->uid == 0;
                }
            }
        }
    }

    function __destruct() {
        $this->refresh();
    }

    function isGuest()      {   return $this->_isGuest; }

    function  __sleep() {
        $this->password = '';   // очистить хеш пароля. Нет необходимости в его копировании в хранилище сессии
        return $this->attributes();
    }
    
    /* Авторизовать пользователя и запомнить факт его входа.*/
    function login($rememberMe=false) {
        $interval = $rememberMe ? $this->_config['longInterval'] : $this->_config['shortInterval'];
        session_set_cookie_params($interval * 3600, App::cookieRoot());
        session_start();
        $this->_isGuest = false;
        $_SESSION['user'] = serialize($this);
        session_commit();
    }

    function refresh() {
        if ($this->_sessionStarted) {
            $_SESSION['user'] = serialize($this);
            session_commit();
            session_start();
        }
    }

    function logout() {
        if($this->isGuest())
            return ;

        $_SESSION['user'] = null;

        $guest = new User();
        $this->assign($guest);

        session_commit();

        setcookie($this->_config['sessionName'], App::cookieRoot(), time() - 3600);
        unset($_COOKIE[$this->_config['sessionName']]);

        $this->_isGuest = true;
    }
}

?>
