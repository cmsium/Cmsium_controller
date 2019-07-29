<?php

namespace Tests\Feature;

use Mockery;
use Testgear\DB\RefreshTables;
use Tests\AppTestCase;

class FilesTest extends AppTestCase {

    use RefreshTables;

    protected $fileServerRequestMock;
    /**
     * @var \App\Models\File
     */
    protected $file;

    protected function setUp() : void {
        parent::setUp();

        $this->fileServerRequestMock = Mockery::mock('overload:App\Utils\FileServerRequest');
        $this->fileServerRequestMock
            ->shouldReceive('post')
            ->with('meta')
            ->andReturn(true);
        $this->fileServerRequestMock
            ->shouldReceive('requestServersStatus')
            ->andReturn([
                'id'       => 1,
                'priority' => 0,
                'url'      => 'http://'.parse_url($this->faker->url, PHP_URL_HOST).'/'
            ]);

        $file = new \App\Models\File;
        $file->setFileRealProps($this->faker->word.'.'.$this->faker->fileExtension);
        $url = $this->faker->url;
        $file->massAssign([
            'size'        => $this->faker->randomNumber(6),
            'server_host' => 'http://'.parse_url($url, PHP_URL_HOST).'/',
            'user_id'     => $this->faker->md5,
            'temp'        => 0,
            'url'         => $url
        ]);
        $this->file = $file->save();
    }

    public function testGetFile() {
        $response = $this->getJson("/file/{$this->file->file_id}", [
            'x-user-token' => $this->faker->md5
        ]);

        $response->assertJson([
            'url' => $this->file->url
        ]);
    }

    public function testGetFileNotFound() {
        $fakeId = md5('ZA_WARUDO');
        $response = $this->getJson("/file/$fakeId", [
            'x-user-token' => $this->faker->md5
        ]);

        $response->assertStatus(404);
    }

    public function testUploadFile() {
        $realName = $this->faker->word;
        $extension = $this->faker->fileExtension;
        $data = [
            'name' => "$realName.$extension",
            'size' => $this->faker->randomNumber(6),
            'temp' => 'false'
        ];

        $response = $this->postJson('/file', $data, [
            'x-user-token' => $this->faker->md5
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('files_info', [
            'real_name' => $realName,
            'extension' => $extension
        ]);
    }

    public function testUploadFileFailed() {
        $realName = $this->faker->word;
        $extension = $this->faker->fileExtension;
        $data = [
            'name' => "$realName.$extension",
            'size' => $this->faker->randomNumber(6),
            'temp' => false
        ];

        $response = $this->postJson('/file', $data, [
            'x-user-token' => $this->faker->md5
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('files_info', [
            'real_name' => $realName,
            'extension' => $extension
        ]);
    }

    public function testDeleteFile() {
        $response = $this->deleteJson("/file/{$this->file->file_id}", [
            'x-user-token' => $this->faker->md5
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('files_info', [
            'file_id' => $this->file->file_id
        ]);
    }

    public function testDeleteFileNotFound() {
        $response = $this->deleteJson("/file/{$this->faker->md5}", [
            'x-user-token' => $this->faker->md5
        ]);

        $response->assertStatus(404);
    }

}