<?php


namespace Dymantic\MultilingualPostsCloudinary\Tests;


use Cloudinary\Uploader;
use Dymantic\MultilingualPostsCloudinary\CloudinaryUpload;
use Dymantic\MultilingualPostsCloudinary\UploadClient;
use Illuminate\Http\UploadedFile;
use Mockery;

class CloudinaryClientTest extends TestCase
{
    /**
     * @test
     */
    public function upload_an_image_to_cloudinary()
    {
        config([
            'multilingual-posts.cloudinary' => [
                'cloud_name' => 'testtest',
                'key'        => '62728283637282',
                'secret'     => 'super-secret',
                'folder'     => 'blog-images'
            ]
        ]);

        $image = UploadedFile::fake()->image('testpic.png', 2000, 1000);

        $uploader = Mockery::mock("alias:". Uploader::class);
        $uploader->shouldReceive('upload')
                 ->once()
                 ->with($image, ['width' => 1400, 'folder' => 'blog-images'])
                 ->andReturn([
                     "public_id"         => "blog-images/hzwo4p7hfdteoc1lr9ol",
                     "version"           => 1576901166,
                     "signature"         => "0e1bac580adfcec59a678eeee556de62979a49d4",
                     "width"             => 1400,
                     "height"            => 700,
                     "format"            => "png",
                     "resource_type"     => "image",
                     "created_at"        => "2019-12-21T04:06:06Z",
                     "tags"              => [],
                     "bytes"             => 411,
                     "type"              => "upload",
                     "etag"              => "b5fc08a647ae6db3dc34bdef57e5a8ba",
                     "placeholder"       => false,
                     "url"               => "http://res.cloudinary.com/dymanticdesign/image/upload/v1576901166/test/hzwo4p7hfdteoc1lr9ol.png",
                     "secure_url"        => "https://res.cloudinary.com/dymanticdesign/image/upload/v1576901166/test/hzwo4p7hfdteoc1lr9ol.png",
                     "original_filename" => "php25DPBG",
                 ]);

        $client = app(UploadClient::class);

        $upload = $client->upload($image, ['width' => 1400]);
        $this->assertInstanceOf(CloudinaryUpload::class, $upload);
        $this->assertEquals('blog-images/hzwo4p7hfdteoc1lr9ol', $upload->public_id);
        $this->assertEquals('1576901166', $upload->version);
        $this->assertEquals('testtest', $upload->cloud_name);
        $this->assertEquals('https://res.cloudinary.com/dymanticdesign/image/upload/v1576901166/test/hzwo4p7hfdteoc1lr9ol.png', $upload->url);
    }

    /**
     *@test
     */
    public function destroy_an_image()
    {
        config([
            'multilingual-posts.cloudinary' => [
                'cloud_name' => 'testtest',
                'key'        => '62728283637282',
                'secret'     => 'super-secret',
                'folder'     => 'blog-images'
            ]
        ]);

        $uploader = Mockery::mock("alias:". Uploader::class);
        $uploader->shouldReceive('destroy')
                 ->once()
                 ->with('abcdef')
                 ->andReturn(['result' => 'ok']);

        $client = app(UploadClient::class);

        $deleted = $client->destroy('abcdef');

        $this->assertTrue($deleted);
    }
}