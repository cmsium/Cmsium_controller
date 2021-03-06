<?php
namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Models\File;
use App\Utils\FileServerRequest;
use Router\Routable;
use Validation\exceptions\DataFormatException;
use \Validation\Validator;

/**
 * @description Single file operations
 */
class FileController {
    use Routable;

    /**
     * @summary Returns a file server URL to read file from.
     * @description Responds a URL that user should be followed to get the requested file. Checks auth if needed.
     */
    public function getFile($id) {
        $validator = new Validator(['id' => $id],"GetFile");
        $errors = $validator->errors();
        if ($errors) {
            throw new ValidationException($errors);
        }

        $file = new File;
        $file->find($id);

        // If no file is found, emmit 404
        if (!$file->exists) {
            app()->response->status(404);
            return ['error' => 'File not found!'];
        }

        // Check file URL presence in db. If URL present - respond. If not - generate URL and push it
        if (!$file->temp && $file->url) {
            return ['url' => $file->url];
        }

        // Generate unique signed read URL
        $file->generateURL('http://'.$file->server_host.'/');

        $file->sendMetaToFileServer('read');

        return ['url' => $file->url];
    }

    /**
     * @summary Initiates file deletion from servers.
     * @description Deletes file meta info from controller, and the file from file server.
     */
    public function deleteFile($id) {
        $validator = new Validator(['id' => $id],"DeleteFile");
        $result = $validator->get();
        $errors = $validator->errors();

        if ($errors) {
            throw new ValidationException($errors);
        }

        $file = new File;
        $file->find($id);

        // If no file is found, emmit 404
        if (!$file->exists) {
            app()->response->status(404);
            return ['error' => 'File not found!'];
        }

        // Check file URL presence in db. If URL present - push it. If not - generate URL and push it
        if (!$file->url) {
            // Generate unique signed delete URL
            $file->generateURL('http://'.$file->server_host.'/');
        }

        // Send delete file request to file server
        $file->sendMetaToFileServer('delete');

        // Delete file meta from DB
        $file->destroy();

        return 'OK';
    }

    /**
     * @summary Requests file upload.
     * @description Sends request to write a file to a file server.
     */
    public function uploadFile() {
        $validator = new Validator($this->request->getArgs(),"UploadFile");
        $errors = $validator->errors();
        if ($errors) {
            throw new ValidationException($errors);
        }

        // Check if server upload info is present in swoole table
        $serverInfo = app()->serversCache->getPrioritized() ?: FileServerRequest::requestServersStatus();

        // Generate File
        $file = new File;
        $bakedData = $this->request->getArgs();
        $temp = $bakedData['temp'] === 'true' ? 1 : 0;
        $file->setFileRealProps($bakedData['name']);
        $file->massAssign([
            'size'        => (int)$bakedData['size'],
            'server_host' => $serverInfo['url'],
            'user_id'     => app()->request->header['x-user-token'],
            'temp'        => $temp
        ]);

        // Generate unique signed upload URL
        $file->generateURL($serverInfo['url']);

        // Save file info in DB
        $file->save();

        // Push unique hash for upload + file_id + temp flagged
        $file->sendMetaToFileServer('upload', $temp);

        return ['url' => $file->url];
    }
}