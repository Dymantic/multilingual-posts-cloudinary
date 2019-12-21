<?php


namespace Dymantic\MultilingualPostsCloudinary;


use Illuminate\Support\ServiceProvider;

class MultilingualPostsCloudinaryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! class_exists('CreateMultilingualPostsCloudinaryTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_multilingual_posts_cloudinary_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_multilingual_posts_cloudinary_table.php'),
            ], 'migrations');
        }
    }

    public function register()
    {

        $this->app->bind(UploadClient::class, function($app) {
            $cloud = config('multilingual-posts.cloudinary.cloud_name');
            $key = config('multilingual-posts.cloudinary.key');
            $secret = config('multilingual-posts.cloudinary.secret');
            $folder = config('multilingual-posts.cloudinary.folder');
            return new CloudinaryClient($cloud, $key, $secret, $folder);
        });

        $this->app->bind(CloudinaryBroker::class, function($app) {
            return new CloudinaryBroker($app->make(UploadClient::class));
        });
    }
}