<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <!-- 20190130 レスポンシブWebデザインを使うために必要なmetaタグ -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- 20190130 -->
    <!-- <link href="index.css" rel="stylesheet" type="text/css"> -->
    <!-- BootstrapのCSS読み込み -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
         -->
    <!-- <link href="../bootstrap-4.2.1-dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- jQuery読み込み -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <!-- BootstrapのJS読み込み -->
    <!-- <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"></script> -->
    <!-- <script src="../bootstrap-4.2.1-dist/css/bootstrap.min.css"> -->
    <title>customer login</title>
</head>

<body>
<!-- 20190204_Bootsnipp style colorgraph -->
<div class="container">
<div calss="wrapper">
    <form action="login.php" method="post">
        <h3 class="form-signin-heading">Welcome to AgoraIO-CallCenter</h3>
            <hr class="colorgraph"><br>

            <input type="text" class="form-control" name="id" placeholder="ID" required="" autofocus="" value="">
            <input type="password" class="form-control" name="pass" placeholder="Password" required="" value="">

            <button class="btn btn-primary btn-block" id="btnLogin" value="Login">Login</button>
    </form>
</div>
</div>
</body>
</html>
