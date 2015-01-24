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
 */
const VERSION = '0.2.0';
const NO_COL = 3;
const PAGE_SIZE = 20;

ini_set('display_errors', TRUE);
error_reporting(-1);

date_default_timezone_set('Europe/Berlin');

session_set_cookie_params(86400 * 7);
session_start();

require_once 'vendor/autoload.php';

use Entangle\DateTime;
use Entangle\TimeVector;

use Controller\Entangled as EntangledController;
use Controller\User as UserController;
use Controller\Timeline as TimelineController;
use Controller\Location as LocationController;

use Granada\ORM;
use Granada\Model;

use Entangle\App;

App::bootstrap();

prefix('/user', function ()
{
  $controller = new UserController;

  before(function ($method, $path) use ($controller) { return $controller->before(); });

  on('POST', '/login', function () use ($controller) { return $controller->login(); });
  on('GET', '/logout', function () use ($controller) { return $controller->logout(); });
  on('GET', '/register', function () use ($controller) { return $controller->registerForm(); });
  on('GET', '/edit', function () use ($controller) { return $controller->edit(); });
  on('POST', '/add', function () use ($controller) { return $controller->addSubscription(); });
  on('POST', '/edit_subscription', function () use ($controller) { return $controller->editSubscription(); });
  on('POST', '/register', function () use ($controller) { return $controller->register(); });
  on('POST', '/edit_profile', function () use ($controller) { return $controller->editProfile(); });
  on('GET', '/update/:user_id', function () use ($controller) { return $controller->update(); });
  on('POST', '/del/:id', function () use ($controller) { return $controller->del(); });
});

prefix('/timeline', function ()
{
  $controller = new TimelineController;

  on('POST', '/add', function () use ($controller) { return $controller->add(); });
  on('POST', '/del/:id', function () use ($controller) { return $controller->del(); });
  on('POST', '/edit', function () use ($controller) { return $controller->edit(); });
});

prefix('/display', function ()
{
  $controller = new EntangledController;

  on( 'POST', '/add', function () use ( $controller ) { return $controller->add(); } );
  on( 'POST', '/del/:id', function () use ( $controller ) { return $controller->del(); } );
  on( 'POST', '/edit', function () use ( $controller ) { return $controller->edit(); } );
});

prefix('/location', function ()
{
  $controller = new LocationController;

  on('POST', '/add', function () use ( $controller ) { return $controller->add(); });
  on('POST', '/del/:id', function () use ( $controller ) { return $controller->del(); });
  on('POST', '/edit', function () use ( $controller ) { return $controller->edit(); });
});

prefix('/event', function () { include 'event.php'; });

on('GET', '/', function ()
{
  $controller = new EntangledController;

  if (!session('user')) {
    return $controller->homepage();
  }

  return $controller->timelines();
});

on('GET', '/_i', function () { phpinfo(); });

prefix('/alert', function () { include 'pushover.php'; });

on('GET', '/:username', function ()
{
  $controller = new UserController;
  return $controller->timeline();
});

dispatch();
