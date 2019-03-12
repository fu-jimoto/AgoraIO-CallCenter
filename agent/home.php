<?php
    session_start();
    if($_SESSION["auth"] == null){
        header("Location: index.php");
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="../AgoraSig-1.4.0.js"></script>
    <script src="../AgoraRTCSDK-2.5.0.js"></script>
    <title>agent home</title>
</head>

<body>
<div class="container">
    <div>
        <button id="btnLogout" onclick="logout()">ログアウト</button>
    </div>
    <div class="tabs">
        <input id="icu" type="radio" name="tab_item" checked>
        <label class="tab_item" for="icu">customer一覧</label>
        <input id="private" type="radio" name="tab_item">
        <label class="tab_item" for="private">agentプライベート通話</label>
    
    <div class="tab_content" id="icu_content">
        <div id="room1" style="padding: 10px; margin-bottom: 10px; border: 1px solid #333333;">
            <h1>Room1:</h1>
            <div>Dr:</div>
            <div>Ns:</div>
            <div>
                <button id="btnStartMtg1" onclick="startMtg()">入室</button>
                <button id="btnLinsteningOnly1" onclick="">傍聴</button>
                <button id="btnInviteAccept1" style="display:none" onclick="startMtg()">呼び出し</button>
            </div>
        </div>
    </div>
    </div>
</div>

<script language="javascript">

//20181120
var appId = "62ec47cc139b4f12a05b82d2ffd91c47";
var channelName = "icu";
var signal = Signal(appId);
var session, account, token, reconnect_count, reconnect_time, call, channel;
account = "s11001";
token = "_no_need_token";
reconnect_count = 10;
recconect_time = 30;

//20181120_Log into Agora's Signaling System
session = signal.login(account, token, reconnect_count, reconnect_time);
session.onLoginSuccess = function(uid){
    console.log("Sig login success " + uid);
    console.log(account);

    //20181120_A Call Request has been Received
    session.onInviteReceived = function(channel, uid, extra){
        console.log("Receive invite meeting from "+ channel + " " + uid + " " + extra);
        //20181205_
        $('#btnInviteAccept1').show();
    }
}
session.onLoginFailed = function(ecode){
    console.log("Sig login failed" + ecode);
}

//20190115_evtを追記
session.onError = function(evt){
    console.log("onError " + evt);
}

//20181108_SatrMtg
function startMtg(){
    location.href = "meeting.php";
}
    
//20181115_logout
function logout(){
    location.href = "logout.php";
}
</script>
</body>
</html>