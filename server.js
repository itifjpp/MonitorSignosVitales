/* Monitor se Singnos Vitales */
var express=require('express');
var app=express();
var http = require('http').createServer(app);
var fs = require('fs')  ;
var location = require('location-href');
var path    = require("path");
var chokidar = require('chokidar');
var open = require('open');
var fsCopy = require('fs-extra');
var watcher = chokidar.watch('C://Program Files (x86)//Welch Allyn//NCE//SavedCopies//', {
    ignored: /^\./, 
    persistent: true
});

path.dirname('xml');
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

http.listen(5001,function () {
    console.log('EL SERVIDOR HTTP ESTA LISTO Y ESCUCHANDO EN EL PUERTO 5001 :)');
});



