<?php 

ini_set('display_errors', TRUE);
error_reporting(-1);

date_default_timezone_set('Europe/Berlin');

session_set_cookie_params(86400 * 7);
session_start();

require_once 'vendor/autoload.php';

ORM::configure('sqlite:db/entangle.sqlite');

config('dispatch.views', './views');
config('source', 'settings.ini');

on('GET', '/', function () {
  render('index', array(
    'site_name' => config('site.name'),
    'page_title' => config('site.title'),
  ));
});

dispatch();