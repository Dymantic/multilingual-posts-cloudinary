<?php


namespace Dymantic\MultilingualPostsCloudinary;


use Cloudinary\Uploader;
use Illuminate\Http\UploadedFile;

class CloudinaryClient
{
    public $cloud_name;
    private $key;
    private $secret;
    private $folder;

    public function __construct($cloud_name, $key, $secret, $folder = false)
    {
        $this->cloud_name = $cloud_name;
        $this->key = $key;
        $this->secret = $secret;
        $this->folder = $folder;

        \Cloudinary::config([
            "cloud_name" => $cloud_name,
            "api_key" => $key,
            "api_secret" => $secret,
            "secure" => true
        ]);
    }

    public function upload(UploadedFile $file, $options = [])
    {
        $response =  Uploader::upload($file, $this->makeOptions($options));

        return new CloudinaryUpload($response, $this->cloud_name);
    }

    public function destroy($public_id)
    {
        $response = Uploader::destroy($public_id);

        $result = $response['result'] ?? 'fail';

        return $result === 'ok';
    }

    private function makeOptions($options = [])
    {
        if(!$this->folder) {
            return $options;
        }

        return array_merge(['folder' => $this->folder], $options);
    }
}