<?php 

const USER_ID = 1;

ini_set('display_errors', TRUE);
error_reporting(-1);

date_default_timezone_set('Europe/Berlin');

session_set_cookie_params(86400 * 7);
session_start();

require_once 'vendor/autoload.php';

ORM::configure('sqlite:../db/entangle.sqlite');
ORM::configure('return_result_sets', TRUE);

config('dispatch.views', './views');
config('source', '../settings.ini');

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

on('GET', '/', function () { 

  if (!session('user')) {
    return render('homepage', array(
      'site_name' => config('site.name'),
      'page_title' => 'Entangled lifes.',
    ));
  }

  $entangled = ORM::for_table('entangled')
    ->where_equal('user_id', USER_ID)
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
    ->order_by_asc('timeline_id')
    ->find_result_set();
  
  $points = array();
  $point_count = 0;
  
  $points[time()][] = (object)array(
    'type' => 'now',
    'title' => '<strong>Heute</strong>',
    'event' => (object)array(
      'id' => 'now',
      'timeline_id' => NULL,
      'duration' => 1,
      'duration_unit' => 'd',
      'user_id' => USER_ID,
    )
  );
  $point_count++;
  
  foreach ($events as $event) {
    $date_from = strtotime($event->date_from);

    /*
     * Calculate end point
     */
    
    $from = strptime($event->date_from, '%Y-%m-%d');
        
    $date_to = NULL;
    if (!empty($event->date_to)) {
      if ($event->date_to != $event->date_from) {
        $date_to = strtotime($event->date_to) + 86399;
      }
    }
    else
    if (!empty($event->duration)) {
      if (!($event->duration == 1 && $event->duration_unit == 'd')) {
        
        switch ($event->duration_unit) {
          case 'y':
            $date_to = mktime(/*H*/0, /*M*/0, /*S*/-1, 
              $from['tm_mon'] + 1, 
              $from['tm_mday'],
              $from['tm_year'] + 1900 + $event->duration
            ); 
            break;
      
          case 'm':
            $date_to = mktime(/*H*/0, /*M*/0, /*S*/-1, 
              $from['tm_mon'] + 1 + $event->duration, 
              $from['tm_mday'],
              $from['tm_year'] + 1900
            ); 
            break;
      
          case 'd':
          default:
            $date_to = mktime(/*H*/0, /*M*/0, /*S*/-1, 
              $from['tm_mon'] + 1, 
              $from['tm_mday'] + $event->duration,
              $from['tm_year'] + 1900
            ); 
            break;
        }
      }
    } // duration

    if ($date_to) {
      // An interval
      $points[$date_from][] = (object)array(
        'type' => 'from', 
        'event' => $event,
      );
      $point_count++;
  
      $points[$date_to][] = (object)array(
        'type' => 'to', 
        'event' => $event, 
      );
      $point_count++;
    }
    else {
      // A single point
      $points[$date_from][] = (object)array(
        'type' => 'on', 
        'event' => $event,
      );
      $point_count++;
    }
    
    /*
     * Calculate anniversaries
     */
    
    if (!empty($event->anniversary)) {
      $next_year = time() + 86400 * 365;
      
      $i = 0;
      do {
        $i++;
        $anniversary = mktime(/*H*/0, /*M*/0, /*S*/0, 
          $from['tm_mon'] + 1, 
          $from['tm_mday'],
          $from['tm_year'] + 1900 + $i
        );
        $points[$anniversary][] = (object)array(
          'type' => 'anniversary',
          'event' => $event,
          'title' => sprintf($event->anniversary, $i),
        );
        $point_count++;
      }
      while ($anniversary < $next_year);
    }    
    
  } 
  krsort($points, SORT_NUMERIC);
  
  render('index', array(
    'site_name' => config('site.name'),
    'page_title' => $entangled->title,
    'timelines' => $timelines,
    'points' => $points,
    'point_count' => $point_count,
  ));
});

dispatch();
