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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="meeting.css" type="text/css">
    <title>customer meeting / AgoraIO-CallCenter</title>
</head>

<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
            <div id="remote_screen" style="float:center;width:600px;height:450px;display:inline-block;background-color:#999999;"></div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3">
            <div class="row" id="remote_video" style="float:center;width:300px;height:225px;display:inline-block;background-color:#999999;"></div>            
            <div class="row" id="local_video" style="float:center;width:300px;height:225px;display:inline-block;background-color:#999999;"></div>            
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3">
            <div class="row">
                <textarea id="textMessageBox" style="float:center;width:340px;height:425px;"></textarea>
            </div>
            <div class="row">
                <input id="textMessage" value="" size="30">
                <button type="button" class="btn btn-primary" id="sendMessage" onclick="sendMessage()">send</button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <!-- <button id="muteMic" onclick="muteMic()"></button> -->
            <!-- <button id="muteVideo" onclick="muteVideo()">カメラ on/off</button> -->
            <!-- Ref. Agora sample code
            <div active="true" type="mic" class="icon-btn"><img class="icon-btn--active" src="../icons/mic_enable_32px.png" alt></div>
            <div active="true" type="camera" class="icon-btn"><img class="icon-btn--active" src="../icons/camera_enable_32px.png" alt></div> -->
            <button type="button" class="icon-btn" onclick="muteMic()"><img src="../icons/mic_enable_32px.png" alt="mic_icon" id="mic_icon"></button>
            <button type="button" class="icon-btn" onclick="muteVideo()"><img src="../icons/video_enable_32px.png" alt="video_icon" id="video_icon"></button>
            <button type="button" class="btn btn-info" id="callAgent" onclick="channelInvite()">Call agent</button>
            <button type="button" class="btn btn-secondary" id="leaveChannel" onclick="leaveChannel()">Exit</button>
        </div>
    </div>

</div>

<!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script> -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="../AgoraSig-1.4.0.js"></script>
<script src="../AgoraRTCSDK-2.5.0.js"></script>
<script language="javascript">
//Video
var appId = "62ec47cc139b4f12a05b82d2ffd91c47";
var channelKey = null;
var channelName = "CallCenter";
var videoUid = <?php print($_SESSION["auth"][2])?>;
var videoClient, videoLocalStream;
var localStreams = [];

var isMuteMic = false;
var isMuteVideo = false;

//Signaling
var signal = Signal(appId);
var session, call, channel;
var account = "customerSignalingAccount";
var token = "_no_need_token";
var reconnect_count = 10;
var reconnect_time = 30;
//20181130
var sigRemoteUid = "agentSignalingAccount";


//Video
//Video Client
//20181025/26_Create a Video Client
videoClient = AgoraRTC.createClient({mode: "live", codec: "vp8"});
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
                console.log("Publish video local stream successfully: " + uid);
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
    console.log("New stream added: " + uid);

    //20181114/16_Check if the stream is a local uid
    if(!localStreams.includes(uid)){
        console.log("subscribe stream: " + uid);
        //to subscribe the stream
        videoClient.subscribe(stream, function(err){
            console.log("Subscribe stream failed", err);
        });
    }
});

videoClient.on("stream-subscribed",function(evt){
    var stream = evt.stream;
    var uid = stream.getId();
    //20190304
    if(uid == 10001 || uid == 10001 || uid == 10003){
        stream.play("remote_video");
        console.log("Subscribe remote video stream successfully: " + uid);
    }else if(uid == 20001 || uid == 20002 || uid == 20003){
        stream.play("remote_screen");
        console.log("Subscribe remote screen stream successfully: " + uid);
    }
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

//20190225_Mute Mic
function muteMic(){
    if(isMuteMic == false){
        videoLocalStream.disableAudio();
        document.getElementById("mic_icon").src = "../icons/mic_mute_32px.png";
        isMuteMic = true;
    }else{
        videoLocalStream.enableAudio();
        document.getElementById("mic_icon").src = "../icons/mic_enable_32px.png";
        isMuteMic = false;
    }
}

//20190110_Mute Video
function muteVideo(){
    //20190111_Layout visibilityを追加
    if(isMuteVideo == false){
        // document.getElementById("local_video").style.visibility = "hidden";
        videoLocalStream.disableVideo();
        //20190325_icon
        document.getElementById("video_icon").src = "../icons/video_mute_32px.png";
        console.log("Local video muted");
        isMuteVideo = true;
    }else{
        // document.getElementById("local_video").style.visibility = "visible";
        videoLocalStream.enableVideo();
        //20190325_icon
        document.getElementById("video_icon").src = "../icons/video_enable_32px.png";
        console.log("Local Video enabled");
        isMuteVideo = false;
    }
}

//20180117/21_Change Layout
function changeLayout(){
    var changeLocalVideo = document.getElementById("local_video");
    var changeLovalScreen = decoument.getElementById("remote_screen");
    changeLocalScreen.innerHTML = '<div id="local_video" style="float:right;width:210px;height:147px;display:inline-block;"></div>';
}

//20181112_Leave the Channel
function leaveChannel(){
    videoClient.leave(function(){
        console.log("Leave channel successfully");
    }, function(err){
        console.log("Leave channel failed");
    });
    
    //20190323_Sig leave a channel
    channel.channelLeave(function(){
        console.log("Siganling leave channel successfully");
    });

    location.href = "logout.php";
}


//Signaling
//20181120_Log into Agora's Signaling System
signal.setDoLog(true);
session = signal.login(account, token, reconnect_count, reconnect_time);
session.onLoginSuccess = function(uid){
    console.log("Sig login success " + uid);

    //20181122/26_Join a channel(sig)
    channel = session.channelJoin(channelName);
    channel.onChannelJoined = function(){
        console.log(account + " : " + uid + " channel join success");

        //20190323_Another user has left the channel
        channel.onChannelUserLeaved = function(account, uid){
            console.log("leave channel : " + account + " " + uid);
        }

        //20190311_A channel message has been received
        channel.onMessageChannelReceive = function(account, uid, msg){
            if(msg != "\S"){
                console.log("onMessageChannelReceive from " + account + " : " + msg);
                addMessage(account, msg); 
            }else{
                console.log("onMessageChannelReceive from " + account + " : space");
            }
        }
    }
    channel.onChannelJoinFailed = function(ecode){
        console.log(account + " : " + uid + " Sig channel join failed " + ecode);
    }
}

session.onLoginFailed = function(ecode){
    console.log("Sig login failed " + ecode);
}

//20181214_Sig error
session.onError = function(evt){
    console.log("onError " + evt);
}

//20190305_Send message
function sendMessage(){
    channel.messageChannelSend($("#textMessage").val(), function(){
        $("#textMessage").val("");
    });
}

function addMessage(account, msg){
    var currentMessage = $("#textMessageBox").val();
    if(account == "customerSignalingAccount"){
        $("#textMessageBox").val(currentMessage + "you : " + msg + "\n");
    }else{
        $("#textMessageBox").val(currentMessage + "agent : " + msg + "\n");
    }
}

//20181120_Channel invite
function channelInvite(){
    var extra = JSON.stringify({hi:'from agent'});
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
    //20181203_A call has failed
    call.onInviteFailed = function(extra){
        console.log("Invite failed");
    }
}
</script>
</body>
</html>