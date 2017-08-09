<?php
function sendRequest($URL,$method,$header,$content){
    $options = ['http' => ['method' => $method, 'header' => $header, 'content' => $content]];
    $context = stream_context_create($options);
    return json_decode(file_get_contents("http://$URL", false, $context),true);
}

function sendFile($URL,$file_path){
    $mime = mime_content_type($file_path);
    $server = "http://$URL";
    $curl = curl_init($server);
    curl_setopt($curl, CURLOPT_POST, true);
    $data = ['userfile' => curl_file_create($file_path,$mime,basename($file_path))];
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    return json_decode(curl_exec($curl),true);
}