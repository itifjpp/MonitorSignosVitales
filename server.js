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
watcher.on('add', function(directory) { 
    if(path.extname(directory)=='.xml'){
        try {
            var xml1=directory.split("\\");
            fsCopy.copySync(directory, 'xml/'+xml1[5]);
            fs.unlink(directory, function(error) {
                if (!error) {
                    console.log('Archivo',xml1[5],'agregado. Iniciando lectura del archivo '+ xml1[5]+'...');
                    open("http://localhost/MonitorSignosVitales?xml="+xml1[5], "Firefox");
                }else{

                }
            });
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




