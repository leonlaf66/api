<?php if (isset($_GET['Ulj!@*$%@Abc'])):?>
<!DOCTYPE html>
<html lang='zh-CN'>
<head>
<meta charset='utf-8'>
<meta content='IE=edge' http-equiv='X-UA-Compatible'>
<title>Usleju API使用说明</title>
<style type="text/css">
body {
    background:#f0f0f0;
    padding-bottom:30px;
}
.message {
    position:fixed;
    left:0;bottom:0;width:100%;
    background:#e40;
    color:#fff;
    padding:5px 10px;
    font-size:12px;
}
h1 {
    font-size:20px;
}
h1 > span {font-size:16px;font-weight:200;color:#777;}
.help {margin-bottom:15px;}
.help > h2 {
    margin:0;padding:0;font-size:16px;
}
.help > pre {
    display:block;
    padding:15px;background:#fff;border:solid 1px #e2e2e2;border-radius:4px;
    margin-top:5px;
}
a {color:#f20;}
</style>
</head>
<body>
<div class="message">
    ** 强烈建议使用Google Chrome浏览器进行API测试，并安装 ModHeader 以及 JSONView 两个扩展, 并配置ModHeader的app-token及lanugage参数.
</div>

    <h1>Usleju API使用说明 <span>(注意: 该API仅供Usleju内部访用，非开放!)</span></h1>

    <div class="help">
        <h2>访问地址</h2>
<pre>
http://api.usleju.cn/

例如: <a href="http://api.usleju.cn/estate/area/list" target="_blank">http://api.usleju.cn/estate/area/list</a>
</pre>
    </div>
    
    <div class="help">
        <h2>访问token</h2>
<pre>
使用headers发送:
{
    "app-token": "b2e476cb5ddcbf81c337218d5b5d43fa83bd6a8d4c9b7ba4ea047c70d22a828c"
}

注意：该token可能会不定期更换，请APP端应用将该token写在配置里
</pre>
    </div>

    <div class="help">
        <h2>多语言支持</h2>
<pre>
使用headers发送:
{
    "language": "en-US" // 仅支持en-US和zh-CN，请注意大小写
}
</pre>
    </div>

        <div class="help">
        <h2>会员授权访问</h2>
<pre>
Url参数中附加:
{
    "access-token": "用户access-token的值" // 该access-token需要从用户登陆接口中获取
}
</pre>
    </div>

    <div class="help">
        <h2>接口文档</h2>
<pre>
<a href="http://api.usleju.cn/route/api" target="_blank">http://api.usleju.cn/route/api</a>
</pre>
    </div>

    <div class="help">
        <h2>接口返回结构</h2>
<pre>
{
    code: 200, // http状态码
    data: null, // 接口返回的真实数据
    message: 'OK' // 一些其它信息
}
</pre>
    </div>

</body>
<?php endif?>