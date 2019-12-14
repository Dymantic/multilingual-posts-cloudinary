<?php


namespace Dymantic\MultilingualPostsCloudinary;


class CloudinaryClient
{
    public $cloud_name;
    private $key;
    private $secret;

    public function __construct($cloud_name, $key, $secret)
    {
        $this->cloud_name = $cloud_name;
        $this->key = $key;
        $this->secret = $secret;
    }
}