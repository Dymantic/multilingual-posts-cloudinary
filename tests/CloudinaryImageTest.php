<?php


namespace Dymantic\MultilingualPostsCloudinary\Tests;


use Dymantic\MultilingualPosts\Post;
use Dymantic\MultilingualPostsCloudinary\CloudinaryBroker;
use Dymantic\MultilingualPostsCloudinary\CloudinaryImage;

class CloudinaryImageTest extends TestCase
{
    /**
     * @test
     */
    public function it_presents_with_conversions()
    {
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
        $post = Post::create(['title' => 'test title']);
        $image = CloudinaryImage::create([
            'post_id'    => $post->id,
            'type'       => CloudinaryBroker::TITLE_TYPE,
            'public_id'  => 'eneivicys42bq5f2jpn2',
            'version'    => '1570979139',
            'url'        => 'https://res.cloudinary.com/xxxxxx/image/upload/v1570979139/eneivicys42bq5f2jpn2.jpg',
            'cloud_name' => 'xxxxxx'
        ]);

        $expected_base = 'https://res.cloudinary.com/xxxxxx/image/upload/';
        $expected = [
            'src'         => $expected_base . 'v1570979139/eneivicys42bq5f2jpn2.jpg',
            'conversions' => [
                'web'    => $expected_base . 'c_limit,w_1200,h_900,q_92/v1570979139/eneivicys42bq5f2jpn2.jpg',
                'banner' => $expected_base . 'c_fill,w_2000,h_1200,q_92/v1570979139/eneivicys42bq5f2jpn2.jpg'
            ]
        ];

        $this->assertEquals($expected, $image->toArray());
    }
}