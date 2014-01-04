<?php
/**
 * @see http://www.php.net/manual/de/phar.using.intro.php
 */

if (php_sapi_name() != 'cli') {
  die("Need to run on the command line\n");
}


try {
  if (!Phar::canWrite()) {
    die("Can't write phar archive\n");
  }
  $p = new Phar('../entangle.phar');
  $p->startBuffering();
  
  $files = array(
    'index.php',
    'user.php',
    'event.php',
    'pushover.php',
    'db-entangle-sqlite.sql',
    
    'app/api.php',
    
    'views/_alert.html.php',
    'views/_edit_displays.html.php',
    'views/edit.html.php',
    'views/_edit_locations.html.php',
    'views/_edit_profile.html.php',
    'views/_edit_subscriptions.html.php',
    'views/_edit_timelines.html.php',
    'views/_footer_edit.html.php',
    'views/_footer_event.html.php',
    'views/_footer_login.html.php',
    'views/homepage.html.php',
    'views/index.html.php',
    'views/layout.html.php',
    'views/_navbar.html.php',
    'views/register.html.php',
    
    'js/edit.js',
    'js/scripts.js',
    'js/timelines.js',
    
    'css/edit.css',
    'css/styles.css',
    'css/timelines.css',
    
    'vendor/autoload.php',
    
    'vendor/composer/autoload_classmap.php',
    'vendor/composer/autoload_files.php',
    'vendor/composer/autoload_namespaces.php',
    'vendor/composer/autoload_real.php',
    'vendor/composer/ClassLoader.php',
    
    'vendor/dispatch/dispatch/src/dispatch.php',
    
    'vendor/j4mie/idiorm/idiorm.php',
  );
  
  $hash = array();
  foreach ($files as $f) {
    $hash[$f] = $f;
  }
  
  $p->buildFromIterator(
    new ArrayIterator($hash)
  );
  $p->setDefaultStub('index.php');
  $p->stopBuffering();
  $p->compressFiles(Phar::GZ);
} catch (Exception $e) {
  die("Could not open Phar: " . $e);
}
