<?php


function getFileInfo($file_id){
    $user_id = checkAuth();
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type', $file_id, []);
    if ($file_id === false) {
        throwException(DATA_FORMAT_ERROR);
    }
    $file_data = getFileData($file_id);
    if (!$file_data){
        throwException(FILE_NOT_FOUND);
    }
    if (!checkPermissions($user_id,$file_data)){
        throwException(PERMISSION_DENIED);
    }
    echo json_encode($file_data);
}


function getFile($file_id){
    $user_id = checkAuth();
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type', $file_id, []);
    if ($file_id === false) {
        throwException(DATA_FORMAT_ERROR);
    }
    $file_data = getFileData($file_id);
    if (!$file_data){
        throwException(FILE_NOT_FOUND);
    }
    if (!checkPermissions($user_id,$file_data)){
        throwException(PERMISSION_DENIED);
    }
    $exp = explode ("//",$file_data['path']);
    $server = $exp[0];
    $path = $exp[1];
    echo json_encode([$server, $path]);
}


function get($file_id,$return = false){
    $user_id = checkAuth();
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type', $file_id, []);
    if ($file_id === false) {
        throwException(DATA_FORMAT_ERROR);
    }
    if (!$return) {
        $file_data = getFileData($file_id);
        if (!$file_data){
            throwException(FILE_NOT_FOUND);
        }
    } else{
        $file_data = $return;
    }
    if (!checkPermissions($user_id,$file_data)){
        throwException(PERMISSION_DENIED);
    }
    $exp = explode ("//",$file_data['path']);
    $server = $exp[0];
    $path = $exp[1];
    $filecheck = sendRequest("$server/checkFile?id=$file_id&path=$path",'GET',null,null);
    if (empty($filecheck)) {
        $link = generateLink($file_id);
        sendRequest("$server/saveTempLink?path=$path&link=$link", 'GET', null, null);
    } else {
        $link = $filecheck;
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
        throwException(DATA_FORMAT_ERROR);
    }
    $file_data = getSandboxFileData($file_id);
    if (!$file_data){
        throwException(FILE_NOT_FOUND);
    }
    if (!checkPermissions($user_id,$file_data)){
        throwException(PERMISSION_DENIED);
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
        throwException(DATA_FORMAT_ERROR);
    }
    $file_data = getSandboxFileData($file_id);
    if (!$file_data){
        throwException(FILE_NOT_FOUND);
    }

    if (!checkPermissions($user_id,$file_data)){
        throwException(PERMISSION_DENIED);
    }

    if(!deleteSandboxFile($file_id)){
        throwException(DELETE_FILE_ERROR);
    }
    $server = Config::get('sandbox_url');
    sendRequest("$server/deleteFile?path={$file_data['path']}",'GET',null,null);
    return;
}

function registerSandboxFile($file_id,$path,$owner_user_id){
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if ($file_id === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $path = $validator->Check('Path',$path,[]);
    if ($path === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $owner_user_id = $validator->Check('Md5Type',$owner_user_id,[]);
    if ($owner_user_id === false){
        throwException(DATA_FORMAT_ERROR);
    }
    if ($data = getSandboxFileData($file_id)){
        throwException(FILE_ALREADY_IN_SANDBOX);
    } else {
        $name = @end(explode('/',$path));
        if (createSandboxFile($file_id,$name,$owner_user_id,$path)){
            echo $file_id;
            return;
        } else {
            throwException(FILE_CREATE_SUCCESS);
        }
    }
}


function copySandboxFile($file_id){
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if ($file_id === false){
        throwException(DATA_FORMAT_ERROR);
    }
    if (!$data = getSandboxFileData($file_id)) {
        throwException(FILE_NOT_FOUND);
    }
    if (getFileData($file_id)){
        throwException(FILE_ALREADY_EXITS);
    }
    $server = DefineServer();
    $conn = DBConnection::getInstance();
    $conn->startTransaction();
    $conn->commit();
    $sandbox = Config::get('sandbox_url');
    $response = sendRequest("$sandbox/copyFile?server=$server&file={$data['path']}&id=$file_id",'GET',null,null);
    if (!createFile($file_id,$data['file_name'],$data['owner_id'],$response)){
        $conn->rollback();
        throwException(FILE_CREATE_ERROR);
    }
//    if (!deleteSandboxFile($file_id)){
//        $conn->rollback();
//        throwException(DELETE_FILE_ERROR);
//    }
    echo $file_id;
    return;
}


function create($file_id,$path,$owner_user_id) {
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if ($file_id === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $path = $validator->Check('Path',$path,[]);
    if ($path === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $owner_user_id = $validator->Check('Md5Type',$owner_user_id,[]);
    if ($owner_user_id === false){
        throwException(DATA_FORMAT_ERROR);
    }
    if ($data = getFileData($file_id)){
        throwException(FILE_ALREADY_EXITS);
    } else {
        $server = DefineServer();
        $sandbox = Config::get('sandbox_url');
        $response = sendRequest("$sandbox/copyFile?server=$server&file=$path&id=$file_id",'GET',null,null);
        $name = @end(explode('/',$path));
        if (createFile($file_id,$name,$owner_user_id, $response)){
            echo $file_id;
            return;
        } else {
            $path = explode('//',$response['file_path'])[1];
            sendRequest("$server/deleteFile?path=$path",'GET',null,null);
            throwException(FILE_CREATE_ERROR);
        }
    }
}

function delete($file_id){
    $user_id = checkAuth();
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type', $file_id, []);
    if ($file_id === false) {
        throwException(DATA_FORMAT_ERROR);
    }
    $file_data = getFileData($file_id);
    if (!$file_data){
        throwException(FILE_NOT_FOUND);
    }

    if (!checkPermissions($user_id,$file_data)){
        throwException(PERMISSION_DENIED);
    }

    if(!deleteFile($file_id)){
        throwException(DELETE_FILE_ERROR);
    }
    $exp = explode ("//",$file_data['path']);
    $server = $exp[0];
    $path = $exp[1];
    sendRequest("$server/deleteFile?path=$path",'GET',null,null);
    return;

}

function updateData($file_id,$new_path){
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if ($file_id === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $path = $validator->Check('Path',$new_path,[]);
    if ($path === false){
        throwException(DATA_FORMAT_ERROR);
    }
    if (!updateFileData($file_id,$new_path)){
        throwException(FILE_DATA_UPDATE_ERROR);
    }
}


function getAllFiles($columns){
    $validator = Validator::getInstance();
    $columns = $validator->Check('DotValues', $columns, []);
    if ($columns === false) {
        throwException(DATA_FORMAT_ERROR);
    }
    $conn = DBConnection::getInstance();
    $columns = implode(',',explode('.',$columns));
    $query = "CALL getAllFiles('$columns');";
    $data = $conn->performQueryFetchAll($query);
    if (!$data){
        throwException(NO_FILES);
    }
    //TODO no json
    echo json_encode($data);
    return;
}

function getSandboxFiles($user_id){
    $validator = Validator::getInstance();
    $user_id = $validator->Check('Md5Type', $user_id, []);
    if ($user_id === false) {
        throwException(DATA_FORMAT_ERROR);
    }
    $conn = DBConnection::getInstance();
    $query = "CALL getSandboxFiles('$user_id');";
    $data = $conn->performQueryFetchAll($query);
    if (empty($data)){
        throwException(EMPTY_SANDBOX);
    }
    //TODO no json
    echo json_encode($data);
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

