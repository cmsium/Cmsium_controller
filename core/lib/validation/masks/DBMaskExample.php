<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/24/19
 * Time: 1:41 PM
 */

namespace Validation\masks;

class DBMaskExample extends DB {
    public $dbStructure = [
        'body' => ["type" => "varchar" , "args" => [255], "required" => true, "nullable" => true],
        'article_id' => ["type" => "integer" , "args" => [11], "required" => true, "nullable" => true],
        'user_id' => ["type" => "integer" , "args" => [11], "required" => true, "nullable" => true]
    ];
}