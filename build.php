<?php
/**
 * entangle! Connected timelines on the web
 *
 * Copyright (C) 2014 Olav Schettler https://entangle.de
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *  
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
