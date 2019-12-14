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
use Mockery\Mock;

class TitleImagesTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
    /**
     *@test
     */
    public function set_a_title_image()
    {
        $this->setConfig();
        app()->instance(UploadClient::class, $this->makeClientMock());

        $post = Post::create(['title' => 'test title']);
        $broker = app(CloudinaryBroker::class);

        $image = $broker->setTitleImage($post, UploadedFile::fake()->image('testpic.jpg'));

        $expected_base = "https://res.cloudinary.com/xxxxxx/image/upload/";
        $expected_src = $expected_base . "v123456789/abcdefg.jpg";
        $expected_web_conversion = $expected_base . "c_limit,w_1200,h_900,q_92/v123456789/abcdefg.jpg";
        $expected_banner_conversion = $expected_base . "c_fill,w_2000,h_1200,q_92/v123456789/abcdefg.jpg";

        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals($expected_src,$image->src);
        $this->assertEquals($expected_web_conversion, $image->getUrl('web'));
        $this->assertEquals($expected_banner_conversion, $image->getUrl('banner'));

        $this->assertDatabaseHas('multilingual_posts_cloudinary', [
            'post_id' => $post->id,
            'public_id' => 'abcdefg',
            'version' => TestCase::CL_VERSION,
            'url' => 'https://res.cloudinary.com/xxxxxx/image/upload/v123456789/abcdefg.jpg',
            'type' => CloudinaryBroker::TITLE_TYPE,
            'cloud_name' => TestCase::CLOUD_NAME,
        ]);
    }

    /**
     *@test
     */
    public function setting_a_title_image_clears_previous_title_images()
    {
        $post = Post::create(['title' => 'test title']);
        $original = CloudinaryImage::create([
            'post_id' => $post->id,
            'public_id' => 'original-title-image',
            'version' => TestCase::CL_VERSION,
            'url' => 'https://res.cloudinary.com/xxxxxx/image/upload/v123456789/abcdefg.jpg',
            'type' => CloudinaryBroker::TITLE_TYPE,
            'cloud_name' => TestCase::CLOUD_NAME,
        ]);
        app()->instance(UploadClient::class, $this->makeDeletingMockClient($original->public_id));

        $broker = app(CloudinaryBroker::class);

        $image = $broker->setTitleImage($post, UploadedFile::fake()->image('testpic.jpg'));

        $this->assertCount(1,
            CloudinaryImage::where('type', CloudinaryBroker::TITLE_TYPE)->where('post_id', $post->id)->get()
        );

        $this->assertDatabaseMissing('multilingual_posts_cloudinary', ['id' => $original->id]);
    }

    /**
     *@test
     */
    public function retrieve_title_image_for_post()
    {
        $this->setConfig();
        $post = Post::create(['title' => 'test title']);
        CloudinaryImage::create([
            'post_id' => $post->id,
            'public_id' => 'original-title-image',
            'version' => TestCase::CL_VERSION,
            'url' => 'https://res.cloudinary.com/xxxxxx/image/upload/v123456789/original-title-image.jpg',
            'type' => CloudinaryBroker::TITLE_TYPE,
            'cloud_name' => TestCase::CLOUD_NAME,
        ]);

        $broker = app(CloudinaryBroker::class);

        $image = $broker->titleImage($post);

        $expected_base = "https://res.cloudinary.com/xxxxxx/image/upload/";
        $expected_src = $expected_base . "v123456789/original-title-image.jpg";
        $expected_web_conversion = $expected_base . "c_limit,w_1200,h_900,q_92/v123456789/original-title-image.jpg";
        $expected_banner_conversion = $expected_base . "c_fill,w_2000,h_1200,q_92/v123456789/original-title-image.jpg";

        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals($expected_src,$image->src);
        $this->assertEquals($expected_web_conversion, $image->getUrl('web'));
        $this->assertEquals($expected_banner_conversion, $image->getUrl('banner'));
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

    private function makeDeletingMockClient($public_id)
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

        $mockClient->shouldReceive('destroy')
            ->once()
            ->withArgs([$public_id])
            ->andReturn(json_encode(['result' => 'ok']));
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
                    'optimize'     => false,
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