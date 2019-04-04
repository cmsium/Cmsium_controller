<?php

namespace App\Utils;

/**
 * Class URLGenerator
 */
class URLGenerator {

    public $path;
    public $host;
    public $hash;
    /**
     * @var string Contains the string that is going to be hashed with salt
     */
    public $seed;
    /**
     * @var string Hashed app key with time
     */
    protected $salt;

    public function __construct($path, $seed, $host = null) {
        $this->path = $path;
        $this->seed = $seed;
        $this->host = $host ?: config('app_url');
    }

    public function generate() {
        $this->generateSalt();
        $host = trim($this->host, '/').'/';
        $path = empty($this->path) ? '' : trim($this->path, '/').'/';
        $hash = base64_encode($this->seed.$this->salt);
        $this->hash = $hash;
        $result = $host.$path.$hash;
        return $result;
    }

    private function generateSalt() {
        $this->salt = md5(config('app_key').time());
        return $this;
    }

}