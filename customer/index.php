<!DOCTYPE html>
<html>
<head>
    <!-- 20190130 レスポンシブWebデザインを使うために必要なmetaタグ -->
    <meta name="viewport" content = "width=device-width, initial-scale=1">
    <!-- 20190130 -->
    <!-- BootstrapのCSS読み込み -->
    <link href="../bootstrap-4.2.1-dist/css/bootstrap.min.css" rel = "stylesheet">
    <!-- jQuery読み込み -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <!-- BootstrapのJS読み込み -->
    <script src="../bootstrap-4.2.1-dist/js/bootstrap.min.js"></script>
</head>

<title>customerログイン</title>
<body>
<!-- <div class="table-responsive"> -->
<table class="table table-bordered">
<form action="login.php" method="post">
    <tr>
        <th>ID</th>
        <td><input name="id" type="text" value="customer"></td>
    </tr>
    <tr>
        <th>Password</th>
        <td><input name="pass" type="password" value="password"></td>
    </tr>
    <!-- <tr>
        <th><button type="button" class="btn btn-primary">ログイン</button></th>
    </tr> -->
</form>
</table>
<!-- </div> -->
<button type="button" class="btn btn-primary">ログイン</button>
</body>



</html>
