<?php


function getFileInfo($file_id){
    $user_id = checkAuth();
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type', $file_id, []);
    if ($file_id === false) {
        echo json_encode(["status" => "error", "message" =>"Wrong file id"]);
        exit;
    }
    $file_data = getFileData($file_id);
    if (!$file_data){
        echo json_encode(["status" => "error", "message" =>"File not found"]);
        exit;
    }
    if (!checkPermissions($user_id,$file_data)){
        echo json_encode(["status" => "error", "message" =>"Permission denied"]);
        exit;
    }
    echo json_encode($file_data);
}


function getFile($file_id){
    $user_id = checkAuth();
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type', $file_id, []);
    if ($file_id === false) {
        echo json_encode(["status" => "error", "message" =>"Wrong file id"]);
        exit;
    }
    $file_data = getFileData($file_id);
    if (!$file_data){
        echo json_encode(["status" => "error", "message" =>"File not found"]);
        exit;
    }
    if (!checkPermissions($user_id,$file_data)){
        echo json_encode(["status" => "error", "message" =>"Permission denied"]);
        exit;
    }
    $exp = explode ("//",$file_data['path']);
    $server = $exp[0];
    $path = $exp[1];
    echo json_encode(["status" => "ok", "server" =>$server, "path"=>$path]);
}


function get($file_id,$return = false){
    $user_id = checkAuth();
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type', $file_id, []);
    if ($file_id === false) {
        echo json_encode(["status" => "error", "message" =>"Wrong file id"]);
        exit;
    }
    if (!$return) {
        $file_data = getFileData($file_id);
        if (!$file_data){
            echo json_encode(["status" => "error", "message" =>"File not found"]);
            exit;
        }
    } else{
        $file_data = $return;
    }
    if (!checkPermissions($user_id,$file_data)){
        echo json_encode(["status" => "error", "message" =>"Permission denied"]);
        exit;
    }
    $exp = explode ("//",$file_data['path']);
    $server = $exp[0];
    $path = $exp[1];
    $filecheck = sendRequest("$server/checkFile?id=$file_id&path=$path",'GET',null,null);
    switch ($filecheck['status']){
        case 'error':
            echo json_encode(["status" => "error", "message" =>$filecheck['message']]);
            exit;
        case 'link':
            $link = $filecheck['link'];
            break;
        case 'nolink':
           $link = generateLink($file_id);
           $linkcheck = sendRequest("$server/saveTempLink?path=$path&link=$link",'GET',null,null);
           if ($linkcheck['status'] == "error"){
               echo json_encode(["status" => "error", "message" =>$linkcheck['message']]);
               exit;
           }
           break;
    }
    if ($return) {
        $link = "<a href=\"http://$server/getFile?link=".$link."&name=".$file_data['file_name']."\">скачать</a>";
        return $link;
    }
    else {
        $header = HeadersController::getInstance();
        $url = "http://$server/getFile?link=$link&name={$file_data['file_name']}";
        $header->respondLocation(['value'=>$url]);
        return;
    }
}

function getFromSandbox($file_id){
    $user_id = checkAuth();
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type', $file_id, []);
    if ($file_id === false) {
        echo json_encode(["status" => "error", "message" =>"Wrong file id"]);
        exit;
    }
    $file_data = getSandboxFileData($file_id);
    if (!$file_data){
        echo json_encode(["status" => "error", "message" =>"File not found"]);
        exit;
    }
    if (!checkPermissions($user_id,$file_data)){
        echo json_encode(["status" => "error", "message" =>"Permission denied"]);
        exit;
    }
    $server = Config::get('sandbox_url');
    $header = HeadersController::getInstance();
    $url = "http://$server/getFile?path={$file_data['path']}&name={$file_data['file_name']}";
    $header->respondLocation(['value'=>$url]);
    return;
}

function deleteFromSandbox($file_id){
    $user_id = checkAuth();
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type', $file_id, []);
    if ($file_id === false) {
        echo "Wrong file id";
        exit;
    }
    $file_data = getSandboxFileData($file_id);
    if (!$file_data){
        echo "File not found";
        exit;
    }

    if (!checkPermissions($user_id,$file_data)){
        echo json_encode(["status" => "error", "message" =>"Permission denied"]);
        exit;
    }

    if(!deleteSandboxFile($file_id)){
        echo "Delete file error";
        exit;
    }
    $server = Config::get('sandbox_url');
    $delete_status = sendRequest("$server/deleteFile?path={$file_data['path']}",'GET',null,null);
    switch ($delete_status['status']){
        case 'error':
            echo $delete_status['message'];
            exit;
    }
    echo "Delete success";
    return;
}

function registerSandboxFile($file_id,$path,$owner_user_id){
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
    $owner_user_id = $validator->Check('Md5Type',$owner_user_id,[]);
    if ($owner_user_id === false){
        echo json_encode(["status" => "error", "message" => "Wrong user id format"]);
        return;
    }
    if ($data = getSandboxFileData($file_id)){
        echo json_encode(["status" => "error", "message" => "File already in sandbox"]);
        exit;
    } else {
        $name = @end(explode('/',$path));
        if (createSandboxFile($file_id,$name,$owner_user_id,$path)){
            echo json_encode(["status" => "ok", "id" => $file_id]);
            return;
        } else {
            echo json_encode(["status" => "error", "message" => 'File Create error']);
            return;
        }
    }
}


function copySandboxFile($file_id){
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if ($file_id === false){
        echo json_encode(["status" => "error", "message" => "Wrong file id format"]);
        exit;
    }
    if (!$data = getSandboxFileData($file_id)) {
        echo json_encode(["status" => "error", "message" => "File does not exist"]);
        exit;
    }
    if (getFileData($file_id)){
        echo json_encode(["status" => "error", "message" =>"File is already exists"]);
        exit;
    }
    $server = DefineServer();
    $conn = DBConnection::getInstance();
    $conn->startTransaction();
    $conn->commit();
    $sandbox = Config::get('sandbox_url');
    $response = sendRequest("$sandbox/copyFile?server=$server&file={$data['path']}&id=$file_id",'GET',null,null);
    switch ($response['status']){
        case 'error':
            echo json_encode(["status" => "error", "message" => $response['message']]);
            exit;
        case 'ok':
            if (!createFile($file_id,$data['file_name'],$data['owner_id'],$response['file_path'])){
                $conn->rollback();
                echo json_encode(["status" => "error", "message" => 'Database error']);
                exit;
            }
            if (!deleteSandboxFile($file_id)){
                $conn->rollback();
                echo json_encode(["status" => "error", "message" => 'Database error']);
                exit;
            }
            echo json_encode(["status" => "ok", "id" => $file_id]);
            return;
        default:
            echo json_encode(["status" => "error", "message" => $response['message']]);
    }
}


function create($file_id,$path,$owner_user_id) {
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
    $owner_user_id = $validator->Check('Md5Type',$owner_user_id,[]);
    if ($owner_user_id === false){
        echo json_encode(["status" => "error", "message" => "Wrong user id format"]);
        return;
    }
    if ($data = getFileData($file_id)){
        $host_url = Config::get('host_url');
        echo json_encode(["status" => "error", "message" => "File already exists: <a href='http://$host_url/get?id=$file_id'>скачать</a>"]);
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
            default:
                echo json_encode(["status" => "error", "message" => $response['message']]);
        }
    }
}

function delete($file_id){
    $user_id = checkAuth();
    //$user_id = 'eeec1e618690fba21fd416df610da961';
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

    if (!checkPermissions($user_id,$file_data)){
        echo json_encode(["status" => "error", "message" =>"Permission denied"]);
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

function updateData($file_id,$new_path){
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if ($file_id === false){
        echo json_encode(["status" => "error", "message" => "Wrong file id format"]);
        exit;
    }
    $path = $validator->Check('Path',$new_path,[]);
    if ($path === false){
        echo json_encode(["status" => "error", "message" => "Wrong file path format"]);
        return;
    }
    if (!updateFileData($file_id,$new_path)){
        echo json_encode(["status" => "error", "message" => "Could not change file data"]);
        return;
    } else {
        echo json_encode(["status" => "ok"]);
        return;
    }
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
        echo json_encode(["status" => "error", "message" => 'No files']);
        exit;
    }
    echo json_encode(array_merge(["status" => "ok"],$data));
    return;
}

function getSandboxFiles($user_id){
    $validator = Validator::getInstance();
    $user_id = $validator->Check('Md5Type', $user_id, []);
    if ($user_id === false) {
        echo json_encode(["status" => "error", "message" => 'Wrong user_id format']);
        exit;
    }
    $conn = DBConnection::getInstance();
    $query = "SELECT * from sandbox_files WHERE owner_id = '$user_id';";
    $data = $conn->performQueryFetchAll($query);
    if (empty($data)){
        echo json_encode(["status" => "error", "message" => 'Sandbox is empty']);
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

