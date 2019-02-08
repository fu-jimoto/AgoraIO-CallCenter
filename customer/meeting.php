<?php
    session_start();
    if($_SESSION["auth"] == null){
        header("Location: index.php");
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="../AgoraSig-1.4.0.js"></script>
    <script src="../AgoraRTCSDK-2.5.0.js"></script>
    <title>customer-meeting</title>
</head>

<body>
<div class="container">
<div id="local_screen" style="float:right;width:420px;height:300px;display:inline-block;"></div>

<div id="video" style="margin:0 auto;">
    <div id="local_video" style="float:right;width:210px;height:147px;display:inline-block;"></div>
    <div id="remote_video" style="float:right;width:210px;height:147px;display:inline-block;"></div>
</div>

<tr>
    <td><input id="textMessage"></td>
</tr>

<div>
    <button id="muteVideo" onclick="muteVideo()">カメラ on/off</button>
    <button id="muteMic" onclick="muteMic()"></button>
    <button type="button" class="btn btn-info" id="callAgent" onclick="channelInvite()">agent呼出</button>
    <button type="button" class="btn btn-secondary" id="leaveMeeting" onclick="leave()">Logout</button>
</div>
</div>

<script language="javascript">
//20180110_ for Video
var appId = "62ec47cc139b4f12a05b82d2ffd91c47";
var channelKey = null;
var channelName = "icu";
var videoUid = <?php print($_SESSION["auth"][2])?>;
var screenUid = <?php print($_SESSION["auth"][3])?>;
var videoClient, screenClient, videoLocalStream, screenLocalStream, camera, microphone;
var localStreams = [];

var isMuteVideo = false;

//20181120_for Signaling
var signal = Signal(appId);
var session, account, token, reconnect_count, reconnect_time, call, channel, sigRemoteUid;
account = "s10001";
token = "_no_need_token";
reconnect_count = 10;
recconect_time = 30;
//20181130
sigRemoteUid = "s11001";

//20181120_Log into Agora's Signaling System
// session = signal.login(account,token,reconnect_count,reconnect_time);
session = signal.login(account,token);
session.onLoginSuccess = function(uid){
    console.log("Sig login success "+uid);

    //20190116_A Call Request has been Received
    session.onInviteReceived = function(channel, uid, extra){
        console.log("Receive invite meeting from " + channel + " " + uid + " " + extra);
        //20190117_
        window.confirm("呼ばれていますよ");
    }

    //20181122/26_Join a Channel(Sig)
    channel = session.channelJoin(channelName);
    channel.onChannelJoined = function(){
        console.log(account + " Sig channel join " + channelName + " successfully");
    }
    channel.onChannelJoinFailed = function(ecode){
        console.log(account + " Sig channel join failed " + ecode);
    }
}
session.onLoginFailed = function(ecode){
    console.log("Sig login failed " + ecode);
}

//20181214_Sig Error
session.onError = function(evt){
    console.log("onError");
}

//Video Client
//20181025/26_Create a Video Client
videoClient = AgoraRTC.createClient({mode: "live", codec: "h264"});
//20181026_Initialize the Client
videoClient.init(appId, function(){
    console.log("AgoraRTC videoClient initialized");
    //20181026_Join a Channel
    //20181029_joinの引数を変数に変更(uidはセッションで取得した値に)
    videoClient.join(channelKey, channelName, videoUid, function(uid){
        console.log("videoClient " + videoUid + " join channel successfully");

        //20181220_Save the returned uid
        localStreams.push(uid);
        console.log("localStreams.push by videoClient " + uid);

        //20181026_Create a stream
        videoLocalStream = AgoraRTC.createStream({
            streamID: videoUid,
            audio: true,
            video: true,
            screen: false
        });

        //Initialize the stream
        videoLocalStream.init(function(){
            console.log(videoUid + "getUserMedia successfully");
            //20181029_Play the stream
            videoLocalStream.play("local_video");

            //20181030_Publish the local stream
            videoClient.publish(videoLocalStream, function(err){
                console.log("Publish video local stream error: "+err);
            });

            videoClient.on("stream-published", function(evt){
                console.log("Publish video local stream successfully");
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

//20181107/09_Subscribe to the remote stream by videoClient
//to detect when a new stream is added to the channel
videoClient.on("stream-added", function(evt){
    var stream = evt.stream;
    var uid = stream.getId();
    console.log("New stream added: "+ uid);

    //20181114/16_Check if the stream is a local uid
    if(!localStreams.includes(uid)){
        console.log("subscribe stream:"+uid);
        //to subscribe the stream
        videoClient.subscribe(stream, function(err){
            console.log("Subscribe stream failed", err);
        });
    }
});

videoClient.on("stream-subscribed",function(evt){
    var stream = evt.stream;
    var uid = stream.getId();
    stream.play("remote_video");
    console.log("Subscribe remote stream successfully: " + uid);
});

//20181119_Leave other Client
videoClient.on("peer-leave",function(evt){
    var stream = evt.stream;
    var uid = stream.getId();
    if(stream){
        stream.stop();
        $("#remote_video" + uid).remove();
    }
});


//Screen Client
//20181105_Create a Screen Client
screenClient = AgoraRTC.createClient({mode:"live",codec:"h264"});
screenClient.init(appId, function(){
    console.log("AgoraRTC screenClient initialized");
    screenClient.join(channelKey, channelName,screenUid,function(uid){
        console.log("screenClient " + screenUid + " join channel successfully");

        //20181220_Save the returned uid
        localStreams.push(uid);
        console.log("localStreams.push by screenClient" + uid);

        //20181105_Create a Screen Stream
        screenLocalStream = AgoraRTC.createStream({
            streamID: screenUid,
            audio: false,
            video: false,
            screen: true,
            mediaSource: "window"
        });
        
        screenLocalStream.init(function(){
            console.log(screenUid + "getUserMedia successfully");
            screenLocalStream.play("local_screen");

            screenClient.publish(screenLocalStream, function(err){
                console.log("Publish screen local stream error: "+err);
            });
            screenClient.on("stream-published", function(evt){
                console.log("Publish screen local stream successfully");
            });
        }, function(err){
            console.log("getUserMedia failed", err);
        });
    }, function(err){
        console.log("Join channel failed", err);
    });
}, function(err){
    console.log("AgoraRTC screenClient init failed", err);
});

//20181220_Subscribe to the remote stream by screenClient


//20181112_Leave the Channel
function leave(){
    videoClient.leave(function(){
        console.log("Leave channel successfully");
    }, function(err){
        console.log("Leave channel failed");
    });

    screenClient.leave(function(){
        console.log("Leave channel successfully");
    }, function(err){
        console.log("Leave channel failed");
    });
    
    location.href = "logout.php";
}

//20190110_Mute Video
function muteVideo(){
    //20190111_Layout visibilityを追加
    if(isMuteVideo == false){
        document.getElementById("local_video").style.visibility = "hidden";
        videoLocalStream.disableVideo();
        isMuteVideo = true;
    }else{
        document.getElementById("local_video").style.visibility = "visible";
        videoLocalStream.enableVideo();
        isMuteVideo = false;
    }
}

//20180117/21_Change Layout
function changeLayout(){
    var changeLocalVideo = document.getElementById("local_video");
    var changeLovalScreen = decoument.getElementById("local_screen");
    changeLocalScreen.innerHTML = '<div id="local_video" style="float:right;width:210px;height:147px;display:inline-block;"></div>';
}

//20181120_Channel Invite
function channelInvite(){
    var extra = JSON.stringify({hi:'from icu'});
    call = session.channelInviteUser2(channelName, sigRemoteUid, extra);
    //20181129_The Remote Calling Process has Succeeded
    session.cb = function(err, ret){
        console.log("session.cb" + err + " " + ret);
    }

    call.onInviteReceivedByPeer = function(){
        console.log("Invite received by " + sigRemoteUid);
        //20181203
        call.channelInviteAccept = function(extra){
            console.log("Channel invite accept " + extra);
        }
        call.onInviteAcceptedByPeer = function(extra){
            console.log("Invite accepted by " + sigRemoteUid + extra);
        }
        call.onInviteRefusedByPeer = function(extra){
            console.log("Invite refused by "+ sigRemoteUid + extra);
        }
    }
    //20181203_A Call has Failed
    call.onInviteFailed = function(extra){
        console.log("Invite failed");
    }
}

</script>
</body>
</html>