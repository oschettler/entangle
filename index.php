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
const VERSION = '0.1.4';
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

use Granada\ORM;
use Granada\Model;

use Entangle\App;

App::bootstrap();

prefix('/user', function () { include 'user.php'; });
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
