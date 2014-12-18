<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/* Usage: strip_tags_attributes($string,'<strong><em><a>','href,rel'); */
function strip_tags_attributes($string,$allowtags=NULL,$allowattributes=NULL){
    $string = strip_tags($string,$allowtags);
    if (!is_null($allowattributes)) {
        if(!is_array($allowattributes))
            $allowattributes = explode(",",$allowattributes);
        if(is_array($allowattributes))
            $allowattributes = implode(")(?<!",$allowattributes);
        if (strlen($allowattributes) > 0)
            $allowattributes = "(?<!".$allowattributes.")";
        $string = preg_replace_callback("/<[^>]*>/i",create_function(
            '$matches',
            'return preg_replace("/ [^ =]*'.$allowattributes.'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);'
        ),$string);
    }
    return $string;
}

class Message extends DataModel {
    public $id,
        $sender,
        $recipient,
        $whenSent,
        $isNew,
        $title,
        $body;

    protected $_user;

    function __construct($user) {
        $this->_user = $user;
    }
   
    // Псевдонимы колонок таблицы
    public static function attributes() {
        return array(
            'id'=>'id',
            'sender'=>'sender',
            'recipient'=>'recipient',
            'is_new'=>'isNew',
            'title'=>'title',
            'body'=>'body',
            'when_sent'=>'whenSent'
        );
    }

    static function tableName() {
        return 'messages';
    }

    public function check($field, &$value, $on='') {
        switch ($field) {
            case 'recipient':
                return User::findById((int)$value) != false;
            case 'title':
                return (strlen($value) <= 100);
            case 'body':
                return strlen($value) <= 2000;
        }
    }

    /* Основные методы работы с сообщениями:
     * listMessages($outgoing=false)     -- вывести список сообщений;
     * send()                       -- отправить сообщение;
     * read($id)                    -- прочесть
     * delete($id)                  -- удалить
     *  */

    /* Получить листинг сообщений (массив экземпляров класса Message).
     * Входящие сообщения */
    function listMessages($outgoing=false) {
        if ($outgoing) {
            $on = 'msgs.recipient = users.uid';
            $fld = 'sender';
        } else {
            $on = 'msgs.sender = users.uid';
            $fld = 'recipient';
        }
        $q = $this->prepare(
<<<EOF
            SELECT msgs.id as id,
                    msgs.sender as sender,
                    msgs.recipient as recipient,
                    users.name as name,
                    msgs.is_new as is_new,
                    msgs.when_sent as when_sent,
                    msgs.title as title
                FROM messages AS msgs LEFT JOIN users ON ($on)
                WHERE
                ($fld=:_user) ORDER BY when_sent;
EOF
                );
        
        if( ($rows = $q->fetchAll(PDO::FETCH_OBJ)) != false) {
            $messages = array();
            foreach($rows as $row) {
                $message = new Message($this->_user);

                $message->load($row);
                if ($outgoing) {
                    $message->recipient = array($row['recipient_id'] => $row['name']);
                } else {
                    $message->sender = array($row['sender_id'] => $row['name']);
                }
                
                $messages[$row['id']] = $message;
            }
            return $messages;
        } else {
            return false;
        }
    }

    function send() {
        $q = $this->prepare(
            'INSERT INTO ' . $this->tableName() . '(sender,recipient,title,body) '.
                'VALUES (:_user,:recipient,:title,:body)'
        );
        return App::$db->insert($q);
    }

    function findById($id) {
        return App::$db->execute('SELECT * FROM '.$this->tableName().' WHERE id=?', array($id))->fetch(PDO::FETCH_OBJ);
    }

    function read($id) {
        $q = $this->prepare(
<<< EOF
    SELECT * FROM messages WHERE (sender = :_user OR recipient = :_user) AND (id = :__id);
    UPDATE messages SET is_new=0 WHERE id=:__id
EOF
                );

        if( ($row = App::$db->execute($q, array('__id'=>$id))) != false) {
            $msg = new Message($this->_user);
            return $msg->load($row);
        } else
            return false;
    }

    /* BUG/FEATURE: Если пользователь удалит отправленное сообщение, то оно удалится и у получателя */
    function delete($id) {
        $q = $this->prepare(
            'DELETE FROM ' . $this->tableName() . 
                ' WHERE (sender = :_user OR recipient = :_user) AND (id = :__id)');
        if( ($row = App::$db->execute($q, array('__id'=>$id))) != false) {
            $msg = new Message($this->_user);
            return $msg->load($row);
        } else
            return false;
    }


}
?>
