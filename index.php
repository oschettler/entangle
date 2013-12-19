<?php 

const USER_ID = 1;

ini_set('display_errors', TRUE);
error_reporting(-1);

date_default_timezone_set('Europe/Berlin');

session_set_cookie_params(86400 * 7);
session_start();

require_once 'vendor/autoload.php';

ORM::configure('sqlite:db/entangle.sqlite');
ORM::configure('return_result_sets', TRUE);

config('dispatch.views', './views');
config('source', 'settings.ini');

on('GET', '/', function () {

  $entangled = ORM::for_table('entangled')
    ->where_equal('user_id', USER_ID)
    ->where_equal('title', 'Start')
    ->find_one();
  
  $timelines = array();
  foreach (ORM::for_table('entangled_timeline')
    ->left_outer_join('timeline', array('entangled_timeline.timeline_id', '=', 'timeline.id'))
    ->where_equal('entangled_timeline.entangled_id', $entangled->id)
    ->find_many() as $timeline) {
      
    $timelines[$timeline->id] = $timeline;
  }
    
  $events = ORM::for_table('event')
    ->select('event.*')
    ->select('location.title', 'location_title')
    ->left_outer_join('location', array('event.location_id', '=', 'location.id'))
    ->where_in('timeline_id', array_keys($timelines))
    ->order_by_desc('date_from')
    ->order_by_asc('timeline_id')
    ->find_result_set();
  
  $points = array();
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

    $points[$date_from] = (object)array(
      'type' => 'from', 
      'event' => $event,
    );

    if ($date_to) {
      $points[$date_from]->to = $date_to;

      $points[$date_to] = (object)array(
        'type' => 'to', 
        'event' => $event, 
      );
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
        $points[$anniversary] = (object)array(
          'type' => 'anniversary',
          'event' => $event,
          'title' => sprintf($event->anniversary, $i),
        );
      }
      while ($anniversary < $next_year);
    }    
    
  } 
  krsort($points, SORT_NUMERIC);
  
  $i = 0;
  foreach ($points as $ts => $point) {
    $point->ix = $i;
    if ($point->type == 'from' && !empty($point->to)) {
      $point->to_ix = $points[$point->to]->ix;
    }
    $i++;
  }

  render('index', array(
    'site_name' => config('site.name'),
    'page_title' => $entangled->title,
    'timelines' => $timelines,
    'points' => $points,
  ));
});

dispatch();