<?php

/**Move file from temporary place to storage
 * @param $upload_path
 * @return bool Move status
 */
function upload($tmp_path,$upload_path,$storage = '/storage'){
    return move_uploaded_file($tmp_path, $upload_path);
}

function detectUploadPath(){

}

/**
 * Generate file id
 * @return string File id
 */
function generateId($path){
    $file_id = md5_file($path);
    return $file_id;
}

/**
 * Check is it a real image;
 * @param string $path Path to image
 */
function checkImage($path){
    $check = getimagesize($path);
    if($check === false) {
        return false;
    }
    return filesize($path);
}

function checkMime($path){
    $type = mime_content_type($path);
    if (!in_array($type,ALLOWED_FILE_MIME_TYPES))
        return false;
    return $type;
}

function readFileWithSpeed($path,$filename, $speed = false){
    $filesize = filesize($path);
    $from = 0;
    $to = $filesize;
    ob_start();
    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = substr($_SERVER['HTTP_RANGE'], strpos($_SERVER['HTTP_RANGE'], '=')+1);
        $from = (integer)(strtok($range, "-"));
        $to = (integer)(strtok("-"));
        header('HTTP/1.1 206 Partial Content');
        header('Content-Range: bytes '.$from.'-'.($to-1).'/'.$filesize);
    } else {
        header('HTTP/1.1 200 Ok');
    }
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . ($filesize-$from));
    header('Content-Type: application/octet-stream');
    header('Last-Modified: ' . gmdate('r', filemtime($path)));
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    $file = fopen($path, 'rb');
    fseek($file, $from);
    $size = $to - $from;
    $downloaded = 0;
    while(!feof($file) and ($downloaded<$size)) {
        echo fread($file, !$speed?CHUNK_SIZE:$speed);
        flush();
        ob_flush();
        if ($speed)
            sleep(1);
        $downloaded += !$speed?CHUNK_SIZE:$speed;
    }
    fclose($file);
}


/**
 * Render and output system file with basic headers
 *
 * @param string $path Path to requested file
 * @param string $name File output name
 */
function renderSysFile($path,$name){
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$name.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($path));
    ob_clean();
    readfile($path);
}


function makeThumbnail($path,$file_type){
    try {
        switch ($file_type) {
            case 'application/pdf':
                $path = $path . "[0]";
                break;
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            case 'application/msword':
                return true;
            default:
                break;
        }
        $image = new Imagick($path);
        $image->setImageFormat('png');
        $image->thumbnailImage(FILES_PREVIEW_SIZE, 0);
        $path = @end(explode('/', $path));
        $result = $image->writeImage(THUMBNAIL_PATH.$path.".png");
        return $result;
    } catch(Exception $e){
        echo $e->getMessage();
    }
}


function renderThumbnail($path,$name){
    $path = @end(explode('/',$path));
    $thumb_path = THUMBNAIL_PATH.$path.".png";
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$name.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($thumb_path));
    ob_clean();
    //flush();
    readfile($thumb_path);
}


/**Check file permissions according to roles
 *
 * @param string $action Requested file action ('r' - read/'c' - create/'d' - delete)
 * @return mixed Check status
 */
function checkFileRoles($action,$file_id,$user)
{
    $conn = DBConnection::getInstance();
    $query = "CALL checkFilePermissions('$file_id','$user');";
    $permissions = $conn->performQueryFetchAll($query);
    if (!$permissions)
        return false;
    return checkActionRoles($action, $permissions);
}


/**Check request action congruence to file permissions
 *
 * @param string $action Requested file action ('r' - read/'c' - create/'d' - delete)
 * @param int $rights File permissions
 * @return mixed Check status
 */
function checkActionRoles($action,$permissions){
    switch ($action){
        case 'd': $bit = 0; break;//delete
        case 'r': $bit = 1; break;//read
        case 'c': $bit = 2; break;//create
        default: return 0;
    }
    return ($permissions >> $bit) & 1;
}


/**
 * Create file record in base
 * @return mixed create status
 */
function createFile($file_id,$name,$owner_user_id,$path){
    $conn = DBConnection::getInstance();
    $query = "CALL createControllerFile('$file_id','$name','$owner_user_id','$path');";
    return $conn->performQuery($query);
}


/**
 * Delete file record from base
 * @return mixed Delete status
 */
function deleteFile($file_id){
    $conn = DBConnection::getInstance();
    $query = "CALL deleteControllerFile('$file_id');";
    return $conn->performQuery($query);
}

/**
 * Delete file permissions records from base
 * @return mixed Delete status
 */
function deleteFilePermissions($file_id){
    $conn = DBConnection::getInstance();
    $query = "CALL deleteFilePermissions('$file_id');";
    return $conn->performQuery($query);
}


/**
 * Create file roles in base
 * @return mixed Create status
 */
function createFilePermissions($file_id,$user,$permissions){
        $conn = DBConnection::getInstance();
        $query = "CALL createFilePermissions('$file_id','$user','$permissions');";
        return $conn->performQuery($query);
}


function generateLink($file_id){
    return md5($file_id.time());
}

function getFileData($file_id){
    $conn = DBConnection::getInstance();
    $query = "CALL getFileData('$file_id');";
    return  $conn->performQueryFetch($query);
}

function checkIntegrity($file_id,$path){
    return ($file_id == md5_file($path));
}

function generateFileName($id,$create_at,$name){
    $ext = @end(explode('.',$name));
    return md5($id.$create_at.$name).".$ext";
}
function DefineServer(){
    $server = Config::get('service_url');
    $result = sendRequest("$server/chooseFileServer",'GET',null,null);
    if ($result['status'] == 'error'){
        echo "File servers error";
        exit;
    }
    return $result['server'];
}

function checkPermissions($user_id,$file_data,$role = false){
    if ($role and $role == "admin"){
        return true;
    }
    return $user_id == $file_data['owner_id'];
}