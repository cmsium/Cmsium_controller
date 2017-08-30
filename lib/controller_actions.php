<?php

function get($file_id,$return = false){
    //TODO auth token check
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type', $file_id, []);
    if ($file_id === false) {
        echo "Wrong file id";
        exit;
    }
    if (!$return) {
        $file_data = getFileData($file_id);
        if (!$file_data){
            echo "File not found";
            exit;
        }
    } else{
        $file_data = $return;
    }

    $user_id = 'eeec1e618690fba21fd416df610da961';
    if (!checkPermissions($user_id,$file_data)){
        echo "Permission denied";
        exit;
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
    $link = "<a href=\"http://$server/getFile?link=".$link."&name=".$file_data['file_name']."\">скачать</a>";
    if ($return)
        return $link;
    else {
        echo $link;
        return;
    }
}

function create($file_id,$path) {
    //TODO auth token check
    $owner_user_id = 'eeec1e618690fba21fd416df610da961';

    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if ($file_id === false){
        echo json_encode(["status" => "error", "message" => "Wrong file id format"]);
        exit;
    }
    $path = $validator->Check('Path',$path,[]);
    if ($path === false){
        echo json_encode(["status" => "error", "message" => "Wrong file path format"]);
        return;
    }
    if ($data = getFileData($file_id)){
        echo json_encode(["status" => "error", "message" => "File already exists: ".get($file_id,$data)]);
        exit;
    } else {
        $server = DefineServer();
        $sandbox = Config::get('sandbox_url');
        $response = sendRequest("$sandbox/copyFile?server=$server&file=$path&id=$file_id",'GET',null,null);
        switch ($response['status']){
            case 'error':
                echo json_encode(["status" => "error", "message" => $response['message']]);
                exit;
            case 'ok':
                $name = @end(explode('/',$path));
                if (createFile($file_id,$name,$owner_user_id, $response['file_path'])){
                    echo json_encode(["status" => "ok", "id" => $file_id]);
                    return;
                } else {
                    $path = explode('//',$response['file_path'])[1];
                    sendRequest("$server/deleteFile?path=$path",'GET',null,null);
                    echo json_encode(["status" => "error", "message" => 'File Create error']);
                    return;
                }
        }
    }
}

function delete($file_id){
    //TODO auth token check
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

    $user_id = 'eeec1e618690fba21fd416df610da961';
    if (!checkPermissions($user_id,$file_data)){
        echo "Permission denied";
        exit;
    }

    if(!deleteFile($file_id)){
        echo "Delete file error";
        exit;
    }
    $exp = explode ("//",$file_data['path']);
    $server = $exp[0];
    $path = $exp[1];
    $delete_status = sendRequest("$server/deleteFile?path=$path",'GET',null,null);
    switch ($delete_status['status']){
        case 'error':
            echo $delete_status['message'];
            exit;
    }
    echo "Delete success";
    return;

}

function update($file_id){
    delete($file_id);
    //create($file_id);
}

function getAllFiles($columns){
    $validator = Validator::getInstance();
    $columns = $validator->Check('DotValues', $columns, []);
    if ($columns === false) {
        echo json_encode(["status" => "error", "message" => 'Wrong columns format']);
        exit;
    }
    $conn = DBConnection::getInstance();
    $columns = implode(',',explode('.',$columns));
    $query = "SELECT $columns from controller_files;";
    $data = $conn->performQueryFetchAll($query);
    if (!$data){
        echo json_encode(["status" => "error", "message" => 'Sql error']);
        exit;
    }
    echo json_encode(array_merge(["status" => "ok"],$data));
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

