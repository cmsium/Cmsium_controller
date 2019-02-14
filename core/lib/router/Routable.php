<?php


namespace Router;

trait Routable{
    public $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
}