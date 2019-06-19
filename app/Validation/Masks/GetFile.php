<?php
namespace App\Validation\Masks;

class GetFile extends \Validation\masks\OpenAPIParameters {
public $structure = 
[
    0 => [
        'name' => 'id',
        'in' => 'path',
        'description' => 'An id of the file to download',
        'required' => true,
        'schema' => [
            'type' => 'string',
            'format' => 'md5',
        ],
        'style' => 'simple',
    ]
]
;
}