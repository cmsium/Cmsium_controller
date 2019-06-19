<?php

namespace App\Controllers;

use App\Models\File;
use Router\Routable;
use Validation\exceptions\DataFormatException;
use Validation\Validator;

class ServiceFileController {
    use Routable;

    /**
     * @summary Deletes a file info from controller.
     * @description Deletes file meta info from a controller service if something goes wrong during upload.
     */
    public function deleteFile ($id) {
        $validator = new Validator(['id' => $id],"DeleteFile");
        $result = $validator->get();
        $errors = $validator->errors();

        if ($errors) {
            throw new DataFormatException;
        }

        $file = new File;
        $file->find($id);

        // If no file is found, emmit 404
        if (!$file->exists) {
            app()->response->status(404);
            return ['error' => 'File not found!'];
        }

        // Delete file meta from DB
        $file->destroy();

        return 'OK';
    }
}