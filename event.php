<?php

function save_event($event) {
  if (empty($_POST['title']) || empty($_POST['date_from'])) {
    error(500, 'Please fill in title and date_from');
  }
  
  $event->title = $_POST['title'];
  $event->description = $_POST['description'];
  $event->date_from = $_POST['date_from'];
  if ($event->id) {
    $event->updated = strftime('%Y-%m-%d %H:%M:%S');
  }
  else {
    $event->created = strftime('%Y-%m-%d %H:%M:%S');
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

on('POST', '/add', function () {
  save_event(ORM::for_table('event')->create());  
});

on('POST', '/edit', function () {
  if (empty($_POST['event_id'])) {
    error(500, 'No event id');
  }
  $event = ORM::for_table('event')
    ->find_one($_POST['event_id']);
  if (!$event) {
    error(500, 'No such event');
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
    ->left_outer_join('location', array('event.location_id', '=', 'location.id'))
    ->find_one();
    
  json_out((object)array(
    'id' => $event->id,
    'timeline_id' => $event->timeline_id,
    'location' => $event->location_title,
    'title' => $event->title,
    'description' => $event->description,
    'date_from' => $event->date_from,
    'date_to' => $event->date_to,
    'duration' => $event->duration,
    'duration_unit' => $event->duration_unit,
    'anniversary' => $event->anniversary,
  ));
});
