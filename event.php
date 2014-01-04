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

/**
 * Save event data for editing an existing event or creating a new one
 */
function save_event($event) {
  if (!$_SESSION['user']) {
    error(500, 'Not logged in');
  }
  if (empty($_POST['title']) || empty($_POST['date_from'])) {
    error(500, 'Please fill in title and date_from');
  }
  
  $event->title = $_POST['title'];
  
  $event->public = !empty($_POST['public']) && $_POST['public'] ? 1 : 0;
  
  $event->description = $_POST['description'];
  $event->date_from = $_POST['date_from'];

  $now = strftime('%Y-%m-%d %H:%M:%S');
  $event->updated = $now;
  if (!$event->id) {
    $event->created = $now;
  }
  
  if (empty($_POST['timeline_id'])) {
    error(500, 'No timeline given');
  }

  /*
   * Make sure the logged-in user either has ID=1 or the timeline belongs to her
   */
  if ($_SESSION['user']->id != 1) {
    $timeline = ORM::for_table('timeline')->find_one($_POST['timeline_id']);
    
    if (!$timeline) {
      error(500, 'Not such timeline');
    }
    
    if ($timeline->user_id != $_SESSION['user']->id) {
      error(500, 'Not permitted to edit this timeline');
    }
  }
  
  $event->timeline_id = $_POST['timeline_id'];

  if (!empty($_POST['location'])) {
  	$location = ORM::for_table('location')
	  	->where_like('title', $_POST['location'] . '%')
      ->find_one();
  	if ($location) {
  	  $event->location_id = $location->id;
  	}
  	else {
  	  $location = ORM::for_table('location')->create();
  	  $location->title = $_POST['location'];
  	  $location->created = strftime('%Y-%m-$d %H:%M:%S');
  	  $location->save();
  	  $event->location_id = $location->id;
  	}
  }

  if (!empty($_POST['date_to'])) {
	  $event->date_to = $_POST['date_to'];
  }

  if (!empty($_POST['duration'])) {
	  $event->duration = $_POST['duration'];
  }

  if (!empty($_POST['duration_unit'])) {
	  $event->duration_unit = $_POST['duration_unit'];
  }

  try {
    if ($event->save()) {
      echo json_encode(array('success' => 'Saved event #' . $event->id . ' "' 
        . addslashes($event->title) . '"'));
      return;
    }
    else {
      error(500, 'The event could not be saved');
    }    
  }
  catch (Exception $e) {
    error(500, $e->getMessage());
  }
}

/**
 * Take event data and create a new event
 */
on('POST', '/add', function () {
  save_event(ORM::for_table('event')->create());  
});

/**
 * Take event data and edit an existing event
 */
on('POST', '/edit', function () {
  if (empty($_POST['event_id'])) {
    error(500, 'No event id');
  }
  $event = ORM::for_table('event')
    ->select('event.*')
    ->select('timeline.user_id', 'user_id')
    ->left_outer_join('timeline', array('event.timeline_id', '=', 'timeline.id'))
    ->find_one($_POST['event_id']);

  if (!$event) {
    error(500, 'No such event');
  }
  
  /*
   * Make sure the logged-in user either has ID=1 or the event belongs to her
   */
  if ($_SESSION['user']->id != 1) {
    if ($event->user_id != $_SESSION['user']->id) {
      error(500, 'Not permitted to edit this event');
    }
  }

  save_event($event);
});

on('GET', '/:id', function () {
  if (!$_SESSION['user']) {
    error(500, 'Not logged in');
  }
  $event = ORM::for_table('event')
    ->where('event.id', params('id'))
    ->select('event.*')
    ->select('location.title', 'location_title')
    ->select('timeline.user_id', 'user_id')
    ->left_outer_join('location', array('event.location_id', '=', 'location.id'))
    ->left_outer_join('timeline', array('event.timeline_id', '=', 'timeline.id'))
    ->find_one();
  
  if (!$event) {
    error(500, 'No such event');
  }
  
  /*
   * Make sure the logged-in user either has ID=1 or the event belongs to her
   */
  if ($_SESSION['user']->id != 1) {
    if ($event->user_id != $_SESSION['user']->id) {
      error(500, 'Not permitted to view this event');
    }
  }
    
  json_out((object)array(
    'id' => $event->id,
    'timeline_id' => $event->timeline_id,
    'location' => $event->location_title,
    'title' => $event->title,
    'public' => $event->public === "1",
    'description' => $event->description,
    'date_from' => $event->date_from,
    'date_to' => $event->date_to,
    'duration' => $event->duration,
    'duration_unit' => $event->duration_unit,
    'anniversary' => $event->anniversary,
  ));
});
