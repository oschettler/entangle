<?php namespace Controller;

use Granada\ORM;
use Granada\Model;

class User
{
	public function timeline()
	{
		$user = Model::factory('User')
			->where('username', params('username'))
			->find_one();

		if (!$user) {
			error(500, 'No such user');
		}

		$events = Model::factory('Event')
			->select('event.*')
			->select('location.title', 'location_title')
			->select('location.longitude', 'location_longitude')
			->select('location.latitude', 'location_latitude')
			->select('timeline.name', 'timeline_name')
			->select('timeline.title', 'timeline_title')
			->select('user.id', 'user_id')
			->left_outer_join('location', array('event.location_id', '=', 'location.id'))
			->left_outer_join('timeline', array('event.timeline_id', '=', 'timeline.id'))
			->left_outer_join('user', array('timeline.user_id', '=', 'user.id'))
			->where('timeline.user_id', $user->id)
			->where('public', 1)
			->order_by_desc('date_from')
			->order_by_asc('timeline_id');

		$since = params('since');
		if (!empty($since)) {
			$events->where_gt('updated', $since);
		}

		if (in_array('application/json', explode(',', $_SERVER["HTTP_ACCEPT"]))) {
			json($events->find_array());
			return;
		}

		$points = points($events->find_result_set());
		if (0 == count($points->timelines)) {
			// Pointless to render anything if there are no timelines with public events
			error(500, "No public events");
		}
		else {

			App::stack('footer', partial('footer_login'));

			$timelines = Model::factory('Timeline')
				->select_many('id', 'title')
				->where_in('id', $points->timelines)
				->order_by_asc('title')
				->find_result_set();

			render('index', array(
				'page_title' => $user->realname,
				'timelines' => $timelines,
				'points' => $points->points,
				'point_count' => $points->count,
			));
		}
	}
}