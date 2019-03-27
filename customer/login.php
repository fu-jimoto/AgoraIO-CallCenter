<?php
    //20181024_セッションスタート
    session_start();

    //20181023_アカウント一致確認表示
    $resultCheckAccount = checkAccountFromCsv($_POST["id"], $_POST["pass"]);

    //20181024/26_判定方法変更
    if($resultCheckAccount != null){
        $_SESSION["auth"] = $resultCheckAccount;
        $login_success_url = "meeting.php";
        header("Location: {$login_success_url}");
        exit;
    }else{
        $error_message = "The account does not exist or the password is incorrect";
        print_r($error_message);
    }

    //20181023_index.phpとCSV内のアカウントの一致確認
    //20181026_戻り値をtrue/falseから配列に変更
    function checkAccountFromCsv($id, $pass){
        $fp = fopen("account.csv","r");
        $array = null;
        while(($data = fgetcsv($fp)) !== FALSE){
            if($data[0] == $id && $data[1] == $pass){
                $array = array($data[0],$data[1],$data[2],$data[3]);
                break;
            }
        }
        return $array;
    }
?>



