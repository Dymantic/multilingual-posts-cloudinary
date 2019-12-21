<?php


namespace Dymantic\MultilingualPostsCloudinary;


class CloudinaryUpload
{
    public $public_id;
    public $version;
    public $url;
    public $cloud_name;

    public function __construct($response, $cloud_name)
    {
        $this->public_id = $response['public_id'];
        $this->version = $response['version'];
        $this->url = $response['secure_url'];
        $this->cloud_name = $cloud_name;
    }
}