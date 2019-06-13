<?php

namespace App\Models;

use App\Exceptions\FileModelException;

/**
 * Class File
 *
 * @package App\Models
 *
 * @property string file_id
 * @property string user_id
 * @property string real_name
 * @property string extension
 * @property int size
 * @property string url
 * @property string server_host
 * @property string uploaded_at
 * @property string touched_at
 */
class File {

    public $exists;
    public $properties;

    private $filesInfoTable = 'files_info';
    private $filesUsersTable = 'files_users';

    public function __construct($id = null) {
        if ($id) {
            $this->find($id);
        }
    }

    public function find($id) {
        $id = strtoupper($id);
        $result = db()->selectFirst(
            "SELECT * FROM files_users JOIN files_info fi on files_users.file_id = fi.file_id 
                    WHERE fi.file_id = '$id';");
        if (!$result) {
            return false;
        }
        $this->loadFromArray($result);
        return $this;
    }

    protected function loadFromArray($array) {
        $this->properties = $array;
        $this->exists = true;
    }

    public function isLoaded() {
        return $this->exists;
    }

    public function save() {
        if ($this->isLoaded()) {
            throw new FileModelException('File already saved in DB!');
        }

        db()->startTransaction();

        // Write files users
        $result = db()->insert(
            "INSERT INTO {$this->filesUsersTable}(file_id, user_id) VALUES ('{$this->file_id}', '{$this->user_id}');"
        );
        if (!$result) {
            db()->rollback();
            throw new FileModelException('Could not write to DB!');
        }

        // Write files info
        $filteredArray = array_filter(
            $this->properties,
            function($k) { return $k !== 'user_id'; },
            ARRAY_FILTER_USE_KEY
        );

        $result = db()->insert(
            'INSERT INTO '
            .$this->filesInfoTable
            .'('
            .implode(', ', array_keys($filteredArray))
            .') VALUES ('
            .rtrim(str_repeat('?,', count($filteredArray)), ',')
            .');'
        , array_values($filteredArray));
        if (!$result) {
            db()->rollback();
            throw new FileModelException('Could not write to DB!');
        }

        db()->commit();
        $this->exists = true;
        return $this;
    }

    public function generateId() {
        $id = md5($this->real_name.$this->extension.$this->size.time());
        $this->file_id = $id;
        return $id;
    }

    public function __get($name) {
        return $this->properties[$name] ?? null;
    }

    public function __set($name, $value) {
        $this->properties[$name] = $value;
    }

}