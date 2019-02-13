<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/23/19
 * Time: 4:43 PM
 */

namespace Validation\masks;

class DB implements Mask {
    public $dbStructure;

    public function __construct ($dbStructure = null) {
        if ($dbStructure){
            $this->dbStructure = $dbStructure;
        }
    }

    public function mask ($validator) {
        foreach ($this->dbStructure as $name => $field){
            if ($this->getRequired($field)){
                $validator->$name->required();
            }
            if ($this->getNullable($field)){
                $validator->$name->nullable();
            }
            $type = $this->getType($field);
            $args = $this->getArgs($field);
            $validator->$name->$type(...$args);
        }
    }

    public function getRequired ($field) {
        return $field['required'];
    }

    public function getNullable ($field) {
        return $field['nullable'];
    }

    public function getType ($field) {
        return $field['type'];
    }

    public function getArgs ($field) {
        return $field['args'];
    }
}