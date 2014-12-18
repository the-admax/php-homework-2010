<? if($outgoing): ?>
<h3>Отправленные</h3>
<? else: ?>
<h3>Принятые</h3>
<? endif; ?>

<table border="1">
    <thead>
        <th>Новое?</th>
        <th>Тема</th>
        <? if($outgoing): ?>
        <th>Получатель</th>
        <? else: ?>
        <th>Отправитель</th>
        <? endif; ?>
    </thead>
    <tbody>
        <tr><td></td>
            <td><h5><?=$msg->title;?></h5>
                <i><?=$msg->whenSent;?></i>
            </td>
            <td><a href="user.php?<?=($outgoing ? $to->name : $to->name);?>">
                <?=($outgoing ? $to->name : $to->name);?></a>
            </td>
        </tr>
    </tbody>
</table>