<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <title>agent login</title>
</head>

<body>
<div class="container">
<div class="wrapper">
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
