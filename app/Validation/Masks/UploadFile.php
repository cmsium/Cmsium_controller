<?php
namespace App\Validation\Masks;

class UploadFile extends \Validation\masks\OpenAPIContent {
public $structure = 
[
    'type' => 'object',
    'properties' => [
        'name' => [
            'type' => 'string',
        ],
        'size' => [
            'type' => 'integer',
            'format' => 'int64',
        ],
    ],
    'required' => [
        0 => 'name',
        1 => 'size',
    ],
    'example' => [
        'name' => 'some_file.txt',
        'size' => 25655,
    ],
]
;
}