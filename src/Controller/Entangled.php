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
}