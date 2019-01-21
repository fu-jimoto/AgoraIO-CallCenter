<?php
    print "index.php";

    //20181024_セッションスタート
    session_start();

    //20181023_アカウント一致確認表示
    $resultCheckAccount = checkAccountFromCsv($_POST["id"], $_POST["pass"]);

    //20181024/26_判定方法変更
    if($resultCheckAccount != null){
        $_SESSION["auth"] = $resultCheckAccount;
        $login_success_url = "home.php";
        header("Location: {$login_success_url}");
        exit;
    }else{
        $error_message = "※ID、もしくはパスワードが間違っています。<br>　もう一度入力して下さい。";
        print_r($error_message);
    }

    //20181023_index.phpとCSV内のアカウントの一致確認
    //20181026_戻り値をtrue/falseから配列に変更
    function checkAccountFromCsv($id, $pass){
        $fp = fopen("account.csv","r");
        while(($data = fgetcsv($fp)) !== FALSE){
            if($data[0] == $id && $data[1] == $pass){
                return array($data[0],$data[1],$data[2],$data[3]);
            }
        }
        return null;
    }

    // print "index.php";
    // print "<br />";
    
    //20181016_取得アカウント表示
    // $res = getData();
    // print_r($res);
    // print "<br />";

    // //ログイン判定
    // session_start();

    // $error_message = "";

    // if(isset($_POST)["btnLogin"]){
    //     if($_POST["id"] == "root" && $_POST["pass"] == "password"){
    //         $_SESSION["id"] = $_POST["id"];
    //         $login_success_url = "meeting.php";
    //         header("Location: {$login_success_url}");
    //         exit;
    //     }

    //     $error_message = "ID、もしくはパスワードが間違っています。<br>　もう一度入力して下さい。";
    // }

    // //20181016_index.phpで入力されたアカウント取得
    // function getData(){
    //     $id = $_POST["id"];
    //     $pass = $_POST["pass"];
    //     $data = array("id"=>$id, "pass"=>$pass);
    //     return $data;
    // }
?>



