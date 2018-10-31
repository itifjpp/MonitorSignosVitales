/* Monitor se Singnos Vitales */
var express=require('express');
var app=express();

var fs = require('fs')  ;
var privateKey  = fs.readFileSync('sslcert/sigh.key', 'utf8');
var certificate = fs.readFileSync('sslcert/sigh.crt', 'utf8');
var credentials = {
    key: privateKey, 
    cert: certificate
};
var https = require('https').createServer(credentials,app);
var location = require('location-href');
var path    = require("path");
var chokidar = require('chokidar');
var open = require('open');
var fsCopy = require('fs-extra');
var watcher = chokidar.watch('C://Program Files (x86)//Welch Allyn//NCE//SavedCopies//', {
    ignored: /^\./, 
    persistent: true
});
var socket=require('socket.io');
var io=socket.listen(https);
path.dirname('xml');
app.use(function(req, res, next) {
    // Website you wish to allow to connect
    res.setHeader('Access-Control-Allow-Origin', '*');
    // Request methods you wish to allow
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE');
    // Request headers you wish to allow
    res.setHeader('Access-Control-Allow-Headers', 'X-Requested-With,content-type');
    // Set to true if you need the website to include cookies in the requests sent
    // to the API (e.g. in case you use sessions)
    res.setHeader('Access-Control-Allow-Credentials', true);
    // Pass to next layer of middleware
    next();
});


const Hl7lib = require('nodehl7');
const config = {
        "mapping": false,
        "profiling": true,
        "debug": true,
        "fileEncoding": "iso-8859-1"
};
let hl7parser = new Hl7lib(config);

let callback = function(err, message){
    if (err){
        console.error(err);
    } else {
        var pidSegment = message.get('PID');
	var patientIDs = pidSegment.get('Patient identifier list');
        
        console.log("FOLIO SIGH: "+patientIDs);
        
        var segment4= message.getSegmentAt(4);/*OXIMETRIA*/
        var segment4_identifier=segment4.get('Observation Identifier');
        let segment4_value = segment4.get('Observation Value');
        console.log("SPO2: "+segment4_value);
        
        var segment5= message.getSegmentAt(5);/*FRECIENCIA CARDIACA*/
        var segment5_identifier=segment5.get('Observation Identifier');
        let segment5_value = segment5.get('Observation Value');
        console.log("FC: "+segment5_value);
        
        var segment6= message.getSegmentAt(6);/*SISTOLICA*/
        var segment6_identifier=segment6.get('Observation Identifier');
        let segment6_value = segment6.get('Observation Value');
        console.log("SISTOLICA: "+segment6_value);
        
        var segment7= message.getSegmentAt(7);/*DIASTOLICA*/
        var segment7_identifier=segment7.get('Observation Identifier');
        let segment7_value = segment7.get('Observation Value');
        console.log("DIASTOLICA: "+segment7_value);
        
        var segment8= message.getSegmentAt(8);/*TEMPERATURA*/
        var segment8_identifier=segment8.get('Observation Identifier');
        let segment8_value = segment8.get('Observation Value');
        console.log("TEMPERATURA: "+segment8_value);
    }
};
hl7parser.parseFile('./125986_20181031_144330.txt', callback);

watcher.on('add', function(directory) { 
    if(path.extname(directory)=='.txt'){
        console.log(directory)
        try {
            var xml1=directory.split("\\");
            //fsCopy.copySync(directory, 'xml/'+xml1[5]);
            //open("http://localhost:8085/MonitorSignosVitales?xml="+xml1[5], "Firefox");
//            fs.unlink(directory, function(error) {
//                if (!error) {
//                    console.log('Archivo',xml1[5],'agregado. Iniciando lectura del archivo '+ xml1[5]+'...');
//                    open("http://localhost/MonitorSignosVitales?xml="+xml1[5], "Firefox");
//                }else{
//
//                }
//            });
        } catch (err) {
            console.error(err);
        }    
    }else{
        var xml1=directory.split("\\");
        fs.unlink(directory, function(error) {
            if(!error){
                console.log('El archivo',xml1[5],' no es xml.','Eliminando archivo');
            }
        })
    }

}).on('change', function(directory) {
    var xml1=directory.split("\\");
    console.log('El archivo', xml1[5], 'ha sido modificado');
}).on('unlink', function(directory) {
    var xml1=directory.split("\\");
    console.log('El archivo', xml1[5], 'ha sido eliminado');
}).on('error', function(error) {
    console.error('A ocurrido un error. Error desconocido..', error);
});
io.sockets.on('connect',function(client){  
    client.on('MonitorSignosVitalesListening',function(data){
       io.sockets.emit('MonitorSignosVitalesListening',data); 
    });
});
https.listen(5001,function () {
    console.log('EL SERVIDOR HTTP ESTA LISTO Y ESCUCHANDO EN EL PUERTO 5001 :)');
});




