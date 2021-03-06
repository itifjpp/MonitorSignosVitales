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
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" type="text/javascript"></script>
    <script src="https://localhost:5001/socket.io/socket.io.js" sighuser="9999" type="text/javascript"></script>
    <script src="xml2json.js" type="text/javascript"></script>
    <script>
        var xml_file ='<?php echo $_GET['xml'];?>';
        var socket=io.connect('https://localhost:5001/');
        var sv_sis='';
        var sv_dis='';
        var sv_temp='';
        var sv_fc='';
        var sv_fr='';
        var sv_oximetria='';
        var sv_glicemia='';
        var sv_glasgow='';
        var ingreso_id=0;
        var ns=0;
        var serial_numers=[];
        var nombres=[];
        var apellidos=[];
        var medico_id='';
        var sv_dia='';
        $(document).ready(function () {
            $.get("http://localhost:8085/MonitorSignosVitales/xml/"+xml_file, {}, function (xml){
                $('MEMBER',xml).each(function(i,e){
                    if($(this).attr('name')=='SerialNumber'){
                        serial_numers.push($(this).find('VALUE').text());
                    }if($(this).attr('name')=='Systolic'){
                        //sv_sis=$(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text().trim().substr(0,3);
                        sv_sis=Number($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text());
                        console.log("Sys:"+$(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text())
                    }if($(this).attr('name')=='Diastolic'){
                        //sv_dia=$(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text().trim().substr(0,2);
                        sv_dia=Number($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text());
                    }if($(this).attr('name')=='FirstName' ){
                        nombres.push($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text())
                    }
                    if($(this).attr('name')=='LastName' ){
                        apellidos.push($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text());
                    }if($(this).attr('name')=='Identifier'){
                        if($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text()!=''){
                            ingreso_id=$(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text().replace(/\s/g,"");
                        }
                    }if($(this).attr('name')=='Sat'){
                        if($(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text()!=''){
                            sv_oximetria=$(this).find('VALUE DEFINITION MEMBERS MEMBER VALUE:first').text().replace(/\s/g,"");
                            let sv_oxi=sv_oximetria.slice(0,sv_oximetria.length-2);
                            sv_oximetria=sv_oxi;
                        }
                    }if($(this).attr('name')=='Respiration'){
                        if($(this).find('VALUE').text()!=''){
                            sv_fr=$(this).find('VALUE').text();
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
                    }if($(this).attr('type')=='widechar' && $(this).attr('name')=='Value' && $(this).text()!=''){
                        widecharText=$(this).text();
                        var x2js = new X2JS();
                        var jsonObj = x2js.xml_str2json( widecharText );
                        $.each(jsonObj,function(i,e) {
                            if($(this).attr('_Name')=='ESCALA DE COMA DE GLASGOW'){
                                sv_glasgow=$(this).attr('Value');
                            }
                            if($(this).attr('_Name')=='GLUCEMIA CAPILAR'){
                                sv_glicemia=$(this).attr('Value');
                            }
                            if($(this).attr('_Name')=='FRECUENCIA RESPIRATORIA'){
                                //sv_fr=$(this).attr('Value');
                            }
                        });
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
                    sv_fr:sv_fr,
                    sv_temp:(sv_temp!='' ? sv_temp.toFixed(1) :''),
                    sv_oximetria:sv_oximetria.substr(0,4),
                    sv_glicemia:sv_glicemia,
                    sv_glasgow:sv_glasgow,
                    equipo_ns:serial_numers[0],
                    xml_file:xml_file,
                    paciente_nombre:nombres[0],
                    paciente_ap:apellidos[0],
                    medico_id:medico_id.replace(/\s/g,""),
                    medico_nombre:nombres[1],
                    medico_ap:apellidos[1],
                    ingreso_id:ingreso_id
                };
                console.log(InformacionSignosVitales)
                socket.emit('MonitorSignosVitalesListening', InformacionSignosVitales); 
                $.ajax({
                    url: "https://192.168.0.111/Sections/SignosVitales/AjaxInformationPatient",
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
