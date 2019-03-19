<?php

namespace Plumber;

class Pipeline {

    public $pipes = [];

    public function addPipe(callable $callback) {
        $this->pipes[] = $callback;
        return $this;
    }

    public function setPipes(array $pipes) {
        $this->pipes = array_merge($this->pipes, $pipes);
    }

    public function run(...$arguments) {
        foreach ($this->pipes as $pipe) {
            $pipe(...$arguments);
        }
        return $this;
    }

}