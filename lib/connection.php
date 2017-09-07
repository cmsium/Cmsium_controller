<?php
function sendRequest($URL,$method,$header,$content){
    $options = ['http' => ['method' => $method, 'header' => $header, 'content' => $content]];
    $context = stream_context_create($options);
    return json_decode(file_get_contents("http://$URL", false, $context),true);
}

function sendFile($URL,$file_path,$file_name){
    $mime = mime_content_type($file_path);
    $server = "http://$URL";
    $curl = curl_init($server);
    curl_setopt($curl, CURLOPT_POST, true);
    $data = ['userfile' => curl_file_create($file_path,$mime,$file_name)];
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    return json_decode(curl_exec($curl),true);
}

function checkAuth(){
    if (!isset($_COOKIE['token'])) {
        $header = HeadersController::getInstance();
        $auth = Config::get('auth_url');
        $host = Config::get('host_url');
        $back = urlencode("http://$host".$_SERVER['REQUEST_URI']);
        $url = "http://$auth?redirect_uri=$back";
        $header->respondLocation(['value'=>$url]);
        exit;
    } else {
        $auth = Config::get('auth_url');
        $authcheck = sendRequest("$auth/token/check",'POST','Content-type: application/x-www-form-urlencoded',http_build_query(['token'=>$_COOKIE['token']]));
        switch ($authcheck['is_valid']){
            case true: return $authcheck['user_id'];break;
            case false:
                $header = HeadersController::getInstance();
                $host = Config::get('host_url');
                $back = urlencode("http://$host".$_SERVER['REQUEST_URI']);
                $url = "http://$auth?redirect_uri=$back";
                $header->respondLocation(['value'=>$url]);
                exit;
        }
    }
}