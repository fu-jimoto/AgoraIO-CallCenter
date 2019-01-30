<!DOCTYPE html>
<html>
<head>customerログイン
    <!-- 20190130 レスポンシブWebデザインを使うために必要なmetaタグ -->
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
</head>
<title>customerログイン</title>

<body>
<form action="login.php" method="post">
    <input name="id" type="text" value="icu"><br />
    <input name="pass" type="password" value="password"><br />
    <button id="btnLogin">ログイン</button>
</form>
</body>

<!-- 20190130 -->
<!-- BootstrapのCSS読み込み -->
<link href = "../bootstrap-4.2.1-dist/css/bootstrap.min.css" rel = "stylesheet">
<!-- jQuery読み込み -->
<script src = "https://ajax.googleapis.com/ajax/libs/jquery.min.js"></script>
<!-- BootstrapのJS読み込み -->
<script src = "../bootstrap-4.2.1-dist/js/bootstrap.min.js"></script>

</html>
