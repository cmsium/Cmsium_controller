<?php

function get($file_id){
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if ($file_id === false){
        echo "Wrong file id";
        return;
    }
    $file_data = getFileData($file_id);
    if (!$file_data){
        echo "File not found";
        return;
    }
    $path = $file_data['path'];
    $filecheck = sendRequest("files.my/checkFile?id=$file_id&path=$path",'GET',null,null);
    switch ($filecheck['status']){
        case 'error':
            echo $filecheck['message'];
            return;
        case 'link':
            $link = $filecheck['link'];
            break;
        case 'nolink':
           $link = generateLink($file_id);
           $linkcheck = sendRequest("files.my/saveTempLink?path=$path&link=$link",'GET',null,null);
           if ($linkcheck['status'] == "error"){
               echo $linkcheck['message'];
               return;
           }
           break;
    }
    //$host_url = Config::get('host_url');
    echo "<a href=\"http://files.my/getFile?link=".$link."\">скачать</a>";
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
function create($file_column_name = false) {
    if (!$file_column_name)
        $file_column_name = 'userfile';
    if (isset($_FILES[$file_column_name]) and !empty($_FILES[$file_column_name]['name'])) {
        $auth = AuthHandler::getInstance();
        $auth->check();
        $validator = Validator::getInstance();
        $file_data = $validator->ValidateAllByMask($_FILES[$file_column_name], 'fileUploadMask');
        if (!$file_data) {
            ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
        }
        $file = new fileslib(['name'=>$file_data['name'],'tmp_path'=>$_FILES[$file_column_name]['tmp_name']]);
        if (!$file->checkMime()) {
            ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
        }
        $size = filesize($_FILES[$file_column_name]['tmp_name']);
        if (($file_data["size"] > MAX_FILE_UPLOAD_SIZE) or ($size > MAX_FILE_UPLOAD_SIZE)) {
            ErrorHandler::throwException(FILE_SIZE_ERROR,'page');
        }
        $id = $file->generateId();
        $this->last_file_id = $id;
        $conn = DBConnection::getInstance();
        $conn->startTransaction();
        if (!$file->createFile()){
            $conn->rollback();
            ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
        }
        if (!$file->createFileRoles()){
            $conn->rollback();
            ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
        }
        $file_name = $file->getFileFromBase();
        if (!$file->makeThumbnail()){
            $conn->rollback();
            ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
        }
        if ($file->upload(STORAGE.$file_name)) {
            $file->addToZip();
            $conn->commit();
            return $id;
            //ErrorHandler::throwException(FILE_UPLOAD_SUCCESS,'page');
        } else {
            $conn->rollback();
            ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
        }
        unset($file);
        unset($conn);

    } else{
        return NULL;
    }
}




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

