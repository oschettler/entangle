<?php

namespace Entangle;

class TimeVector
{
  private $timelines = array();
  private $points = array();
  private $point_count = 0;

  /**
   * Add a single point to the $points vector
   */
  private function addPoint($date, $type, $event, $title = NULL) {
    if (is_a($date, 'Entangle\DateTime')) {
      $date = $date->format('Y-m-d');
    }
    $this->points[$date][] = new TimePoint($type, $event, $title);
    $this->point_count++;
  }

  /**
   * Calculate start and end points for an event and add them to the $points vector
   */
  private function addPoints($event) {
    $date_from = new DateTime($event->date_from);
    //var_dump(array($date_from, $event->date_from, $event->date_to, $event->anniversary));

    /*
     * Calculate end point
     */

    $date_to = NULL;
    if (!empty($event->date_to)) {
      if ($event->date_to == $event->date_from) {
        $date_to = $date_from->add(new \DateInterval('PT86399S'));
      }
      else {
        $date_to = new DateTime($event->date_to);
        //var_dump(array($date_from, $date_to));
      }
    }
    else
    if (!empty($event->duration)) {
      if (!($event->duration == 1 && $event->duration_unit == 'd')) {

        switch ($event->duration_unit) {
          case 'y':
            $date_to = $date_from->add(new \DateInterval('P' . $event->duration . 'Y'));
            break;

          case 'm':
            $date_to = $date_from->add(new \DateInterval('P' . $event->duration . 'M'));
            break;

          case 'd':
          default:
            $date_to = $date_from->add(new \DateInterval('P' . $event->duration . 'D'));
            break;
        }
      }
    } // duration

    if ($date_to) {
      // An interval
      $this->addPoint($date_from, 'from', $event);

      /*
       * If precision of the start date is "one year", only add end date
       * if the duration is more than a year
       */
      $has_date_to = TRUE;
      if (empty($event->date_to) && preg_match('/^\d{4}$/', $event->date_from)) {
        /*
         * DateTimeImmutable.diff does not work
         * This is only fixed in PHP5.5 as of 2013-10
         * https://bugs.php.net/bug.php?id=65768
         */
        $diff = date_diff(
          new \DateTime($date_to->format('Y-m-d')),
          new \DateTime($date_from->format('Y-m-d'))
        );
        if ($diff->y < 1) {
          $has_date_to = FALSE;
        }
      }

      if ($has_date_to) {
        $this->addPoint($date_to, 'to', $event);
      }
    }
    else {
      // A single point
      $this->addPoint($date_from, 'on', $event);
    }
  }

  /**
   * Add points for the anniversaries of an event until $next_year
   */
  private function addAnniversaries($next_year, $event) {
    $date_from = new DateTime($event->date_from);

    $i = 0;
    do {
      $i++;
      $anniversary = $date_from->add(new \DateInterval('P' . $i . 'Y'));
    /*
    var_dump(array("ANN",
      get_class($date_from),
      $date_from,
      $anniversary,
      $next_year->diff(new DateTime($anniversary->format('Y-m-d')))->y
    ));
    */
      $this->addPoint($anniversary, 'anniversary', $event);
    }

    /*
     * Comparing DateTimeImmutable does not work, neither does DateTimeImmutable->diff
     * @see https://bugs.php.net/bug.php?id=65768
     */
    while ($next_year->diff(new DateTime($anniversary->format('Y-m-d')))->y > 0);
  }

  /**
   * Prepare events for display in a timeline
   */
  public function points() {
    /*
     * If we show anniversaries one year into the future,
     * mark TODAY with a special event
     */
    if ($this->future) {
      $today = strftime('%Y-%m-%d');
      $this->addPoint($today,
        'now',
        (object)array(
          'id' => 'now',
          'public' => FALSE,
          'timeline_id' => NULL,
          'date_from' => $today,
          'duration' => 1,
          'duration_unit' => 'd',
          'user_id' => $_SESSION['user']->id,
        ),
        '<strong>Heute</strong>'
      );
    }

    foreach ($this->events as $event) {
      $this->timelines[$event->timeline_id] = TRUE;
      $this->addPoints($event);

      /*
       * Calculate anniversaries
       */

      if (!empty($event->anniversary)) {
        if ($this->future) {
          // Show anniversaries one year into the future
          $next_year = (new DateTime('NOW'))->add(new \DateInterval('P1Y'));
        }
        else {
          // Show only past anniversaries
          $next_year = new DateTime('NOW');
        }

        $this->addAnniversaries($next_year, $event);
      }
    }
    krsort($this->points, SORT_REGULAR);
    return (object)array(
      'points' => $this->points,
      'count' => $this->point_count,
      'timelines' => array_keys($this->timelines),
    );
  }

  /**
   * Prepare events for display in a timeline
   */
  function __construct($events, $future = FALSE) {
    $this->events = $events;
    $this->future = $future;
  }
}
