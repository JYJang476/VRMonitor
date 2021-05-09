<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script type="text/javascript" src="http://jsgetip.appspot.com"></script>
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
    <script>
        let ips = [];
        window.onload = () => {
            let rtc = new RTCPeerConnection();
            let i = 0;

            function InitVR() {
                location.href = "vrmonitor:0.0.0.0";
                let qrcodeObj = document.getElementById("qrcode");
                qrcodeObj.innerHTML = "";

                let code = new QRCode(qrcodeObj, {
                    width: 100,
                    height: 100
                });

                code.makeCode("https://github.com/Marlamin/VROverlayTest");

                document.getElementById("qrpls").innerText = "QR코드를 찍어주세요";
                let initButton = document.getElementById("init");

            }

            document.getElementById("init").onclick = InitVR;
            rtc.createOffer().then(offer=>rtc.setLocalDescription(offer));

        }
    </script>

    <div class="main">
        <h>VR Monitor</h>
        <button id="init">시작하기</button>
    </div>
    <div id="qrpls" class="qrpls"></div>
    <div class="qr" id="qrcode">

    </div>
</body>
</html>