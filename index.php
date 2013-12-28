<?php 

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

function mkdate($date) {
  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $result = $date;
  }
  else
  if (preg_match('/^\d{4}-\d{2}$/', $date)) {
    $result = $date . '-01';
  }
  else
  if (preg_match('/^\d{4}$/', $date)) {
    $result = $date . '-01-01';
  }
  return new DateTimeImmutable($result);
}

prefix('/user', function () { include 'user.php'; });
prefix('/event', function () { include 'event.php'; });

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
    ->order_by_asc('timeline_id')
    ->find_result_set();
  
  $points = array();
  $point_count = 0;
  
  $points[strftime('%Y-%m-%d')][] = (object)array(
    'type' => 'now',
    'title' => '<strong>Heute</strong>',
    'event' => (object)array(
      'id' => 'now',
      'timeline_id' => NULL,
      'duration' => 1,
      'duration_unit' => 'd',
      'user_id' => $_SESSION['user']->id,
    )
  );
  $point_count++;
  
  foreach ($events as $event) {
    $date_from = mkdate($event->date_from);    
    //var_dump(array($date_from, $event->date_from, $event->date_to, $event->anniversary));
    
    /*
     * Calculate end point
     */
    
    $date_to = NULL;
    if (!empty($event->date_to)) {
      if ($event->date_to == $event->date_from) {
        $date_to = $date_from->add(new DateInterval('PT86399S'));
      }
      else {
        $date_to = mkdate($event->date_to);
        //var_dump(array($date_from, $date_to));
      }
    }
    else
    if (!empty($event->duration)) {
      if (!($event->duration == 1 && $event->duration_unit == 'd')) {
        
        switch ($event->duration_unit) {
          case 'y':
            $date_to = $date_from->add(new DateInterval('P1Y'));
            break;
      
          case 'm':
            $date_to = $date_from->add(new DateInterval('P1M'));
            break;
      
          case 'd':
          default:
            $date_to = $date_from->add(new DateInterval('P1D'));
            break;
        }
      }
    } // duration

    if ($date_to) {
      // An interval
      $points[$date_from->format('Y-m-d')][] = (object)array(
        'type' => 'from', 
        'event' => $event,
      );
      $point_count++;
  
      $points[$date_to->format('Y-m-d')][] = (object)array(
        'type' => 'to', 
        'event' => $event, 
      );
      $point_count++;
    }
    else {
      // A single point
      $points[$date_from->format('Y-m-d')][] = (object)array(
        'type' => 'on', 
        'event' => $event,
      );
      $point_count++;
    }
    
    /*
     * Calculate anniversaries
     */
    
    if (!empty($event->anniversary)) {
      $next_year = (new DateTime('NOW'))->add(new DateInterval('P1Y'));
     
      $i = 0;
      do {
        $i++;
        $anniversary = $date_from->add(new DateInterval('P' . $i . 'Y'));
      /*
      var_dump(array("ANN",
        get_class($date_from),
        $date_from,
        $anniversary,
        $next_year->diff(new DateTime($anniversary->format('Y-m-d')))->y
      ));
      */
         $points[$anniversary->format('Y-m-d')][] = (object)array(
          'type' => 'anniversary',
          'event' => $event,
          'title' => sprintf($event->anniversary, $i),
        );
        $point_count++;
      }
      
      /*
       * Comparing DateTimeImmutable does not work, neither does DateTimeImmutable->diff
       * @see https://bugs.php.net/bug.php?id=65768
       */
      while ($next_year->diff(new DateTime($anniversary->format('Y-m-d')))->y > 0);
    }    
    
  } 
  krsort($points, SORT_REGULAR);
  
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
    'points' => $points,
    'point_count' => $point_count,
  ));
});

dispatch();
