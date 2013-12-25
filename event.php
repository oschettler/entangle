<?php

on('POST', '/add', function () {
  
  if (empty($_POST['title']) || empty($_POST['date_from'])) {
    error(500, 'Please fill in title and date_from');
  }
  
  $event = ORM::for_table('event')->create();
  $event->title = $_POST['title'];
  $event->date_from = $_POST['date_from'];
  $event->created = strftime('%Y-%m-%d %H:%M:%S');
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
      return 'OK';
    }
    else {
      error(500, 'The event could not be saved');
    }    
  }
  catch (Exception $e) {
    error(500, $e->getMessage());
  }
});
