<?php

/* Ядро системы.
 * Содержит классы Db, DataModel и App.
 *  Класс App должен содержать только статические члены. Он всегда доступен в единственном
 * экземпляре во всём приложении.
 *  Db предоставляет некоторые методы для удобного обращения к СУБД
 *  DataModel - родитель всех классов, использующих БД в системе.
 *
 */

require_once 'config.php';
require_once 'UserClass.php';
require_once 'MessageClass.php';
require_once 'WebUserClass.php';

error_reporting(E_ERROR);

// Обработчик непойманного исключения
function onException(Exception $e) {
    onError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
}


// Обработчик ошибок. Прерывает вывод информации на пользователю, заменяя её
// сообщением об ошибке и отладочной информацией. Чтобы изменить формат этих
// сообщений, см. файл "views/_error.php"
function onError($errno, $errstr, $errfile, $errline) {
    if ($errno & error_reporting() != 0) {
        while(ob_get_level() > 0)
            ob_end_clean();
        require 'views/_error.php';
        die(0);
    }
}

class Db extends PDO {
    /* Выполнить запрос $statement к СУБД, используя значения параметров из $params.
     * Запрос может быть как экземпляром класса PDOStatement, так и текстовым (в этом
     * случае автоматически создаётся экземпляр PDOStatement).
     * Функция заменяет PDOStatement::execute(). Для подробностей - см. документацию
     * на сайте PHP.
     */
    public function execute($statement, array $params=null) {
        if (is_string($statement))
            $statement = $this->prepare($statement);

        if($params == null)
            $data = null;
        else {
            $data = array();
            foreach($params as $key=>$value) {
                if (is_int($key) || strstr($key, ':'))
                    $data[$key] = $value;
                else
                    $data[':'.$key] = $value;
            }
        }
        
        if (!$statement->execute($data)) {
            App::fatal('Не удалось выполнить запрос к СУБД');
        }

        return $statement;
    }

    public function insert($statement, array $params=null) {
        $this->execute($statement, $params);
        return $this->lastInsertID();
    }
}

/* Класс, абстрагирующий доступ к модели данных - таблице. Каждая её строка может
 * быть представлена моделью.
 * DataModel сам по себе не может служить для обращения к БД. Для этого необходимо
 * определить свою модель с методами доступа к атрибутам.
 *  Наследник должен переопределить:
 *      attributes() - функция, возвращающая массив значений КОЛОНКА=>ПОЛЕ.
 *          То есть ключи в массиве - имена колонок в таблице (её атрибутов),
 *          а значения - имена соответствующих им полей в классе
 *      tableName() - возвращает имя таблицы, для которой действует модель
 *  И, при необходимости:
 *      check() - функция проверки и нормализации значений атрибутов
 *      assign() - заимствование значений атрибутов из другой модели. При этом
 *          имеющиеся значения, конечно же, теряются.
 *
 * В переопределении остальных функций нет необходимости. Для примера рабочей модели,
 * см. UserClass.php
 */
class DataModel {
    static function attributes() {
        App::fatal('Определите атрибуты модели');
    }

    public function check($field, &$value, $on='') {
        return true;
    }

    static function tableName() {
        return '';
    }

    public function assign(DataModel $source) {
        $vars = get_object_vars($source);
        foreach($vars as $var=>$value) {
            $this->{$var} = $value;
        }
    }

    // Проверка атрибутов модели.
    // Параметр $fields может содержать имя атрибута для проверки, или их массив,
    // или NULL, подразумевающее проверку всех атрибутов.
    function validate($fields=null, $on='') {
        if(is_string($fields)) {
            $fields = array($fields);
        } else if ($fields == null) {
            $fields = array_values($this->attributes());
        }

        $errornous = array();
        foreach($fields as $field) {
            if(!$this->check($field, $this->{$field}, $on))
                $errornous[] = $field;
        }
        return $errornous;
    }

    /* Загрузить информацию о пользователе из $params.
     * $data - массив обновляемых параметров. Атрибуты, существующие в БД, но
     * не в классе игнорируются */
    function load(array $data) {
        $columns = $this->attributes();
        foreach($data as $columnName => $value) {
            if (array_key_exists($columnName, $columns)) {
                    $this->{$columns[$columnName]} = $value;
            } else ;// Игнорировать колонки, не учтённые в модели
        }
        return $this;
    }

    /* получить данные полей модели, перечисленных в $fields.
     * Отстутствие списка подразумевает взятие значений всех атрибутов */
    protected function getData(array $fields=null) {
        $data = array();
        if ($fields != null) {
            $noAttribs = array_diff($fields, $this->attributes());
            if (!empty($noAttribs))
                App::fatal('Атрибут(ов) '.implode(', ', $noAttribs).' не существует');

            $columns = array_flip($this->attributes());
            foreach ($fields as $fieldName) {
                $data[$columns[$fieldName]] = $this->{$fieldName};
            }
        } else {
            foreach ($this->attributes() as $columnName=>$fieldName) {
                $data[$columnName] = $this->{$fieldName};
            }
        }

        return $data;
    }

    /* Выполнить SQL-выражение, переданное в параметре $statement, 
     * подставляя значения из атрибутов модели.
     * По сути, эта функция обрабатывает строковое представление запроса,
     * извлекая из него параметры, и подставляет их значения (см. bindParam()),
     * игнорируя параметры запроса, не определённые в атрибутах, позволяя
     * позднее доопределить их.
     *  */
    protected function prepare($statement) {
        if (is_string($statement))
            $statement = App::$db->prepare($statement);

        $n = preg_match_all('/:([a-z0-9_]*)/i', $statement->queryString, $tokens);
        for($i = 0; $i < $n; $i++) {
            if (property_exists(get_class($this, $tokens[1][$i])))
                $statement->bindParam($tokens[0][$i], $this->{$tokens[1][$i]});
            else; // Игнорировать поля, не добавленные в модель
        }

        return $statement;
    }
}

class App {
    static $user;
    static $db;
    static $errors;
    static $messages;
    static $title;
    private static $_secret;

    /* Подпрограмма загрузки приложения:
     * обрабатывает конфигурацию, создавая экземпляры классов первой необходимости:
     * Db, WebUser. */
    static function init($config) {
        self::$db = new Db(
            $config['db']['dsn'],
            $config['db']['username'],
            $config['db']['password']
        );

        self::$user = new WebUser($config['user']);

        self::$errors = array();
        self::$messages = array();

        self::$title = $config['app']['title'];
        self::$_secret = $config['app']['secret'];
    }

    // Простейшая подпрограмма, нужная для получения пути сохранения куки в браузере клиента.
    // Так как все открытые сценарии и действия доступы из корня каталога, то функция вернёт
    // относительный путь к их местонахождению.
    //
    static function cookieRoot() {
        return dirname($_SERVER['REQUEST_URI']);
    }

    /* Создать ключ подтверждения транзакицй на стороне клиента.
     * Использует ID сессии, код операции и секретный код, который должен быть известен
     * только серверной стороне */
    static function getKey($action) {
        return md5(self::$_secret . session_id() . $salt);
    }

    /* Проверить ключ */
    static function isValidKey($key, $action) {
        return $key == self::getKey($salt);
    }

    static function appRoot() {
        static $path;
        if (!isset($path))
            $path = 'http://' . $_SERVER['SERVER_NAME'] . '/'. dirname($_SERVER['REQUEST_URI']);
        return $path;
    }

    /* Вывести пользователю страницу с разметкой
     * $view - имя страницы (имя файла в каталоге 'views/' без расширения '.php'
     * $params - массив значений, которые будут подставлены в страницу */
    static function render($view='default', array $params=null,$noReturn=true,$isRoot=false) {
        if ($params != null)
            extract($params, EXTR_REFS);
        
        ob_start();

        include 'views/' . $view . '.php';
        
        if($isRoot) {
            ob_end_flush();
            self::terminate();
        } else {
            if ($params == null)
                $params = array();
            $params['content'] = ob_get_clean();
            $params['errors'] = (is_array($params['errors'])
                ? $params['errors'] + App::$errors
                : App::$errors);

            $params['messages'] = (is_array($params['messages'])
                ? $params['messages'] + App::$messages
                : App::$messages);
            
            App::render('layout', $params, $noReturn, true);
        }
    }

    static function redirectTo($url) {
        if (!headers_sent())  {
            header('Location: ' . $url);
        }
        echo "Перейти на <a href=\"$url\">$url</a>";
        self::terminate();
    }

    static function terminate() {
        /* Здесь можно выполнять любые действия, которые будут выполнены при нормальном
         *  завершении работы приложения */
        exit();
    }

    // true, если клиент обратился к системе действие методом POST
    static function isPost() {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    // Уведомить пользователя сообщением $message, предоставляя возможность выбора действия (из $actions)
    static function notify($message, array $actions=null) {
        $acts = array();
        if(is_array($actions)) {
            foreach($actions as $action=>$title)
                $acts[] = "<a href=\"$action\">$title</a>";
        }
        self::$messages[] = array($message, $acts);
    }

    // Отобразить ошибку времени исполнения
    static function error($source, $msg, $isCritical=false) {
        self::$errors[$source][] = $msg;
        if ($isCritical)
            throw new Exception($msg);
    }

    // Подпрограмма, зевершающая работу приложения в результате ошибки
    static function fatal($msg) {
        trigger_error($msg, E_ERROR);
    }
}

set_exception_handler('onException');
set_error_handler('onError');

App::init($config);

?>
