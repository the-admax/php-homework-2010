
<? if($nousers): ?>
    <h4>Пользователей нет</h4>
<? else: ?>
<table class="users">
    <thead>
        <th></th>
        <th>Логин</th>
        <th>Отображаемое имя</th>
        <th>Класс доступа</th>
        <th>Email</th>
        <th>Зарегистрирован</th>
    </thead>

    <tbody>
    <?  $user = new User();
        foreach($users as $userdata):
        $user->load($userdata);
    ?>
        <tr><td>
        <? if(App::$user->class == 'admin'): ?>
            <a href="user.php?<?=$user->uid;?>/delete">Удалить</a> |
            <a href="user.php?<?=$user->uid;?>/edit">Редактировать</a>
        <? endif; ?>
            </td>
            <td><?=$user->login;?></td>
            <td><a href="user.php?<?=$user->uid;?>"><?=$user->name;?></a></td>
            <td><?=User::userClasses($user->class);?></td>
            <td><?=$user->email;?></td>
            <td><?=$user->whenRegistered;?></td>
        </tr>
    <? endforeach;?>
    </tbody>
</table>

<? endif; ?>