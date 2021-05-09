const { isUndefined } = require('util');

const websock = require('ws').Server;

const wss = new websock({port: 3000});
const ip = require('ip');
var conn_Users = [[]];

wss.on('connection', function(ws, req){
    let reqIP = req.headers['x-forwarded-for'] || req.connection.remoteAddress;
    let thisIndex = 0;
    let userType;

    console.log(reqIP + " 아이피 접속 요청");    
    
    ws.on('message', function(msg){
        let jmsg = JSON.parse(msg);
        // 접속 메세지 : connect
        // 일반 메세지 : normal
        switch(jmsg.type){
            case "connect":
                thisIndex = conn_Users.length;
                userType = jmsg.value;
                // 요청 유저가 서버일 경우
                // 새로운 배열을 생성하여 client를 기다림                
                if (jmsg.value == "server"){                    
                    conn_Users[thisIndex] = [];
                    conn_Users[thisIndex][0] = ws;
                    
                    ws.send(JSON.stringify({ type: "ip", value: ip.address() }));
                }
                else if(jmsg.value == "client") {
                    // 요청 유저가 클라이언트일 경우
                    // 유저 목록을 순회하면서 서버 유저를 매칭시킨다. 
                    let i = 0;

                    for(let usr of conn_Users){
                        if (usr[0] != undefined && usr[1] == undefined){ 
                            thisIndex = i;
                            usr[1] = ws;            
                            console.log("match result " + usr[0] + " " + usr[1]);
                            let jObj = { type: "normal", value: "ok" };
                            usr[0].send(JSON.stringify(jObj));
                        }
                        i++;
                    }
                }
                break;
            case "normal":
                console.log("normal");
                break;
            case "data":                
                let targetSocket = userType == "server" ? conn_Users[thisIndex][1] : conn_Users[thisIndex][0];                
                targetSocket.send(msg);
                break;
            case "candidate":
                let targetSocket2 = userType == "server" ? conn_Users[thisIndex][1] : conn_Users[thisIndex][0];                
                targetSocket2.send(msg);
                break;
        }
    })
});