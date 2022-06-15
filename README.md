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
