<?php namespace Controller;

use Granada\ORM;
use Granada\Model;

class Timeline
{
	/**
	 * Add a timeline
	 */
	public function add()
	{
		$now = strftime('%Y-%m-%d %H:%M:%S');

		$timeline = ORM::for_table('timeline')->create();

		$fields = array('user_id', 'name', 'title');
		foreach ($fields as $field) {
			if (empty($_POST[$field])) {
				error(500, 'All fields are required');
			}
			$timeline->{$field} = $_POST[$field];
		}

		if ($_SESSION['user']->id != 1 // only super-user
		    && $_SESSION['user']->id != $timeline->user_id) { // not self

			error(500, 'Not allowed');
		}

		$timeline->timelines = empty($_POST['timelines']) ? '' : $_POST['timelines'];
		$timeline->created = $now;
		$timeline->save();

		json(array('success' => "Timeline added"));
	}


	/**
	 * Delete a timeline
	 */
	public function del()
	{
		$id = params('id');

		if (empty($id)) {
			error(500, 'No timeline given');
		}

		$timeline = ORM::for_table('timeline')->find_one($id);
		if (!$timeline) {
			error(500, 'No such timeline');
		}

		if ($_SESSION['user']->id != 1 // only super-user
		    && $_SESSION['user']->id != $timeline->user_id) { // not self

			error(500, 'Not allowed');
		}

		$timeline->delete();

		json(array('success' => "Timeline #{$id} deleted"));
	}


	/**
	 * Edit a timeline
	 */
	public function edit()
	{
		if (empty($_POST['id'])) {
			error(500, 'No timeline given');
		}

		$timeline = ORM::for_table('timeline')->find_one($_POST['id']);
		if (!$timeline) {
			error(500, 'No such timeline');
		}

		$now = strftime('%Y-%m-%d %H:%M:%S');

		$fields = array('name', 'title');
		foreach ($fields as $field) {
			if (empty($_POST[$field])) {
				error(500, 'All fields are required');
			}
			$timeline->{$field} = $_POST[$field];
		}

		if ($_SESSION['user']->id != 1 // only super-user
		    && $_SESSION['user']->id != $timeline->user_id) { // not self

			error(500, 'Not allowed');
		}

		$timeline->timelines = empty($_POST['timelines']) ? '' : $_POST['timelines'];
		$timeline->updated = $now;
		$timeline->save();

		json(array('success' => "Timeline changed"));
	}
}