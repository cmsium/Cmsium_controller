<?php

namespace App\Models;

class File {

    public $exists;
    public $properties;

    public function __construct($id = null) {
        if ($id) {
            $this->find($id);
        }
    }

    public function find($id) {
        $id = strtoupper($id);
        $result = db()->selectFirst("SELECT * FROM files_users JOIN files_info fi on files_users.file_id = fi.file_id 
                                       WHERE fi.file_id = '$id';");
        if (!$result) {
            return false;
        }
        $this->loadFromArray($result);
        return $this;
    }
    public function isLoaded() {
        return $this->exists;
    }

    protected function loadFromArray($array) {
        $this->properties = $array;
        $this->exists = true;
    }

    public function __get($name) {
        return $this->properties[$name] ?? null;
    }

}