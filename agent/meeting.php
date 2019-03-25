<?php
    session_start();
    if($_SESSION["auth"]==null){
        header("Location: index.php");
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="meeting.css" type="text/css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="../AgoraSig-1.4.0.js"></script>
    <script src="../AgoraRTCSDK-2.5.0.js"></script>
    <title>agent meeting</title>
</head>

<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
            <div id="local_screen" style="float:center;width:600px;height:450px;display:inline-block;background-color:#999999;"></div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3">
            <div class="row" id="remote_video" style="float:center;width:300px;height:225px;display:inline-block;background-color:#999999;"></div>
            <div class="row" id="local_video" style="float:center;width:300px;height:225px;display:inline-block;background-color:#999999;"></div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
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
            <button type="button" class="icon-btn" onclick="muteMic()"><img src="../icons/mic_enable_32px.png" alt="mic_icon" id="mic_icon"></button>
            <button type="button" class="icon-btn" onclick="muteVideo()"><img src="../icons/video_enable_32px.png" alt="video_icon" id="video_icon"></button>
            <button type="button" class="btn btn-secondary" id = "leave" onclick = "leaveChannel()">Exit</button>
        </div>
    </div>
</div>

<script language = "javascript">
//Video
var appId = "62ec47cc139b4f12a05b82d2ffd91c47";
var channelKey = null;
var channelName = "CallCenter";
var videoUid = <?php print($_SESSION["auth"][2])?>;
var screenUid = <?php print($_SESSION["auth"][3])?>;
var videoClient, videoLocalStream, screenClient, screenLocalStream;
var localStreams = [];

var isMuteMic = false;
var isMuteVideo = false;

//Signaling
var signal = Signal(appId);
var session, call, channel;
var account = "agentSignalingAccount";
var token = "_no_need_token";
var reconnect_count = 10;
var reconnect_time = 30;
//20190116
var sigRemoteUid = "s10001";


//Video
//20181113_Create a Video Client
videoClient = AgoraRTC.createClient({mode:"live",codec:"h264"});
videoClient.init(appId, function(){
    console.log("AgoraRTC videoClient initialized");
    videoClient.join(channelKey,channelName,videoUid,function(uid){
        console.log("User "+videoUid+" join channel successfully");

        //20190305_Save the uid of the local stream
        localStreams.push(uid);
        console.log("localStreams pushed by videoClient" + uid);

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

    //20190305_Check if the stream is a local uid
    if(!localStreams.includes(uid)){
        console.log("subscribe stream: " + uid);
        videoClient.subscribe(stream, function(err){
            console.log("Subscribe stream failed", err);
        });
    }
});

videoClient.on("stream-subscribed", function(evt){
    var stream = evt.stream;
    var uid = stream.getId();
    stream.play("remote_video");
    console.log("Subscribe remote video stream successfully: " + uid);
});

//Screen Client
//20181105_Create a Screen Client
screenClient = AgoraRTC.createClient({mode:"live",codec:"h264"});
screenClient.init(appId, function(){
    console.log("AgoraRTC screenClient initialized");
    screenClient.join(channelKey,channelName,screenUid,function(uid){
        console.log("screenClient "+screenUid+" join channel successfully");

        //20181220_Save the uid of the local stream
        localStreams.push(uid);
        console.log("localStreams pushed by screenClient" + uid);

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

//20181119_Leave other Client
videoClient.on("peer-leave", function(evt){
    var stream = evt.stream;
    var uid = stream.getId();
    if(stream){
        stream.stop();
        $("#remote_video" + uid).remove();
    }
});

//20190301_Mute Mic
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

//20190301_Mute Video
function muteVideo(){
    if(isMuteVideo == false){
        // document.getElementById("local_video").style.visibility = "hidden";
        videoLocalStream.disableVideo();
        document.getElementById("video_icon").src = "../icons/video_mute_32px.png";
        console.log("Local Video muted");
        isMuteVideo = true;
    }else{
        // document.getElementById("local_video").style.visibility = "visible";
        videoLocalStream.enableVideo();
        document.getElementById("video_icon").src = "../icons/video_enable_32px.png";
        console.log("Local Video enabled");
        isMuteVideo = false;
    }
}

//20181112_Leave the Channel
function leaveChannel(){
    videoClient.leave(function(){
        console.log("Leave channel successfully videoClient");
    }, function(err){
        console.log("Leave channel failed videoClient");
    });

    screenClient.leave(function(){
        console.log("Leave channel successfully screenClient");
    }, function(err){
        console.log("Leave channel failed screenClient");
    });

    //20190323_Sig leave a channel
    channel.channelLeave(function(){
        console.log("Siganling leave channel successfully");
    });

    location.href = "home.php";
}


//Signaling
//20190115_Log into Agora's Signaling System
session = signal.login(account, token, reconnect_count, reconnect_time);
session.onLoginSuccess = function(uid){
    console.log("Sig login success " + uid);

    //20190311_Join a channel(sig)
    channel = session.channelJoin(channelName);
    channel.onChannelJoined = function(){
        console.log(account + " channel join success");

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
        console.log(account + "Sig channel join failed " + ecode);
    }
}
session.onLoginFailed = function(ecode){
    console.log("Sig login failed " + ecode);
}

session.onError = function(evt){
    console.log("onError " + evt);
}

//20190311_Send Message
function sendMessage(){
    channel.messageChannelSend($("#textMessage").val(), function(){
        $("#textMessage").val("");
    });
}

function addMessage(account, msg){
    var currentMessage = ($("#textMessageBox").val());
    if(account == "agentSignalingAccount"){
        $("#textMessageBox").val(currentMessage + "you :" + msg + "\n");
    }else{
        $("#textMessageBox").val(currentMessage + "customer : " + msg + "\n");
    }
}

//20190115_channelInviteCustomer
function channelInviteCustomer(){
    var extra = JSON.stringify({hi:"from:customer"});
    call = session.channelInviteUser2(channelName, sigRemoteUid, extra);
    session.cb = function(err, ret){
        console.log("session.cb" + err + " " + ret);
    }

    //20190116_A Call has Failed
    call.onInviteFailed = function(extra){
        console.log("Invite failed");
    }
}
</script>

</body>
</html>