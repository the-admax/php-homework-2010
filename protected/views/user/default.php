<h3>Профиль 
    <? if($canEdit): ?>
    (<a href="user.php?<?=($id ? $id.'/' : '');?>edit">изменить</a>)
    <? endif; ?>
</h3> 

<table>
    <tr>
    <td>Имя учётной записи (логин):</td>
    <td><?=$user->login;?></td>
    </tr>

    <tr>
    <td>Имя:</td>
    <td><?=$user->name;?></td>
    </tr>

    <tr>
    <td>Электронная почта:</td>
    <td><?=$user->email;?></td>
    </tr>

    <tr>
    <td>Класс доступа:</td>
    <td><?=$user->userClasses($user->class);?></td>
    </tr>

    <tr>
    <td>Зарегистрирован:</td>
    <td><?=$user->whenRegistered;?></td>
    </tr>
</table>