<?php

namespace App\Utils;

use swoole_table;

class SwooleServerCache {

    private $table;

    public static $instance;

    public static function getInstance() {
        if (static::$instance != null) {
            return static::$instance;
        }

        static::$instance = new static();
        return static::$instance;
    }

    public function __construct() {
        // Create new table
        $this->table = new swoole_table(32);

        // Create columns to store server info
        $this->table->column('id', swoole_table::TYPE_INT);
        $this->table->column('priority', swoole_table::TYPE_INT);
        $this->table->column('url', swoole_table::TYPE_STRING, 256);
        $this->table->create();
    }

    public function getServersInfo() {
        return $this->table;
    }

    public function getPrioritized() {
        // Check if table not empty
        if ($this->table->exist(0)) {
            foreach($this->table as $row) {
                if ($row['priority'] <= 0) {
                    return $row;
                }
            }
            return false;
        } else {
            return false;
        }
    }

    public function __destruct() {
        $this->table->destroy();
        $this->table = null;
    }

}