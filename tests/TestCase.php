<?php

namespace Dymantic\MultilingualPostsCloudinary\Tests;

use Dymantic\MultilingualPosts\MultilingualPostsServiceProvider;
use Dymantic\MultilingualPostsCloudinary\MultilingualPostsCloudinaryServiceProvider;
use File;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Exceptions\Handler;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{

    const CLOUD_NAME = 'xxxxxx';
    const CL_VERSION = '123456789';


    public function setUp() :void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            MultilingualPostsServiceProvider::class,
            MultilingualPostsCloudinaryServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->initializeDirectory(__DIR__ . '/temp');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('filesystems.disks.media', [
            'driver' => 'local',
            'root'   => __DIR__ . '/temp/media',
        ]);



        $app->bind('path.public', function () {
            return __DIR__ . '/temp';
        });

        $app['config']->set('sluggable', [
            'source' => null,
            'maxLength' => null,
            'maxLengthKeepWords' => true,
            'method' => null,
            'separator' => '-',
            'unique' => true,
            'uniqueSuffix' => null,
            'includeTrashed' => false,
            'reserved' => null,
            'onUpdate' => false,

        ]);

        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {

        $app['db']->connection()->getSchemaBuilder()->create('multilingual_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('author_id')->nullable();
            $table->string('author_type')->nullable();
            $table->json('title');
            $table->string('slug')->nullable();
            $table->json('description')->nullable();
            $table->json('intro')->nullable();
            $table->json('body')->nullable();
            $table->date('first_published_on')->nullable();
            $table->date('publish_date')->nullable();
            $table->boolean('is_draft')->default(1);
            $table->nullableTimestamps();
        });

        include_once __DIR__ . '/../database/migrations/create_multilingual_posts_cloudinary_table.php.stub';

        (new \CreateMultilingualPostsCloudinaryTable())->up();
    }

    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory);
    }
}