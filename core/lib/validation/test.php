<?php
include "Field.php";
include "Validator.php";
include "types/ValidationType.php";
include "types/Md5.php";
include "types/AlphaNumeric.php";
include "types/ValueFromList.php";
include "types/Boolean.php";
include "types/Date3339.php";
include "masks/Mask.php";
include "masks/DefaultMask.php";
include "masks/OpenAPI.php";
include "masks/OpenAPIParameters.php";
include "masks/OpenAPIContent.php";
include "masks/SaveLink.php";

$arr = ['expire'=>"1990-12-31 23:59:60Z", "type" => "upload"];
$validator = new Validator($arr,"SaveLink");
var_dump($validator->get(), $validator->errors());
