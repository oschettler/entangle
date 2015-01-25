<?php namespace Controller;

use Granada\ORM;
use Granada\Model;

class Location {
	/**
	 * Add a location
	 */
	public function add()
	{
		$now = strftime( '%Y-%m-%d %H:%M:%S' );

		$location = ORM::for_table('location')->create();

		$fields = array( 'title' );
		foreach ( $fields as $field ) {
			if ( empty( $_POST[ $field ] ) ) {
				error( 500, 'All fields are required' );
			}
			$location->{$field} = $_POST[ $field ];
		}

		$location->longitude = empty( $_POST['longitude'] ) ? '' : $_POST['longitude'];
		$location->latitude  = empty( $_POST['latitude'] ) ? '' : $_POST['latitude'];
		$location->created   = $now;
		$location->save();

		json( array( 'success' => "Location added" ) );

	}

	/**
	 * Delete a location
	 */
	public function del()
	{
		if ( $_SESSION['user']->id != 1 ) { // only super-user
			error( 500, 'Not allowed' );
		}

		$id = params( 'id' );

		if ( empty( $id ) ) {
			error( 500, 'No location given' );
		}

		$location = ORM::for_table('location')->find_one( $id );
		if ( ! $location ) {
			error( 500, 'No such location' );
		}

		$location->delete();

		json( array( 'success' => "Location #{$id} deleted" ) );
	}

	/**
	 * Edit a location
	 */
	public function edit()
	{
		if (empty($_POST['id'])) {
			error(500, 'No location given');
		}

		$location = ORM::for_table('location')->find_one($_POST['id']);
		if (!$location) {
			error(500, 'No such location');
		}

		$now = strftime('%Y-%m-%d %H:%M:%S');

		$fields = array('title');
		foreach ($fields as $field) {
			if (empty($_POST[$field])) {
				error(500, 'All fields are required');
			}
			$location->{$field} = $_POST[$field];
		}

		$location->longitude = empty($_POST['longitude']) ? '' : $_POST['longitude'];
		$location->latitude = empty($_POST['latitude']) ? '' : $_POST['latitude'];
		$location->updated = $now;
		$location->save();

		json(array('success' => "Location changed"));
	}
}
