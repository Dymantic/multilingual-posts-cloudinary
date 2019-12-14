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
            $config = config('multilingual-posts.cloudinary');
            return new CloudinaryClient($config['cloud_name'], $config['key'], $config['secret']);
        });

        $this->app->bind(CloudinaryBroker::class, function($app) {
            return new CloudinaryBroker($app->make(UploadClient::class));
        });
    }
}