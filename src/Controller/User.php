<?php namespace Controller;

use Granada\ORM;
use Granada\Model;

use Entangle\TimeVector;

class User
{
	/**
	 * For the /user prefix, only /login and /register may be used anonymously
	 */
	public function before($method, $path)
	{
		if (strpos($path, '/user') === 0
		    && !in_array($path, array('/user/login', '/user/register'))
		    && empty($_SESSION['user'])) {

			flash('error', 'Not logged in');
			redirect('/');
		}
	}

	public function timeline()
	{
		$user = ORM::for_table('user')
			->where('username', params('username'))
			->find_one();

		if (!$user) {
			error(500, 'No such user');
		}

		$events = ORM::for_table('event')
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

		$vector = new TimeVector($events->find_result_set(), /*future*/TRUE);
		$points = $vector->points();

		if (0 == count($points->timelines)) {
			// Pointless to render anything if there are no timelines with public events
			error(500, "No public events");
		}
		else {

			App::stack('footer', partial('footer_login'));

			$timelines = ORM::for_table('timeline')
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

	/**
	 * Login a user by verifying their username and hashed password
	 */
	public function login()
	{

		if (empty($_POST['username']) || empty($_POST['password'])) {
			flash('error', 'Please fill in username and password');
			redirect('/');
		}

		$user = ORM::for_table('user')
		             ->where_equal('username', $_POST['username'])
		             ->where_equal('password', md5($_POST['password']))
		             ->find_one();

		if ($user) {
			flash('success', 'Welcome back');
			// Make new object to avoid "incomplete object" error
			$_SESSION['user'] = (object)array(
				'username' => $user->username,
				'id' => $user->id,
			);
		}
		else {
			flash('error', 'No such user' . md5($_POST['password']));
		}

		redirect('/');
	}


	/**
	 * Logout and redirect to startpage
	 */
	public function logout()
	{
		unset($_SESSION['user']);
		flash('success', 'You are logged out');
		redirect('/');
	}

	/**
	 * Save a user for both acount registration and editing
	 */
	private function saveUser($user, $redirect)
	{
		// All fields required
		$fields = array('username', 'email', 'realname');
		if (empty($_SESSION['user']->id)) {
			// New users need to set their password
			$fields[] = 'password';
		}
		foreach ($fields as $field) {
			if (empty($_POST[$field])) {
				flash('error', 'All fields are required');
				redirect($redirect);
			}
		}

		// username, email must be unique
		foreach (array('username', 'email') as $field) {
			$other = ORM::for_table('user')
			            ->select_expr('COUNT(*)', 'count')
			            ->where('username', $_POST[$field]);

			if (!empty($_SESSION['user']->id)) {
				$other->where_not_equal('id', $_SESSION['user']->id);
			}

			$other->find_one();
			if ($other->count) {
				flash('error', ucfirst($field) . ' "' . addslashes($_POST[$field]) . '" already taken');
				redirect($redirect);
			}
		}

		// email must be an email address
		if (!preg_match('/^\w\S*@\w+\.\S*\w+$/', $_POST['email'])) {
			flash('error', 'Invalid email address');
			redirect($redirect);
		}

		$user->username = $_POST['username'];
		$user->email = $_POST['email'];

		if (!empty($_POST['password'])) {
			$user->password = md5($_POST['password']);
		}

		$user->realname = $_POST['realname'];

		if ($user->id) {
			$user->updated = strftime('%Y-%m-%d %H:%M:%S');
		}
		else {
			$user->created = strftime('%Y-%m-%d %H:%M:%S');
		}
		$user->save();
		return $user->id;
	}

	private function saveSubscription($user)
	{
		// All fields required
		$fields = array('source_url', 'realname');
		foreach ($fields as $field) {
			if (empty($_POST[$field])) {
				error(500, 'All fields are required');
			}
		}

		$now = strftime('%Y-%m-%d %H:%M:%S');

		if (!preg_match('#^https?://#i', $_POST['source_url'])) {
			$user->source_url = 'http://' . $_POST['source_url'];
		}
		else {
			$user->source_url = $_POST['source_url'];
		}

		$url = parse_url($user->source_url);

		if (empty($url['path']) || strlen($url['path']) == 1) {
			$path = time();
		}
		else {
			$path = substr($url['path'], 1);
		}

		$user->username = $path . '@' . $url['host'];
		$user->realname = $_POST['realname'];

		if ($user->id) {
			$user->updated = $now;
		}
		else {
			$user->created = $now;
		}

		$user->save();
		return $user->id;
	}

	/**
	 * Edit a subscription (disguised as user)
	 */
	public function editSubscription()
	{
		if (empty($_POST['id'])) {
			error(500, 'No id given');
		}

		$user = ORM::for_table('user')
		           ->find_one($_POST['id']);

		if (!$user) {
			error(500, 'No such subscription');
		}

		save_subscription($user);

		json(array('success' => "Subscription changed"));
	}

	/**
	 * Add a subscription (disguised as user)
	 */
	public function addSubscription()
	{
		$user = ORM::for_table('user')->create();

		$this->saveSubscription($user);

		json(array('success' => "Subscription created"));
	}

	/**
	 * Save data from editing the logged-in user
	 */
	public function editProfile()
	{
		$user = ORM::for_table('user')
		           ->find_one($_SESSION['user']->id);

		save_user($user, '/user/edit');

		flash('success', 'User has been saved');
		redirect('/user/edit');
	}

	/**
	 * Get a form to register a new user
	 */
	public function registerForm()
	{
		App::stack('styles', 'edit');

		render('register', array(
			'page_title' => 'Register account',
		));
	}

	/**
	 * Take data from a user registration form and create a new user account
	 * In addition, create the required database entries and a first event.
	 */
	public function register()
	{
		$user = ORM::for_table('user')->create();

		$user_id = save_user($user, '/user/register');

		$now = strftime('%Y-%m-%d %H:%M:%S');

		$timeline = ORM::for_table('timeline')->create(array(
			'user_id' => $user_id,
			'name' => 'life',
			'title' => 'Leben',
			'created' => $now,
		));
		$timeline->save();

		$event = ORM::for_table('event')->create(array(
			'timeline_id' => $timeline->id,
			'title' => 'Nutzerkonto angelegt',
			'date_from' => strftime('%Y-%m-%d'),
			'duration' => 1,
			'duration_unit' => 'd',
			'created' => $now,
			'updated' => $now,
		));
		$event->save();

		$entangled = ORM::for_table('entangled')->create(array(
			'user_id' => $user_id,
			'title' => 'Start',
			'created' => $now
		));
		$entangled->save();

		$entangled_timeline = ORM::for_table('entangled_timeline')->create(array(
			'entangled_id' => $entangled->id,
			'timeline_id' => $timeline->id,
			'created' => $now,
		));
		$entangled_timeline->save();

		flash('success', 'User has been created. Please login');
		redirect('/');
	}

	/**
	 * Get a form to edit the logged-in user
	 */
	public function edit()
	{
		$user = ORM::for_table('user')
			->find_one($_SESSION['user']->id);

		$users = ORM::for_table('user');
		if ($_SESSION['user']->id != 1) {
			$users->where_equal('id', $_SESSION['user']->id);
		}

		$subscriptions = ORM::for_table('user')
			->where_not_null('source_url')
			->find_result_set();

		$timelines = ORM::for_table('timeline')
			->select('timeline.*')
			->select('user.realname', 'user_realname')
			->left_outer_join('user', array('timeline.user_id', '=', 'user.id'))
			->order_by_asc('user_id', 'name');

		if ($_SESSION['user']->id != 1) {
			// not super-user
			$timelines->where_equal('user_id', $_SESSION['user']->id);
		}

		$displays = ORM::for_table('entangled_timeline')
			->select('timeline.id', 'timeline_id')
			->select('timeline.title', 'timeline_title')
			->select('entangled.id', 'entangled_id')
			->select('entangled.title', 'entangled_title')
			->select('entangled.user_id')
			->select('user.realname', 'user_realname')
			->left_outer_join('timeline', array('entangled_timeline.timeline_id', '=', 'timeline.id'))
			->left_outer_join('entangled', array('entangled_timeline.entangled_id', '=', 'entangled.id'))
			->left_outer_join('user', array('entangled.user_id', '=', 'user.id'))
			->order_by_asc('user.id')
			->order_by_asc('entangled.id')
			->order_by_asc('timeline.id');

		if ($_SESSION['user']->id != 1) {
			// not super-user
			$displays->where_equal('entangled.user_id', $_SESSION['user']->id);
		}

		$locations = ORM::for_table('location')
			->order_by_asc('title')
			->find_result_set();

		render('edit', array(
			'page_title' => 'Edit',
			'user' => $user,
			'users' => $users->find_result_set(),
			'subscriptions' => $subscriptions,
			'timelines' => $timelines->find_result_set(),
			'displays' => $displays->find_result_set(),
			'locations' => $locations,
		));
	}

	/**
	 * Replicate events from a remote user
	 */
	public function update()
	{
		require_once 'app/api.php';

		$lf = empty($_SERVER['DOCUMENT_ROOT']) ? "\n" : '<br>';

		$user_id = params('user_id');

		$user = ORM::for_table('user')->find_one($user_id);
		if (!$user || empty($user->source_url)) {
			error(500, 'No such user');
		}

		$api = new API;
		$events = $api->call($user->source_url);

		$now = strftime('%Y-%m-%d %H:%M:%S');

		foreach ($events as $remote_event) {

			$timeline = ORM::for_table('timeline')
			               ->where('name', $remote_event->timeline_name)
			               ->where('user_id', $user->id)
			               ->find_one();

			if (!$timeline) {
				$timeline = ORM::for_table('timeline')->create(array(
					'user_id' => $user_id,
					'name' => $remote_event->timeline_name,
					'title' => $remote_event->timeline_title,
					'created' => $now,
				));
				$timeline->save();
				echo "New timeline #{$timeline->id} \"{$timeline->title}\"{$lf}";
			}

			if (!empty($remote_event->location_title)) {
				$location = ORM::for_table('location')
				               ->where('title', $remote_event->location_title)
				               ->find_one();

				if (!$location) {
					$location = ORM::for_table('location')->create(array(
						'title' => $remote_event->location_title,
						'longitude' => $remote_event->location_longitude,
						'latitude' => $remote_event->location_latitude,
						'created' => $now,
					));
					$location->save();
					echo "New location #{$location->id} \"{$location->title}\"{$lf}";
				}

				$location_id = $location->id;
			}
			else {
				$location_id = NULL;
			}

			$event = ORM::for_table('event')
			            ->where('source_id', $remote_event->id)
			            ->where('timeline_id', $timeline->id)
			            ->find_one();

			if ($event) {
				echo "Updating existing event #{$event->id} \"{$event->title}\"{$lf}";
			}
			else {
				$event = ORM::for_table('event')->create();
				echo "Created new event{$lf}";
			}

			// Detect a conflict
			if ($event->updated > $event->replicated) {
				echo "Event #{$event->id} \"{$event->title}\" locally edited - not replicated{$lf}";
				continue;
			}

			$event->set(array(
				'source_id' => $remote_event->id,
				'replicated' => $now,
				'public' => 0, // Remote events are always non-public

				'timeline_id' => $timeline->id,
				'location_id' => $location_id,
				'title' => $remote_event->title,
				'description' => $remote_event->description,
				'date_from' => $remote_event->date_from,
				'date_to' => $remote_event->date_to,
				'duration' => $remote_event->duration,
				'duration_unit' => $remote_event->duration_unit,
				'created' => $remote_event->created,
				'updated' => $remote_event->updated,
			));
			$event->save();

			echo "Saved #{$event->id}{$lf}";
		}
	}

	public function del()
	{
		$id = params('id');

		if (
			!$_SESSION['user'] // only logged-in
			|| $_SESSION['user']->id != 1 // only super-user
			|| $_SESSION['user']->id == $id) { // not self

			error(500, 'Not allowed');
		}

		$user = ORM::for_table('user')->find_one($id);

		$type = empty($user->source_url) ? 'User' : 'Subscription';

		$user->delete();

		json(array('success' => "{$type} #{$id} deleted"));
	}
}
