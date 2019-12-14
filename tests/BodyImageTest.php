<?php


namespace Dymantic\MultilingualPostsCloudinary\Tests;


use Dymantic\MultilingualPosts\Image;
use Dymantic\MultilingualPosts\Post;
use Dymantic\MultilingualPostsCloudinary\CloudinaryBroker;
use Dymantic\MultilingualPostsCloudinary\CloudinaryClient;
use Dymantic\MultilingualPostsCloudinary\CloudinaryImage;
use Dymantic\MultilingualPostsCloudinary\UploadClient;
use Illuminate\Http\UploadedFile;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class BodyImageTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    /**
     *@test
     */
    public function attach_a_body_image_to_a_post()
    {
        $this->setConfig();
        app()->instance(UploadClient::class, $this->makeClientMock());

        $post = Post::create(['title' => 'test title']);
        $broker = app(CloudinaryBroker::class);

        $image = $broker->attachImage($post, UploadedFile::fake()->image('testpic.jpg'));

        $expected_base = "https://res.cloudinary.com/xxxxxx/image/upload/";
        $expected_src = $expected_base . "v123456789/abcdefg.jpg";
        $expected_web_conversion = $expected_base . "c_limit,w_1200,h_900,q_92/v123456789/abcdefg.jpg";
        $expected_thumb_conversion = $expected_base . "c_fill,w_400,h_300,q_80/v123456789/abcdefg.jpg";

        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals($expected_src,$image->src);
        $this->assertEquals($expected_web_conversion, $image->getUrl('web'));
        $this->assertEquals($expected_thumb_conversion, $image->getUrl('thumb'));

        $this->assertDatabaseHas('multilingual_posts_cloudinary', [
            'post_id' => $post->id,
            'public_id' => 'abcdefg',
            'version' => TestCase::CL_VERSION,
            'url' => 'https://res.cloudinary.com/xxxxxx/image/upload/v123456789/abcdefg.jpg',
            'type' => CloudinaryBroker::BODY_TYPE,
            'cloud_name' => TestCase::CLOUD_NAME,
        ]);
    }



    private function makeClientMock()
    {
        $mockClient = Mockery::mock(CloudinaryClient::class, [TestCase::CLOUD_NAME, 'key', 'secret']);
        $mockClient->shouldReceive('upload')
                   ->once()
                   ->withArgs(function($arg1, $arg2) {
                       if(!($arg1 instanceof UploadedFile)) {
                           return false;
                       }

                       if(!is_array($arg2)) {
                           return false;
                       }

                       return true;
                   })
                   ->andReturn([
                       'url' => 'https://res.cloudinary.com/xxxxxx/image/upload/v123456789/abcdefg.jpg',
                       'version' => TestCase::CL_VERSION,
                       'public_id' => 'abcdefg'
                   ]);
        return $mockClient;
    }

    private function setConfig()
    {
        config(['multilingual-posts.cloudinary' => [
            'cloud_name' => TestCase::CLOUD_NAME,
            'key' => 'key',
            'secret' => 'secret'
        ]]);
        config([
            'multilingual-posts.conversions' => [
                [
                    'name'         => 'thumb',
                    'manipulation' => 'crop',
                    'width'        => '400',
                    'height'       => '300',
                    'title'        => false,
                    'post'         => true,
                    'optimize'     => true,
                ],
                [
                    'name'         => 'web',
                    'manipulation' => 'fit',
                    'width'        => '1200',
                    'height'       => '900',
                    'title'        => true,
                    'post'         => true,
                    'optimize'     => false,
                ],
                [
                    'name'         => 'banner',
                    'manipulation' => 'crop',
                    'width'        => '2000',
                    'height'       => '1200',
                    'title'        => true,
                    'post'         => false,
                    'optimize'     => false,
                ]
            ]
        ]);
    }
}