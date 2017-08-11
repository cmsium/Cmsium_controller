<?php

function get($file_id,$return = false){
    if (!$return) {
        $validator = Validator::getInstance();
        $file_id = $validator->Check('Md5Type', $file_id, []);
        if ($file_id === false) {
            echo "Wrong file id";
            exit;
        }
        $file_data = getFileData($file_id);
        if (!$file_data){
            echo "File not found";
            exit;
        }
    } else{
        $file_data = $return;
    }
    $exp = explode ("//",$file_data['path']);
    $server = $exp[0];
    $path = $exp[1];
    $filecheck = sendRequest("$server/checkFile?id=$file_id&path=$path",'GET',null,null);
    switch ($filecheck['status']){
        case 'error':
            echo $filecheck['message'];
            exit;
        case 'link':
            $link = $filecheck['link'];
            break;
        case 'nolink':
           $link = generateLink($file_id);
           $linkcheck = sendRequest("$server/saveTempLink?path=$path&link=$link",'GET',null,null);
           if ($linkcheck['status'] == "error"){
               echo $linkcheck['message'];
               exit;
           }
           break;
    }
    $link = "<a href=\"http://$server/getFile?link=".$link."\">скачать</a>";
    if ($return)
        return $link;
    else {
        echo $link;
        return;
    }
}

function create() {
    //TODO auth token check
    $owner_user_id = 'eeec1e618690fba21fd416df610da961';

    if (!empty($_FILES)) {
        $validator = Validator::getInstance();
        $file_data = $validator->ValidateAllByMask($_FILES['userfile'], 'fileUploadMask');
        if ($file_data === false) {
            var_dump($validator->getErrors());
            echo "Wrong file format";
            exit;
        }
        if (!checkMime($_FILES['userfile']['tmp_name'])) {
            echo "Wrong file type";
            exit;
        }
        $size = filesize($_FILES['userfile']['tmp_name']);
        if (($file_data["size"] > MAX_FILE_UPLOAD_SIZE) or ($size > MAX_FILE_UPLOAD_SIZE)) {
            echo "File is too large";
            exit;
        }
    } else {
        echo "No file sent";
        exit;
    }
    $path = $_FILES['userfile']['tmp_name'];
    $name = $_FILES['userfile']['name'];
    $server = DefineServer();
    $response = SendFile($server.'/generateFileId',$path,$name);
    switch ($response['status']){
        case 'error':
            echo $response['message'];
            print_r($response);
            exit;
        case 'ok':
            $file_id = $response['id'];
    }
    if ($data = getFileData($file_id)){
        echo "File already exists: ".get($file_id,$data);
        exit;
    } else {
        $created_at = date('Y-m-d H:i:s');
        $file_name = generateFileName($file_id,$created_at,$name);
        $response = SendFile($server."/createFile?file_name=$file_name",$path,$name);
        switch ($response['status']){
            case 'error':
                echo $response['message'];
                exit;
            case 'ok':
                $file_path = $response['path'];
        }
        if (createFile($file_id,$name,$owner_user_id,$file_path)){
            echo $file_id;
            return;
        } else {
            $path = explode('//',$path)[1];
            sendRequest("$server/deleteFile?path=$path",'GET',null,null);
            echo "Create file error";
        }
    }
}

function delete($file_id){
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type', $file_id, []);
    if ($file_id === false) {
        echo "Wrong file id";
        exit;
    }
    $file_data = getFileData($file_id);
    if (!$file_data){
        echo "File not found";
        exit;
    }
    $exp = explode ("//",$file_data['path']);
    $server = $exp[0];
    $path = $exp[1];
    $delete_status = sendRequest("$server/deleteFile?path=$path",'GET',null,null);
    switch ($delete_status['status']){
        case 'ok':
            if(!deleteFile($file_id)){
                echo "Delete file error";
                exit;
            }
            echo "Delete success";
            return;
        case 'error':
            echo $delete_status['message'];
            exit;
    }
}

function update($file_id){
    delete($file_id);
    create();
}

function testFileForm(){
    $server = Config::get('host_url');
    echo "
<html>
    <body>
        <form action='http://$server/create' method='post' enctype=\"multipart/form-data\">
            <input type='file' name='userfile'>
            <input type='submit'>
        </form>
    </body>
</html>
";
    return;
}

/*
function getPicture($file_id,$url,$description){
    if (!isset($file_id))
        return NULL;
    //ErrorHandler::throwException(FILE_PARAM_ABSENT, "page");
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if (!$file_id)
        ErrorHandler::throwException(DATA_FORMAT_ERROR,"page");
    $file = new fileslib(['id'=>$file_id]);
    $file->checkFileRoles('r');
    $file->getFileFromBase();
    $link = $file->getLink();
    $host_url = Config::get('host_url');
    unset($file);
    return "<div class='inline'><img src=\"".$host_url."/file/preview/".$link."\"/><br />
    <a href=\"".$host_url."$url\">$description</a></div>";
}

/**
 * Get requested file by temporary link
 *
 * @param string $link Temporary link
 */
/*
function preview($link){
    $validator = Validator::getInstance();
    $link = $validator->Check('Md5Type',$link,[]);
    if (!$link)
        ErrorHandler::throwException(DATA_FORMAT_ERROR,"page");
    $file = new fileslib(['link'=>$link]);
    $file->getFileIdByLink();
    $file->checkFileRoles('r');
    $file->getFileFromBase();
    $file->renderThumbnail();
    unset($file);
}

*/

