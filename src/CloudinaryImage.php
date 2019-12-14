<?php


namespace Dymantic\MultilingualPostsCloudinary;


use Dymantic\MultilingualPosts\ImageConversions;
use Dymantic\MultilingualPosts\PostImageConversion;
use Illuminate\Database\Eloquent\Model;

class CloudinaryImage extends Model
{
    const BASE_URL = 'https://res.cloudinary.com';
    const GOOD_QUALITY = 80;
    const TOP_QUALITY = 92;

    protected $table = 'multilingual_posts_cloudinary';

    protected $fillable = [
        'post_id',
        'type',
        'public_id',
        'version',
        'url',
        'cloud_name',
    ];

    public function toArray()
    {
        $conversion = ImageConversions::configured()
                                      ->filter(function (PostImageConversion $conversion) {
                                          return in_array($this->type, $conversion->collections);
                                      })
                                      ->flatMap(function (PostImageConversion $conversion) {
                                          return [$conversion->name => $this->buildConversionUrl($conversion)];
                                      })->all();

        return [
            'src'         => $this->url,
            'conversions' => $conversion,
        ];
    }

    private function buildConversionUrl(PostImageConversion $conversion)
    {
        $crop = $conversion->manipulation === 'crop' ? 'fill' : 'limit';
        $width = $conversion->width;
        $height = $conversion->height;
        $quality = $conversion->optimize ? static::GOOD_QUALITY : static::TOP_QUALITY;
        $transform = sprintf("c_%s,w_%s,h_%s,q_%s", $crop, $width, $height, $quality);
        $extension = pathinfo($this->url, PATHINFO_EXTENSION);

        return sprintf("%s/%s/image/upload/%s/v%s/%s.%s", static::BASE_URL, $this->cloud_name, $transform,
            $this->version, $this->public_id, $extension);
    }
}