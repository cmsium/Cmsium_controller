<?php


namespace Router;

class Example {

    use Routable;

    public function hello($user,$some){
        var_dump("test",$user,$some,$this->request);
    }

}