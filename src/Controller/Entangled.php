<?php namespace Controller;

use Granada\Model;
use Entangle\TimeVector;


class Entangled
{
	public function homepage()
	{
		return render('homepage', array(
			'page_title' => 'Entangled lifes.',
		));
	}

	public function timelines()
	{
		$entangled = Model::factory('Entangled')
			->where_equal('user_id', $_SESSION['user']->id)
			->where_equal('title', 'Start')
			->find_one();

		$timelines = array();
		$event_timelines = array();
		foreach (Model::factory('EntangledTimeline')
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

		$events = Model::factory('Event')
			->select('event.*')
			->select('location.title', 'location_title')
			->select('user.id')
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
			$events->where('user.id', $_SESSION['user']->id);
		}

		$vector = new TimeVector($events->find_result_set(), /*future*/TRUE);
		$points = $vector->points();

		$named_timelines = Model::factory('Timeline')
			->select_many('timeline.id', 'timeline.user_id', 'timeline.title', 'timeline.timelines')
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
	}

	/**
	 * Add a display
	 */
	public function add()
	{
		$now = strftime('%Y-%m-%d %H:%M:%S');

		$display = Model::factory('Entangled')->create();

		$fields = array('user_id', 'title');
		foreach ($fields as $field) {
			if (empty($_POST[$field])) {
				error(500, 'All fields are required');
			}
			$display->{$field} = $_POST[$field];
		}

		if ($_SESSION['user']->id != 1 // only super-user
		    && $_SESSION['user']->id != $display->user_id) { // not self

			error(500, 'Not allowed');
		}

		$display->created = $now;
		$display->save();

		foreach (explode(',', $_POST['timelines']) as $tl_id) {
			$timeline = ORM::for_table('entangled_timeline')->create();
			$timeline->entangled_id = $display->id;
			$timeline->timeline_id = intval($tl_id);
			$timeline->created = $now;
			$timeline->save();
		}

		json(array('success' => "Display added"));
	}

	/**
	 * Delete a display
	 */
	public function del()
	{
		$id = params('id');

		if (empty($id)) {
			error(500, 'No display given');
		}

		$display = Model::factory('Entangled')->find_one($id);
		if (!$display) {
			error(500, 'No such display');
		}

		if ($_SESSION['user']->id != 1 // only super-user
		    && $_SESSION['user']->id != $display->user_id) { // not self

			error(500, 'Not allowed');
		}

		$timelines = Model::factory('EntangledTimeline')
			->where_equal('entangled_id', $id)
			->find_many();
		foreach ($timelines as $tl) {
			$tl->delete();
		}

		$display->delete();

		json(array('success' => "Display #{$id} deleted"));
	}

	/**
	 * Edit a display
	 */
	public function edit()
	{
		if (empty($_POST['id'])) {
			error(500, 'No display given');
		}

		$display = Model::factory('Entangled')->find_one($_POST['id']);
		if (!$display) {
			error(500, 'No such display');
		}

		$now = strftime('%Y-%m-%d %H:%M:%S');

		$fields = array('user_id', 'title');
		foreach ($fields as $field) {
			if (empty($_POST[$field])) {
				error(500, 'All fields are required');
			}
			$display->{$field} = $_POST[$field];
		}

		if ($_SESSION['user']->id != 1 // only super-user
		    && $_SESSION['user']->id != $display->user_id) { // not self

			error(500, 'Not allowed');
		}

		$display->created = $now;
		$display->save();

		$timelines = Model::factory('EntangledTimeline')
			->where_equal('entangled_id', $_POST['id'])
			->delete_many();

		foreach (explode(',', $_POST['timelines']) as $tl_id) {
			$timeline = Model::factory('EntangledTimeline')->create();
			$timeline->entangled_id = $display->id;
			$timeline->timeline_id = intval($tl_id);
			$timeline->created = $now;
			$timeline->save();
		}

		json(array('success' => "Display changed"));
	}
}