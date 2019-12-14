<?php


namespace Dymantic\MultilingualPostsCloudinary;


use Dymantic\MultilingualPosts\Image;
use Dymantic\MultilingualPosts\MediaBroker;

class CloudinaryBroker implements MediaBroker
{
    const TITLE_TYPE = 'title-images';
    const BODY_TYPE = 'body-images';

    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function setTitleImage($post, $file): Image
    {
        $this->clearPreviousTitleImages($post->id);

        $response = $this->client->upload($file, ['max-width' => 2400]);

        return $this->saveTitleImageInfo($response, $post);
    }


    public function titleImage($post): Image
    {
        $cloudinary_image = CloudinaryImage::where('type', CloudinaryBroker::TITLE_TYPE)->where('post_id',
            $post->id)->latest()->first();

        if (!$cloudinary_image) {
            return new Image();
        }

        $image_data = $cloudinary_image->toArray();

        return new Image($image_data['src'], $image_data['conversions']);
    }

    public function attachImage($post, $file): Image
    {
        $result = $this->client->upload($file, ['max-width' => 2000]);

        return $this->saveBodyImageInfo($result, $post);
    }

    private function saveTitleImageInfo($cloudinary, $post)
    {
        return $this->saveImageInfo(static::TITLE_TYPE, $cloudinary, $post);
    }

    private function saveBodyImageInfo($cloudinary, $post)
    {
        return $this->saveImageInfo(static::BODY_TYPE, $cloudinary, $post);
    }

    private function saveImageInfo($type, $cloudinary, $post)
    {
        $image = CloudinaryImage::create([
            'post_id'    => $post->id,
            'type'       => $type,
            'public_id'  => $cloudinary['public_id'],
            'version'    => $cloudinary['version'],
            'url'        => $cloudinary['url'],
            'cloud_name' => $this->client->cloud_name
        ])->toArray();

        return new Image($image['src'], $image['conversions']);
    }

    private function clearPreviousTitleImages($post_id)
    {
        $existing = CloudinaryImage::where('type', CloudinaryBroker::TITLE_TYPE)->where('post_id', $post_id)->get();

        if (!$existing) {
            return;
        }

        $existing->each(function ($image) {
            $this->client->destroy($image->public_id);
            $image->delete();
        });
    }
}