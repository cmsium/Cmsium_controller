<?php
namespace App\Validation\Masks;

class UploadFile extends \Validation\masks\OpenAPIContent {
public $structure = 
[
    'type' => 'object',
    'properties' => [
        'name' => [
            'type' => 'string',
            'format' => 'Varchar',
            'maxLength' => 255
        ],
        'size' => [
            'type' => 'integer',
            'format' => 'int64',
        ],
        'temp' => [
            'type' => 'boolean'
        ]
    ],
    'required' => [
        0 => 'name',
        1 => 'size',
        2 => 'temp'
    ],
    'example' => [
        'name' => 'some_file.txt',
        'size' => 25655,
    ],
]
;
}