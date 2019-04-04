<?php
namespace App\Controllers;

use App\Models\File;
use App\Utils\FileServerRequest;
use App\Utils\URLGenerator;
use DateInterval;
use DateTime;
use Router\Routable;
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
    public function getFile ($id) {
        // TODO: Auth middleware
        $validator = new Validator(['id' => $id],"GetFile");
        $result = $validator->get();
        $errors = $validator->errors();

        $file = new File();
        $file->find($id);

        // If no file is found, emmit 404
        if (!$file->exists) {
            app()->response->status(404);
            return ['error' => 'File not found!'];
        }

        // Check file URL presence in db. If URL present - respond. If not - generate URL and push it
        if ($file->url) {
            return ['url' => $file->url];
        }

        $urlGenerator = new URLGenerator('file', $file->file_id, 'http://'.$file->server_host.'/');
        $url = $urlGenerator->generate();
        $expire = (new DateTime('now'))->add(DateInterval::createFromDateString('5 minutes'));

        // Send hash to file server
        $payload = [
            'hash' => $urlGenerator->hash,
            'temp' => true,
            'expire' => $expire->format(DateTime::RFC3339),
            'type' => 'read'
        ];
        $request = new FileServerRequest($file->server_host, $payload);
        $request->post('meta');

        return ['url' => $url];
    }

    /**
     * @summary Deletes a file info from controller.
     * @description Deletes file meta info from a controller service if something goes wrong during upload.
     */
    public function deleteFile ($id) {
        $validator = new Validator(['id' => $id],"DeleteFile");
        $result = $validator->get();
        $errors = $validator->errors();

    }

    /**
     * @summary Requests file upload.
     * @description Sends request to write a file to a file server.
     */
    public function uploadFile () {
        $validator = new Validator($this->request->getArgs(),"UploadFile");
        $result = $validator->get();
        $errors = $validator->errors();

    }
}