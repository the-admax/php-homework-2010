<?php

/* Модель данных пользователя. Содержит его атрибуты и методы работы с ними
 * (проверки), а так же действия, которые можно совершить с моделью */

class User extends DataModel {
    public $uid,
            $name,
            $login,
            $password,
            $class,
            $email,
            $whenRegistered;

    // Псевдонимы колонок таблицы
    public static function attributes() {
        return array(
            'uid'=>'uid',
            'login'=>'login',
            'pwhash'=>'password',
            'name'=>'name',
            'when_registered'=>'whenRegistered',
            'class'=>'class',
            'email'=>'email'
        );
    }

    static function tableName() {
        return 'users';
    }

    static function userClasses($class=false) {
        $a = array(
            ''=>'Гость',
            'user'=>'Зарегистрированный участник',
            'moderator'=>'Модератор',
            'admin'=>'Администратор'
        );
        return $class===false ? $a : $a[$class];
    }

    /* Фильтры, проверяющие и нормализующие значения атрибутов модели.
     * $field - имя атрибута модели
     * $value - значение
     * $on - сценарий, в котором срабатывает фильтр
     * возвращает true, если проверка и/или преобразование успешно выполнено
     */
    public function check($field, &$value, $on='') {
        switch ($field) {
            case 'login':
                return preg_match('/^[a-z][a-z0-9_.-]{2,18}[a-z0-9]$/i', $value);
            case 'password':
                return (strlen($value) >= 4) && (strlen($value) <= 24);
            case 'class':
                return array_key_exists($value, $this->userClasses());
            case 'name':
                $value = trim($value);
                return (strlen($value) <= 30);
            case 'email':
                return preg_match('/^[0-9a-z_.\-]+@[0-9a-z_.\-]+\.[a-z]{2,6}$/i', $value);
        }
    }

    public function assign(User $source) {
        parent::assign($source);
    }

    /* подпрограмма, составляющая запрос для обновления профиля пользователя. Затрагивает
     * только те поля, которые перечисленны в $fields. Если элемент имеет  */
    public function update($fields) {
        $columns = array_flip($this->attributes());

        $set = '';
        foreach($fields as $field=>$expr) {
            if (is_int($field)) {
                $field = $expr;
                $expr = ':'.$expr;
            }
            $set[]= $columns[$field] . '=' . $expr;
        }

        $sql = 'UPDATE '. $this->tableName()
                . ' SET '. implode(',', $set)
                // Здесь по-хорошему нужно перенести этот метод в класс-родитель и
                // Реализовать подстановку ключевого поля автоматически
                . ' WHERE uid=:uid';

        return App::$db->execute($this->prepare($sql)) != false;
    }

    // Добавить учётную запись в БД (используется для регистрации)
    function create() {
        $q = $this->prepare('INSERT INTO users (login,pwhash,name,email) VALUES (:login,SHA1(:password),:name,:email)');
        return App::$db->insert($q);
    }

    // Удалить модель из базы
    function delete() {
        return App::$db->execute($this->prepare('DELETE FROM users WHERE uid=:uid'));
    }

    // Найти пользователя по его идентификатору. Вернёт экземпляр класса User
    static function findById($uid) {
        if( ($info = App::$db->execute('SELECT * FROM users WHERE (uid = ?)',
                array($uid) )->fetch(PDO::FETCH_ASSOC)) != false)
        {
            $user = new User();
            return $user->load($info);
        } else
            return false;
    }

    // Аналог функции findById. Ищет по имени авторизации
    static function findByLogin($login) {
        if( ($info = App::$db->execute('SELECT * FROM users WHERE (login = ?)',
                array($login) )->fetch(PDO::FETCH_ASSOC)) != false)
        {
            $user = new User();
            return $user->load($info);
        } else {
            return false;
        }
    }
    
    // Проверка идентификационной информации.
    function authenicate($username, $password) {
        if( ($info = App::$db->execute('SELECT * FROM users WHERE (login = ?) AND (pwhash = SHA1(?))',
            array($username, $password))->fetch(PDO::FETCH_ASSOC)) != false)
        {
            return $this->load($info);
        } else
            return false;
    }

    static function listUsers($short=false) {
        if ($short) {
            return App::$db->execute('SELECT id,name FROM users')->fetchAll(PDO::FETCH_OBJ);
        } else {
            return App::$db->execute('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

?>
