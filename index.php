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
const VERSION = '0.1.2';
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

config('dispatch.views', 'views');

$here = dirname(__FILE__) . '/..';
if (!file_exists("{$here}/settings.ini")) {
  if (strpos($here, 'phar://') === 0) {
    $here = substr($here, 7);
    $here = substr($here, 0, strpos($here, '/entangle.phar'));

    on('GET', '/js/:file', function () {
      header('Content-type: application/javascript');
      readfile('js/' . params('file'));
    });
    on('GET', '/css/:file', function () {
      header('Content-type: text/css');
      readfile('css/' . params('file'));
    });
  }
  file_put_contents("{$here}/settings.ini",
    "; entangle! - https://entangle.de\n; Enter config options here\n"
  );
}
config('source', "{$here}/settings.ini");

if (!config('db.name')) {
  config('db.name', "sqlite:{$here}/entangle.sqlite");
}

$needs_init = FALSE;
if (!file_exists(preg_replace('/^sqlite:/', '', config('db.name')))) {
  $needs_init = TRUE;
}

ORM::configure(config('db.name'));
ORM::configure('return_result_sets', TRUE);

if ($needs_init) {
  unset($_SESSION['user']);
  $db = ORM::get_db();
  foreach (explode(';', file_get_contents('db-entangle-sqlite.sql')) as $sql) {
    $db->exec($sql);
  }
  flash('success', 'Database has been set up. Now register your account');
  redirect('/user/register');
}

/**
 * Similar to dispatch.scope, but keep values as stack
 */
function stack($name, $value = null) {

  static $_stash = array();

  if ($value === null) {
    return isset($_stash[$name]) ? array_pop($_stash[$name]) : NULL;
  }

  if (!isset($_stash[$name])) {
    $_stash[$name] = array();
  }
  return array_push($_stash[$name], $value);
}

prefix('/user', function () { include 'user.php'; });
prefix('/event', function () { include 'event.php'; });
prefix('/oauth', function () { include 'oauth.php'; });

on('GET', '/events(/:page@\d+)', function ($page) {

  if (!session('user')) {
    return render('homepage', array(
      'page_title' => 'Entangled lifes.',
    ));
  }

  if (empty($page)) {
    $page = 0;
  }

  $events = ORM::for_table('event')
    ->select('event.*')
    ->select('location.title', 'location_title')
    ->select('user.id', 'user_id')
    ->select('user.realname', 'user_realname')
    ->left_outer_join('location', array('event.location_id', '=', 'location.id'))
    ->left_outer_join('timeline', array('event.timeline_id', '=', 'timeline.id'))
    ->left_outer_join('user', array('timeline.user_id', '=', 'user.id'))
    ->order_by_desc('date_from')
    ->order_by_asc('timeline_id')
    ->offset($page * PAGE_SIZE)
    ->limit(PAGE_SIZE)
    ->find_result_set();

  $columns = array();
  //for ($i = 0; $i < count($events); $i++) {
  foreach ($events as $i => $event) {
    $col = $i % NO_COL;
    $columns[$col][] = $event;
  }

  render('events', array(
    'page_title' => 'Events',
    'events' => $events,
    'columns' => $columns,
    'column_width' => 12 / NO_COL,
  ));
});

on('GET', '/', function () {

  if (!session('user')) {
    return render('homepage', array(
      'page_title' => 'Entangled lifes.',
    ));
  }

  $entangled = ORM::for_table('entangled')
    ->where_equal('user_id', $_SESSION['user']->id)
    ->where_equal('title', 'Start')
    ->find_one();

  $timelines = array();
  $event_timelines = array();
  foreach (ORM::for_table('entangled_timeline')
    ->left_outer_join('timeline', array('entangled_timeline.timeline_id', '=', 'timeline.id'))
    ->where_equal('entangled_timeline.entangled_id', $entangled->id)
    ->find_many() as $timeline) {

    if (empty($timeline->timelines)) {
      $timeline->timelines = array();
    }
    else {
      $timeline->timelines = explode(',', $timeline->timelines);
    }
    $timelines[$timeline->id] = $timeline;

    if (0 == count($timeline->timelines)) {
      $event_timelines[] = $timeline->id;
    }
    else {
      $event_timelines = array_merge($event_timelines, $timeline->timelines);
    }
  }

  $events = ORM::for_table('event')
    ->select('event.*')
    ->select('location.title', 'location_title')
    ->select('user.id', 'user_id')
    ->select('user.realname', 'user_realname')
    ->left_outer_join('location', array('event.location_id', '=', 'location.id'))
    ->left_outer_join('timeline', array('event.timeline_id', '=', 'timeline.id'))
    ->left_outer_join('user', array('timeline.user_id', '=', 'user.id'))
    ->where_in('timeline_id', $event_timelines)
    ->order_by_desc('date_from')
    ->order_by_asc('timeline_id');

  /*
   * Make sure the logged-in user either has ID=1 or the events belong to her
   */
  if ($_SESSION['user']->id != 1) {
    $events->where('user_id', $_SESSION['user']->id);
  }

  $vector = new TimeVector($events->find_result_set(), /*future*/TRUE);
  $points = $vector->points();

  $named_timelines = ORM::for_table('timeline')
  	->select_many('timeline.id', 'timeline.user_id', 'timeline.title')
    ->select('user.realname', 'user_realname')
    ->left_outer_join('user', array('timeline.user_id', '=', 'user.id'))
    ->order_by_asc('user_realname', 'title')
  	->find_result_set();

  render('index', array(
    'page_title' => $entangled->title,
    'timelines' => $timelines,
    'named_timelines' => $named_timelines,
    'points' => $points->points,
    'point_count' => $points->count,
  ));
});

on('GET', '/_i', function () { phpinfo(); });

prefix('/alert', function () { include 'pushover.php'; });

on('GET', '/:username', function () {

  $user = ORM::for_table('user')
    ->where('username', params('username'))
    ->find_one();

  if (!$user) {
    error(500, 'No such user');
  }

  $events = ORM::for_table('event')
    ->select('event.*')
    ->select('location.title', 'location_title')
    ->select('location.longitude', 'location_longitude')
    ->select('location.latitude', 'location_latitude')
    ->select('timeline.name', 'timeline_name')
    ->select('timeline.title', 'timeline_title')
    ->select('user.id', 'user_id')
    ->left_outer_join('location', array('event.location_id', '=', 'location.id'))
    ->left_outer_join('timeline', array('event.timeline_id', '=', 'timeline.id'))
    ->left_outer_join('user', array('timeline.user_id', '=', 'user.id'))
    ->where('timeline.user_id', $user->id)
    ->where('public', 1)
    ->order_by_desc('date_from')
    ->order_by_asc('timeline_id');

  $since = params('since');
  if (!empty($since)) {
    $events->where_gt('updated', $since);
  }

  if (in_array('application/json', explode(',', $_SERVER["HTTP_ACCEPT"]))) {
    echo json_out($events->find_array());
    return;
  }

  $points = points($events->find_result_set());
  if (0 == count($points->timelines)) {
    // Pointless to render anything if there are no timelines with public events
    error(500, "No public events");
  }
  else {

    stack('footer', partial('footer_login'));

    $timelines = ORM::for_table('timeline')
    	->select_many('id', 'title')
      ->where_in('id', $points->timelines)
      ->order_by_asc('title')
    	->find_result_set();

    render('index', array(
      'page_title' => $user->realname,
      'timelines' => $timelines,
      'points' => $points->points,
      'point_count' => $points->count,
    ));
  }
});

dispatch();
