<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<?php
$key = '^manks.top&swoole$';
$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
$token = md5(md5($uid) . $key);
?>

<div>
    发送内容：<textarea name="content" id="content" cols="30" rows="10"></textarea><br>
    发送给谁：<input type="text" name="toUid" value="" id="toUid"><br>
    <button onclick="send();">发送</button>
</div>

<script>
    var ws = new WebSocket("ws://127.0.0.1:9501?uid=<?php echo $uid ?>&token=<?php echo $token; ?>");
    ws.onopen = function(event) {
    };
    ws.onmessage = function(event) {
        var data = event.data;
        data = eval("("+data+")");
        if (data.event == 'alertTip') {
            alert(data.msg);
        }
    };
    ws.onclose = function(event) {
        console.log('Client has closed.\n');
    };

    function send() {
        var obj = document.getElementById('content');
        var content = obj.value;
        var toUid = document.getElementById('toUid').value;
        ws.send('{"event":"alertTip", "toUid": '+toUid+'}');
    }
</script>
</body>
</html>