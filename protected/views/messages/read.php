<h4><?=$msg->title;?></h4>
<p>Осторожно! Прежде чем нажать на ссылку в сообщении, проверьте, не
    ссылается ли она на подозрительный сайт или адрес (вроде "<code>javascript:</code>")
</p>
<div class="message">
    <?=$msg->body; ?>
</div>
<a href="messages.php?<?=$msg->id; ?>/reply">Ответить</a>

