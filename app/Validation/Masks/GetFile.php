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
    ],
    1 => [
        'name' => 'user_token',
        'in' => 'header',
        'description' => 'A token to identify the user. Can be a OAuth2.0 token or a proprietary one.',
        'required' => true,
        'schema' => [
            'type' => 'string',
            'format' => 'byte',
        ],
        'style' => 'simple',
    ],
]
;
}