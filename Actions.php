<?php 
unlink('xml/'.$_POST['xml']);
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
    $response = array(
        'action' => 2
    );

echo json_encode($response); 
?>
