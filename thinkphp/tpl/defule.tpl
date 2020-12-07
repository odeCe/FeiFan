{__NOLAYOUT__}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
    <meta charset="utf-8">
    <title>跳转提示</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">


    <link rel="stylesheet" href="__STATIC__/xadmin/css/font.css">
    <link rel="stylesheet" href="__STATIC__/xadmin/css/xadmin.css">
    <link id="layuicss-layer" rel="stylesheet" href="https://www.layui.com/admin/std/dist/layuiadmin/layui/css/modules/layer/default/layer.css?v=3.1.1" media="all"></head>
<body layadmin-themealias="classic-black">


<div class="layui-fluid">
    <div style="text-align: center">

        <?php switch ($code) {?>
        <?php case 1:?>
        <i class="layui-icon layui-icon-face-smile" style="font-size: 200px; color: #15ff07;"></i>
        <?php break;?>
        <?php case 0:?>
        <i class="layui-icon layui-icon-face-cry" style="font-size: 200px; color: #ff0003;"></i>
        <?php break;?>
        <?php } ?>
        <br>
        <br>
        <br>




        <div class="layui-text" style="font-size: 20px;">
            <h1>
                <?php echo(strip_tags($msg));?>
            </h1>
            <br>
            <br>
            页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>
        </div>

    </div>
</div>

<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),
            href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>



</div>
</body>
</html>
