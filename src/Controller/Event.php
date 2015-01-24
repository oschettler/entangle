<?php namespace Controller;

use Granada\Model;
use Entangle\TimeVector;

class Event
{
	public function show()
	{
		if (!$_SESSION['user']) {
			error(500, 'Not logged in');
		}
		$event = Model::factory('Event')
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

		json((object)array(
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
	}
}
