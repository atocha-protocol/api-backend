## Laravel documents
* https://laravel.com/docs/9.x#choosing-your-sail-services
* Install curl -s "https://laravel.build/example-app?with=mysql,redis" | bash

## Twitter setting
* `https://developer.twitter.com/en/portal/dashboard`

## Start service
* start service for develop `./vendor/bin/sail up`

## Stop service 
* `./vendor/bin/sail down`

## Install  `abraham/twitteroauth`
* If use docker to start service try to `./vendor/bin/sail composer require abraham/twitteroauth`

## TwitterOAuth refer
* `https://twitteroauth.com/`

## Twitter for test on postman
* `https://www.postman.com/twitter/workspace/twitter-s-public-workspace/collection/9956214-784efcda-ed4c-4491-a4c0-a26470a67400?ctx=documentation`

## Create twitter table
* `./vendor/bin/sail artisan make:migration twitter_bind`
* fill migration content:
```text
        Schema::create('twitter_bind', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ato_address')->unique();
            $table->string('twitter_screen_name')->nullable()->unique();
            $table->string('twitter_profile_image_url')->nullable();
            $table->string('twitter_profile_image_url_https')->nullable();
            $table->string('twitter_full_data');
            $table->timestamps();
        });
```

## Go to migrate
* `./vendor/bin/sail artisan migrate`


## Bind twitter with ADDRESS
* Example:
```text
http://localhost:6999/bind/5EUwwkgp1wyNNaG9QEdsM5EFWtS46WUcDT5Bkq4tEJapD9ZP
```

## Support cors
* View `https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS`

## Install twitter-api-v2
* `./vendor/bin/sail composer require noweh/twitter-api-v2-php`

## Develop for TaskReward
### Make Controller
* ./vendor/bin/sail artisan make:controller TaskController --resource

### Make data migrations
* `./vendor/bin/sail artisan make:migration task_reward`
* `./vendor/bin/sail artisan make:migration task_request`

### Fill data schema.
* For TaskReward
```php
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_reward', function (Blueprint $table) {
            $table->id();
            $table->string('task_kind', 50);
            $table->string('task_title', 255);
            $table->text('task_detail');
            $table->bigInteger('task_prize');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_reward');
    }
};
```

* For TaskRequest
```php
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_request', function (Blueprint $table) {
            $table->id();
            $table->string('request_owner', 100);
            $table->integer('request_status');
            $table->text('request_detail');
            $table->timestamps();
            $table->unsignedBigInteger('task_id')->index()->comment('Foreign key with task_reward');
            $table->foreign('task_id')->references('id')->on('task_reward');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_request');
    }
};
```
* Run migrate `./vendor/bin/sail artisan migrate`


### Make module 

* Make Module of task_reward table 
`./vendor/bin/sail artisan make:model TaskReward`

* Make Module of task_request table   
`./vendor/bin/sail artisan make:model TaskRequest`

## For dashboard with 
```text
./vendor/bin/sail composer require backpack/crud
./vendor/bin/sail artisan backpack:install

# (optional) require Backpack's tool that allows you to generate CRUDs
./vendor/bin/sail composer require --dev backpack/generators
```

### Login for testing
* Open `your-app-name/admin` on your browser.
* Check user table and login

### Configure
In most cases, it's a good idea to look at the configuration files and make the admin panel your own:

You should change the configuration values in config/backpack/base.php to make the admin panel your own. Backpack is white label, so you can change everything: menu color, project name, developer name etc.
By default all users are considered admins; If that's not what you want in your application (you have both users and admins), please:
Change app/Http/Middleware/CheckIfAdmin.php, particularly checkIfUserIsAdmin($user), to make sure you only allow admins to access the admin panel;
Change app/Providers/RouteServiceProvider::HOME, which will send logged in (but not admin) users to /home, to something that works for your app;
If your User model has been moved from the default App\Models\User.php, please change config/backpack/base.php to use the correct user model under the user_model_fqn config key;


### Make module

* Make single `./vendor/bin/sail artisan backpack:crud User`
* Or make all `./vendor/bin/sail artisan backpack:build`


### Input laravel/ui for user manage

