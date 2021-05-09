<!doctype html>
<html lang="en">
<head>
            <script src="https://code.jquery.com/pep/0.4.2/pep.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/dat-gui/0.6.2/dat.gui.min.js"></script>
            <script src="https://preview.babylonjs.com/ammo.js"></script>
            <script src="https://preview.babylonjs.com/cannon.js"></script>
            <script src="https://preview.babylonjs.com/Oimo.js"></script>
            <script src="https://preview.babylonjs.com/libktx.js"></script>
            <script src="https://preview.babylonjs.com/earcut.min.js"></script>
            <script src="https://preview.babylonjs.com/babylon.js"></script>
            <script src="https://preview.babylonjs.com/inspector/babylon.inspector.bundle.js"></script>
            <script src="https://preview.babylonjs.com/materialsLibrary/babylonjs.materials.min.js"></script>
            <script src="https://preview.babylonjs.com/proceduralTexturesLibrary/babylonjs.proceduralTextures.min.js"></script>
            <script src="https://preview.babylonjs.com/postProcessesLibrary/babylonjs.postProcess.min.js"></script>
            <script src="https://preview.babylonjs.com/loaders/babylonjs.loaders.js"></script>
            <script src="https://preview.babylonjs.com/serializers/babylonjs.serializers.min.js"></script>
            <script src="https://preview.babylonjs.com/gui/babylon.gui.min.js"></script>

    <title>Document</title>

    <style>
        #renderCanvas {
            width: 100%;
            height: 100%;
        }

        .qr {
            width: 100%;
            height: 300px;
            background-color: black;
        }
    </style>
</head>
<body>
    <canvas id="renderCanvas"></canvas>
    <script>
        let ws = new WebSocket("ws://<?=$_GET['ip']?>:3000");

        let log = console.log;
        let peer;
        let receiveOffer;

        ws.onopen = e => {
            let jObj = { type: "connect", value: "client" };
            sendws(JSON.stringify(jObj));
        }

        ws.onerror = e => {
            log("ws error" + e);

        }

        ws.onmessage = e => {
            let g_obj = JSON.parse(e.data);
            log(e.data);
            switch (g_obj.type) {
                case "data":
                    log("data");
                    receiveOffer = new RTCSessionDescription({type: "offer"});
                    receiveOffer.sdp = g_obj.value;
                    setPeer(receiveOffer);

                    break;
                case "candidate":
                    let newCandidate = new RTCIceCandidate(g_obj.value);
                    console.dir(g_obj.value);
                    peer.addIceCandidate(newCandidate);
                case "onHand":

            }
        }

        function gotRemoteStream(e) {
            setVidTexture(e.streams[0]);
            log('pc2 received remote stream');
        }

        function sendws(str){
            ws.send(str);
        }

        async function onIceCandidate(pc, event) {
            if(event.candidate && event.candidate.length > 0) {
                let j_obj = {type: "candidate", value: event.candidate};
                log(event.candidate);
                sendws(JSON.stringify(j_obj));
            }
        }

        function onIceStateChange(pc, event) {
            if (pc) {
                log(`${pc} ICE state: ${pc.iceConnectionState}`);
                log('ICE state change event: ', event);
            }
        }

        async function setPeer(offer) {
            peer = new RTCPeerConnection({
                iceServers: [{
                    urls: ["stun:stun.l.google.com:19302"]
                }]
            });
            peer.onicecandidate = e => onIceCandidate(peer, e);
            peer.oniceconnectionstatechange = e => onIceStateChange(peer, e);
            peer.addEventListener('track', gotRemoteStream);

            try {
                await peer.setRemoteDescription(offer).then(async function () {
                    const answer = await peer.createAnswer().then(function (answer) {
                        onCreateAnswerSuccess(answer);
                    });
                });
            } catch (e) {
                log(e);
            }
        }

        async function onCreateOfferSuccess(desc) {
            // peer2
            log('pc2 setRemoteDescription start');
            try {
                await peer.setRemoteDescription(desc).then(async function () {
                    const answer = await peer.createAnswer().then(async function() {
                        await onCreateAnswerSuccess(answer);
                    });
                });
            } catch (e) {
                log("remote.error");
            }
        }

        async function onCreateAnswerSuccess(desc){
            try {
                await peer.setLocalDescription(desc).then(function () {
                    let jObj = { type: "data", value: desc.sdp };
                    sendws(JSON.stringify(jObj));
                }).catch((e) => "error");
            } catch (e) {
                log(e);
            }
        }

        window.onclose = () => ws.close();
    </script>
    <script src="babylon.js"></script>
</body>
</html>
