<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ошибка</title>
    <style type="text/css">
        span.caption { color: gray;  }
    </style>
</head>
<body>
    <h3>Ошибка #<?=$errno;?></h3>
<table>
    <tr>
        <td><span class="caption">Описание:</span></td>
        <td><?=$errstr;?></td>
    </tr>
    <tr>
        <td><span class="caption">Источник:</span></td>
        <td><?=$errfile;?>(<?=$errline;?>)</td>
    </tr>
    <tr>
        <td><span class="caption">Стек:</span></td>
        <td><code>
            <? foreach(debug_backtrace() as $trace): ?>
                <?= basename($trace['file']) . '('.$trace['line'].') @ ' . $trace['function']; ?><br/>
            <? endforeach; ?>
        </code></td>
    </tr>
</table>
</body>
</html>
