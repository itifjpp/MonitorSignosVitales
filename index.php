<!DOCTYPE html>
<html>
    <head>
        <title>Monitor de Signos Vitales</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div>Iniciando Lectura de Archivos XML..</div>
    </body>
    <?php
        define('base_domain', $_SERVER['HTTP_HOST']);
    ?>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" type="text/javascript"></script>
    <script src="https://<?=base_domain?>:5001/socket.io/socket.io.js" sighuser="9999" type="text/javascript"></script>
    <script>
        var xml_file ='<?php echo $_GET['xml'];?>';
        var socket=io.connect('https://<?=base_domain?>:5001/');
        
        /* 
            Created on : 22/11/2017, 02:05:50 PM
            Author     : felipe de jesus <itifjpp@gmail.com>
        */
        var sv_sis='';
        var sv_dis='';
        var sv_temp='';
        var sv_fc='';
        var sv_oximetria='';
        var paciente_id=0;
        var ns=0;
        var serial_numers=[];
        var nombres=[];
        var apellidos=[];
        var medico_id='';
        var sv_dia='';
        $(document).ready(function () {
            $.get("http://localhost/MonitorSignosVitales/xml/"+xml_file, {}, function (xml){
                $('MEMBER',xml).each(function(i,e){
                    if($(this).attr('name')=='SerialNumber'){
                        serial_numers.push($(this).find('VALUE').text());
                    }if($(this).attr('name')=='Systolic'){
                        sv_sis=$(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text().trim().substr(0,3);
                    }if($(this).attr('name')=='Diastolic'){
                        sv_dia=$(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text().trim().substr(0,2);
                    }if($(this).attr('name')=='FirstName' ){
                        nombres.push($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text())
                    }
                    if($(this).attr('name')=='LastName' ){
                        apellidos.push($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text());
                    }if($(this).attr('name')=='Identifier'){
                        if($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text()!=''){
                            paciente_id=$(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text().replace(/\s/g,"");
                        }
                    }if($(this).attr('name')=='Sat'){
                        if($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text()!=''){
                            sv_oximetria=$(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text().replace(/\s/g,"");
                            let sv_oxi=sv_oximetria.slice(0,sv_oximetria.length-2);
                            sv_oximetria=sv_oxi;
                        }
                    }if($(this).attr('name')=='Temperature'){
                        if($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text()!=''){
                            sv_temp=$(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text().replace(/\s/g,"");
                            sv_temp=sv_temp.slice(0,sv_temp.length-2);
                            sv_temp=Number(sv_temp)-273.15;

                        }
                    }if($(this).attr('name')=='HR'){
                        if($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text()!=''){
                            sv_fc=$(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text().replace(/\s/g,"");
                            sv_fc=sv_fc.slice(0,sv_fc.length-2);
                        }
                    }if($(this).attr('name')=='IdentifierExt'){
                        medico_id=$(this).text();
                    }

                });
                if(nombres.length==1){
                    nombres.push('');
                    apellidos.push('');
                }
                var InformacionSignosVitales={
                    sv_sis:sv_sis,
                    sv_dia:sv_dia,
                    sv_fc:sv_fc,
                    sv_temp:(sv_temp!='' ? sv_temp.toFixed(1) :''),
                    sv_oximetria:sv_oximetria.substr(0,4),
                    equipo_ns:serial_numers[0],
                    xml_file:xml_file,
                    paciente_nombre:nombres[0],
                    paciente_ap:apellidos[0],
                    medico_id:medico_id.replace(/\s/g,""),
                    medico_nombre:nombres[1],
                    medico_ap:apellidos[1],
                    paciente_id:paciente_id
                };
                socket.emit('MonitorSignosVitalesReading', InformacionSignosVitales); 
                $.ajax({
                    url: "https://192.168.0.108/sigh/Sections/SignosVitales/AjaxInformationPatient",
                    type: 'POST',
                    dataType: 'json',
                    data:InformacionSignosVitales,
                    success: function (data, textStatus, jqXHR) {
                        $.ajax({
                            url: "Actions.php",
                            type: 'POST',
                            dataType: 'json',
                            data:{xml:xml_file},
                            success: function (data, textStatus, jqXHR) {
                                window.top.close();
                            },error: function (error) {
                                console.log(error);
                            }
                        });
                    },error: function (error) {
                        console.log(error);
                    }
                });
            });
        });
    </script>
</html>
