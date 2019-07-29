<?php

namespace App\Models;

use App\Exceptions\FileModelException;
use App\Utils\FileServerRequest;
use App\Utils\URLGenerator;
use DateInterval;
use DateTime;

/**
 * Class File
 *
 * @package App\Models
 *
 * @property string file_id
 * @property string user_id
 * @property string real_name
 * @property string extension
 * @property int size
 * @property bool temp
 * @property string url
 * @property string server_host
 * @property string uploaded_at
 * @property string touched_at
 */
class File {

    public $exists;
    public $properties;

    private $filesInfoTable = 'files_info';
    private $filesUsersTable = 'files_users';
    private $urlGenerator = null;

    public function __construct($id = null) {
        if ($id) {
            $this->find($id);
        }
    }

    public function find($id) {
        $id = strtoupper($id);
        $result = db()->selectFirst(
            "SELECT * FROM files_users JOIN files_info fi on files_users.file_id = fi.file_id 
                    WHERE fi.file_id = '$id';");
        if (!$result) {
            return false;
        }
        $this->loadFromArray($result);
        return $this;
    }

    protected function loadFromArray($array) {
        $this->properties = $array;
        $this->exists = true;
    }

    public function isLoaded() {
        return $this->exists;
    }

    public function save() {
        if (!$this->file_id) {
            $this->generateId();
        }

        if ($this->isLoaded()) {
            throw new FileModelException('File already saved in DB!');
        }

        db()->startTransaction();

        // Write files users
        $result = db()->insert(
            "INSERT INTO {$this->filesUsersTable}(file_id, user_id) VALUES ('{$this->file_id}', '{$this->user_id}');"
        );
        if (!$result) {
            db()->rollback();
            throw new FileModelException('Could not write to DB!');
        }

        // Write files info
        $filteredArray = array_filter(
            $this->properties,
            function($k) { return $k !== 'user_id'; },
            ARRAY_FILTER_USE_KEY
        );

        $query = 'INSERT INTO '
            .$this->filesInfoTable
            .'('
            .implode(', ', array_keys($filteredArray))
            .') VALUES ('
            .rtrim(str_repeat('?,', count($filteredArray)), ',')
            .');';

        $result = db()->insert($query, array_values($filteredArray));
        if (!$result) {
            db()->rollback();
            throw new FileModelException('Could not write to DB!');
        }

        db()->commit();
        $this->exists = true;
        return $this;
    }

    public function destroy() {
        if (!$this->isLoaded()) {
            throw new FileModelException('File does not exist in DB!');
        }

        db()->startTransaction();

        // Delete from files info
        $result = db()->delete(
            "DELETE FROM {$this->filesInfoTable} WHERE file_id='{$this->file_id}';"
        );
        if (!$result) {
            db()->rollback();
            throw new FileModelException('Could not delete from DB!');
        }

        // Delete from files users
        $result = db()->delete(
            "DELETE FROM {$this->filesUsersTable} WHERE file_id='{$this->file_id}';"
        );
        if (!$result) {
            db()->rollback();
            throw new FileModelException('Could not delete from DB!');
        }

        db()->commit();
        $this->exists = false;
        return $this;
    }

    public function generateId() {
        $id = md5($this->real_name.$this->extension.$this->size.time());
        $this->file_id = $id;
        return $id;
    }

    public function generateURL($host) {
        if (!$this->file_id) {
            $this->generateId();
        }

        $urlGenerator = new URLGenerator('file', $this->file_id, $host);
        $this->urlGenerator = $urlGenerator;
        $url = $urlGenerator->generate();
        $this->url = $url;
        return $url;
    }

    public function sendMetaToFileServer($type, $temp = 1) {
        // Check if generator or hash exists
        if ($this->urlGenerator) {
            $hash = $this->urlGenerator->hash;
        } else {
            if (!$this->url) {
                throw new FileModelException('URLGenerator object not defined!');
            }

            $urlArray = explode('/', $this->url);
            $hash = end($urlArray);
        }

        $payload = [
            'hash'   => $hash,
            'file'   => $this->file_id,
            'temp'   => $temp,
            'type'   => $type
        ];

        // If persistent, no expire needed
        if (!$temp) {
            $expire = (new DateTime('now'))->add(DateInterval::createFromDateString(config('hash_expire')));
            $payload['expire'] = $expire->format(DateTime::RFC3339);
        }

        $request = new FileServerRequest($this->server_host, $payload);
        $request->async = true;
        $request->post('meta');
    }

    /**
     * Set file properties based on real file name
     *
     * @param $filename
     * @return File
     */
    public function setFileRealProps($filename) {
        $fileRealName = implode('.', explode('.', $filename, -1));
        $arrayToPop = explode('.', $filename);
        $fileExtension = array_pop($arrayToPop);
        $this->real_name = $fileRealName;
        $this->extension = $fileExtension;
        return $this;
    }

    public function massAssign(array $data) {
        $this->properties = $this->properties + $data;
    }

    public function __get($name) {
        return $this->properties[$name] ?? null;
    }

    public function __set($name, $value) {
        $this->properties[$name] = $value;
    }

}