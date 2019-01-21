<?php
    session_start();
    if($_SESSION["auth"]==null){
        header("Location: index.php");
    }
?>

<!DOCTYPE html>
<html>
<head>接続中</head>
<title>t-icu Meeting</title>
<body>
<div id="video">
    <div id="local_video" style="float:right;width:210px;height:147px;display:inline-block;"></div>
    <div id="remote_screen" style="float:right;width:420px;height:300px;display:inline-block;"></div>
    <div id="remote_video" style="float:right;width:210px;height:147px;display:inline-block;"></div>
</div>

<div>
    <button id = "channelInviteIcu" onclick = "channelInviteIcu()">病院呼出</button>
    <button id = "urgent">緊急対応要請</button>
    <button id = "cutMeeting" onclick = "leave()">切断</button>
</div>

<script src = "../AgoraRTCSDK-2.5.0.js"></script>
<script src = "../AgoraSig-1.4.0.js"></script>
<script src = "https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script language = "javascript">

var appId = "62ec47cc139b4f12a05b82d2ffd91c47";
var channelKey = null;
var channelName = "icu";
var videoUid = <?php print($_SESSION["auth"][2])?>;
var videoClient, videoLocalStream,camera,microphone;

//20190115_for Signaling
var signal = Signal(appId);
var session, account, token, reconnect_count, reconnect_time, call, channel, sigRemoteUid;
account = "s11001";
token = "_no_need_token";
recconect_count = 10;
recconect_time = 30;
//20190116
sigRemoteUid = "s10001";

//20190115_Log into Agora's Signaling System
session = signal.login(account, token, reconnect_count, reconnect_time);
session.onLoginSuccess = function(uid){
    console.log("Sig login success " + uid);
    console.log(account);
}
session.onLoginFailed = function(ecode){
    console.log("sig login failed " + ecode);
}

session.onError = function(evt){
    console.log("onError " + evt);o
}

//20181113_Create a Video Client
videoClient = AgoraRTC.createClient({mode:"live",codec:"h264"});
videoClient.init(appId, function(){
    console.log("AgoraRTC videoClient initialized");
    videoClient.join(channelKey,channelName,videoUid,function(uid){
        console.log("User "+videoUid+" join channel successfully");
        videoLocalStream=AgoraRTC.createStream({
            streamID: videoUid,
            audio: true,
            video: true,
            screen: false
        });

        videoLocalStream.init(function(){
            console.log("getUserMedia successfully");
            videoLocalStream.play("local_video");

            videoClient.publish(videoLocalStream, function(err){
                console.log("Publish video local stream error: "+err);
            });

            videoClient.on("stream-published", function(evt){
                console.log("Publish video local stream: " + uid + " successfully");
            });
        }, function(err){
            console.log("getUserMedia failed", err);
        });
    }, function(err){
        console.log("Join channel failed", err);
    });
}, function(err){
    console.log("AgoraRTC videoClient init failed", err);
});

//20181113_Subscribe to the remote stream
videoClient.on("stream-added", function(evt){
    var stream = evt.stream;
    var uid = stream.getId();
    console.log("New stream added: "+ uid);

    videoClient.subscribe(stream, function(err){
        console.log("Subscribe stream failed", err);
    });
});

videoClient.on("stream-subscribed", function(evt){
    var stream = evt.stream;
    var uid = stream.getId();
    console.log("Subscribe remote stream successfully: "+ uid);
    if(uid == 30001 || uid == 30002 || uid == 30003){
        stream.play("remote_video");
        console.log("remote_video" + uid + " start playing");
    }else if(uid == 40001 || uid == 40002 || uid == 40003){
        stream.play("remote_screen");
        console.log("remote_screen: " + uid + " start playing");
    }
});

//20181119_Leave other Client
videoClient.on("peer-leave", function(evt){
    var stream = evt.stream;
    var uid = stream.getId();
    if(stream){
        stream.stop();
        $("#remote_screen" + uid).remove();
        $("#remote_video" + uid).remove();
    }
});

//20190115_channelInviteIcu
function channelInviteIcu(){
    var extra = JSON.stringify({hi:"from:t-icu"});
    call = session.channelInviteUser2(channelName, sigRemoteUid, extra);
    session.cb = function(err, ret){
        console.log("session.cb" + err + " " + ret);
    }

    //20190116_A Call has Failed
    call.onInviteFailed = function(extra){
        console.log("Invite failed");
    }
}

//20181112_Leave the Channel
function leave(){
    videoClient.leave(function(){
        console.log("Leave channel successfully");
    }, function(err){
        console.log("Leave channel failed");
    });

    location.href = "home.php";
}

</script>

</body>
</html>