<!doctype html>
<html lang="en">
<head>

    <title>Document</title>
    <script type="text/javascript" src="qrcode.js"></script>

    <style>
        html, body {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .main {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            width: 50%;
            height: 30%;
        }

        .main h {
            text-align: center;
            font-size: 80px;
            font-family: "12롯데마트드림Bold";
        }

        button {
            width: 224px;
            height: 64px;
            background-color: #1473E6;
            box-shadow: 0px 3px 6px gray;
            border: none;
            color: white;
            border-radius: 32px;
            font-size: 22px;
            font-family: "나눔 고딕";
        }

        button:active {
            background-color: #14E6B4;
        }

        .qr {
            margin-top: 20px;
            width: 100px;
            height: 100px;
        }

        .qrpls {
            margin-top: 20px;
            color: red;
            font-size: 22px;
            font-family: "나눔 스퀘어";
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="main">
        <h>VR Monitor</h>
        <button id="init">시작하기</button>
    </div>
    <div id="qrpls" class="qrpls"></div>
    <div class="qr" id="qrcode">

    </div>
    <script>
        let vid = document.getElementById("vid");
        let stream2;
        let peer1;
        let log = console.log;
        let ws = new WebSocket("ws://localhost:3000");
        let receiveAnswer;
        let isMatch = false;
        let thisIp;
        let cap;

        function sendws(str) {
            ws.send(str);
        }

        function startCapture() {
            let captureStream = null;

            // chrome : getDisplayMedia
            // ff : getUserMedia
            captureStream = navigator.mediaDevices.getDisplayMedia({video: {mediaSource: "screen"}, audio: true}).then(stream=>{
                stream2 = stream;
            }).then(() => {
                startCap();
            });


            return captureStream;
        }

        async function onIceCandidate(pc, event) {
            try {;
                let j_obj = {type: "candidate", value: event.candidate};

                if(event.candidate != null)
                    sendws(JSON.stringify(j_obj));
            } catch (e) {
                log(e);
            }
        }

        async function onCreateOfferSuccess(desc) {
            log('pc2 setRemoteDescription start');
            try {
                await peer1.setLocalDescription(desc).then(function () {
                    let jObj = { type: "data", value: desc.sdp };
                    log("send offer");
                    sendws(JSON.stringify(jObj));
                });
            } catch (e) { log("remote1.error"); }

        }

        async function onCreateAnswerSuccess(desc){
            try {
                await peer1.setRemoteDescription(desc).then(function () {
                    log('pc1 setRemoteDescription start');
                });

            } catch (e) {
                log(e);
            }
        }

        function onIceStateChange(pc, event) {
            if (pc) {
                log(`${pc} ICE state: ${pc.iceConnectionState}`);
                log('ICE state change event: ', event);
                if(pc.iceConnectionState == "checking") {
                    log(pc.sdp);
                }
            }
        }

        async function startCap(){
            const videoTracks = stream2.getVideoTracks();
            const audioTracks = stream2.getAudioTracks();
            if(videoTracks.length > 0 || audioTracks.length > 0){
                peer1 = new RTCPeerConnection();

                peer1.onicecandidate = e => onIceCandidate(peer1, e);
                peer1.oniceconnectionstatechange = e => onIceStateChange(peer1, e);
                peer1.ondatachannel = e => log("data : " + e);

                stream2.getTracks().forEach(track => peer1.addTrack(track, stream2));
                const offer = await peer1.createOffer({offerToReceiveAudio: 1, offerToReceiveVideo: 1}).then(async function (offer) {
                    await onCreateOfferSuccess(offer);
                });
            }
        }

        window.onclose = () => {
            ws.close();
        }

        ws.onopen = e => {
            let jObj = { type: "connect", value: "server" };
            sendws(JSON.stringify(jObj));
        }

        ws.onerror = e => {log("ws error" + e);}

        ws.onmessage = e => {
            let g_obj = JSON.parse(e.data);
            switch (g_obj.type) {
                case "normal":
                    isMatch = true;
                    break;
                case "data":
                    receiveAnswer = new RTCSessionDescription({type: "answer"});
                    receiveAnswer.sdp = g_obj.value;
                    log("received Answer");
                    // receiveAnswer.type = "answer";
                    onCreateAnswerSuccess(receiveAnswer);
                case "candidate":
                    let newCandidate = new RTCIceCandidate(g_obj.value);
                    log("value : " + g_obj.value);
                    peer1.addIceCandidate(newCandidate);
                case "ip":
                    thisIp = g_obj.value;
            };
        }

        function InitVR() {
            // location.href = "vrmonitor:0.0.0.0";

            let qrcodeObj = document.getElementById("qrcode");
            qrcodeObj.innerHTML = "";

            let code = new QRCode(qrcodeObj, {
                width: 100,
                height: 100
            });

            code.makeCode(thisIp);

            document.getElementById("qrpls").innerText = "QR코드를 찍어주세요";
            let initButton = document.getElementById("init");

            let cap = startCapture();
        }

        document.getElementById("init").onclick = InitVR;

    </script>

</body>
</html>
