# entangle!

Lifelines connected

## Try it

Get the PHAR archive. Run it with

<pre>
    php -S 0.0.0.0:1111 entangle.phar
</pre>

Point your web server at http://your-server:1111/

## Installation

* Clone this repository

<pre>
    git clone git@github.com:oschettler/entangle.git htdocs
</pre>

* Point a webserver to the resulting directory, e.g. with PHP-enabled [NGinx](http://nginx.org/)

<pre>
    server {
      listen 80;
      server_name entangle.example.com;
      root /var/www/entangle-example/htdocs;
      location / {
        index index.php;
        try_files $uri $uri/ /index.php?$args;
      }
      access_log entangle-example.access.log;
      error_log /var/log/nginx/entangle-example.error.log;

      include common/php;
    }
</pre>

* Install [Composer](https://getcomposer.org) in htdocs and run it

<pre>
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install
</pre>

* Create a file settings.ini

    cp settings.ini.example ../settings.ini

* Create an SQlite database

<pre>
    mkdir ../db 
    sqlite ../db/entangle.sqlite &lt; db-entangle-sqlite.sql
    sudo chown -R www-data ../db
</pre>

* Open http://entangle.example.com in your browser, click on login / Register account

    <img src="https://www.evernote.com/shard/s1/sh/1b17f1ea-9312-4b30-a043-803f742e12a6/5bfd83b11d86604d9d4a841551a057df/deep/0/entangle!----Register-account.png">

* Login with this account

* Start logging your life's events

    <img src="https://www.evernote.com/shard/s1/sh/3be0f356-03c3-4dba-9cc0-d7d98f2e5133/6290fac600959fd7111edef8492ef3b7/deep/0/entangle!----Start.png">

