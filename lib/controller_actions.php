<?php

function get($file_id,$return = false){
    if (!$return) {
        $validator = Validator::getInstance();
        $file_id = $validator->Check('Md5Type', $file_id, []);
        if ($file_id === false) {
            echo "Wrong file id";
            return;
        }
        $file_data = getFileData($file_id);
        if (!$file_data){
            echo "File not found";
            return;
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
            return;
        case 'link':
            $link = $filecheck['link'];
            break;
        case 'nolink':
           $link = generateLink($file_id);
           $linkcheck = sendRequest("$server/saveTempLink?path=$path&link=$link",'GET',null,null);
           if ($linkcheck['status'] == "error"){
               echo $linkcheck['message'];
               return;
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
    $path = ROOTDIR.'/testdummy.txt';
    $name = 'testdummy.txt';
    $owner_user_id = 'eeec1e618690fba21fd416df610da961';

    $server = DefineServer();
    $response = SendFile($server.'/generateFileId',$path);
    switch ($response['status']){
        case 'error':
            echo $response['message'];
            return;
        case 'ok':
            $file_id = $response['id'];
    }
    if ($data = getFileData($file_id)){
        echo "File already exists: ".get($file_id,$data);
        return;
    } else {
        $created_at = date('Y-m-d H:i:s');
        $file_name = generateFileName($file_id,$created_at,$name);
        $response = SendFile($server."/createFile?file_name=$file_name",$path);
        switch ($response['status']){
            case 'error':
                echo $response['message'];
                return;
            case 'ok':
                $file_path = $response['path'];
        }
        if (createFile($file_id,$name,$owner_user_id,$file_path)){
            echo $file_id;
        } else {
            $path = explode('//',$path)[1];
            sendRequest("$server/deleteFile?path=$path",'GET',null,null);
            echo "Create file error";
        }
    }

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

/**
 * Get requested file by temporary link
 *
 * @param string $link Temporary link
 */
/*
function link($link){
    $validator = Validator::getInstance();
    $link = $validator->Check('Md5Type',$link,[]);
    if (!$link)
        ErrorHandler::throwException(DATA_FORMAT_ERROR,"page");
    $file = new fileslib(['link'=>$link]);
    $file->getFileIdByLink();
    $file->checkFileRoles('r');
    $file->getFileFromBase();
    $file->deleteLink();
    $file->renderFile();
    unset($file);
}


/**
 *Create new file using file create page
 */
/*





/**
 * Delete file from system by id
 *
 * @param string $file_id File id
 */
/*
function delete($file_id){
    if (!isset($file_id))
        return true;
        //ErrorHandler::throwException(FILE_PARAM_ABSENT, "page");
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if (!$file_id)
        ErrorHandler::throwException(DATA_FORMAT_ERROR);
    $file = new fileslib(['id'=>$file_id]);
    $file->checkFileRoles('d');
    $file_name = $file->getFileFromBase();
    $conn = DBConnection::getInstance();
    $conn->startTransaction();
    if (!$file->deleteLink()) {
        $conn->rollback();
        ErrorHandler::throwException(FILE_DELTE_BASE_ERROR,'page');
    }
    if (!$file->deleteFile()) {
        $conn->rollback();
        ErrorHandler::throwException(FILE_DELTE_BASE_ERROR,'page');
    }
    if (!$file->deleteFileRoles()) {
        $conn->rollback();
        ErrorHandler::throwException(FILE_DELTE_BASE_ERROR,'page');
    }
    if (!unlink(STORAGE.$file_name.'.zip')){
        $conn->rollback();
        ErrorHandler::throwException(FILE_DELTE_ERROR,'page');
    }
    @unlink(ROOTDIR . "/images/previews/".$file_name.'.png');
    $conn->commit();
    unset($file);
    unset($conn);
    return true;
    //ErrorHandler::throwException(FILE_DELTE_SUCCESS,'page');
}



/**
 * Update current file
 *
 * @param string $old_file_id Updated file id
 * @param string $new_file_column_name New file column name from FILES
 * @return null|string Update status (new file id if success)
 */
/*
function update($old_file_id, $new_file_column_name){
    if (!empty($old_file_id))
        $this->delete($old_file_id);
    return $this->create($new_file_column_name);
}
*/

