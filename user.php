<?php

/**
 * For the /user prefix, only /login and /register may be used anonymously
 */
before(function () {
  $path = path();
  
  if (strpos($path, '/user') === 0 
    && !in_array($path, array('/user/login', '/user/register')) 
    && empty($_SESSION['user'])) {

    flash('error', 'Not logged in');
    redirect('/');
  }
});

/**
 * Login a user by verifying their username and hashed password
 */
on('POST', '/login', function () {
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
});

/**
 * Logout and redirect to startpage
 */
on('GET', '/logout', function () {
  unset($_SESSION['user']);
  flash('success', 'You are logged out');
  redirect('/');
});

/**
 * Get a form to register a new user
 */
on('GET', 'register', function () {
  stack('styles', 'edit');

  render('register', array(
    'page_title' => 'Register account',
  ));
});

/**
 * Get a form to edit the logged-in user
 */
on('GET', '/edit', function () {
  $user = ORM::for_table('user')
    ->find_one($_SESSION['user']->id);
    
  $subscriptions = ORM::for_table('user')
    ->where_not_null('source_url')
    ->find_result_set();

  $timelines = ORM::for_table('timeline')
    ->select('timeline.*')
    ->select('user.realname', 'user_realname')
    ->left_outer_join('user', array('timeline.user_id', '=', 'user.id'))
    ->order_by_asc('user_id', 'name')
    ->find_result_set();
    
  $displays = ORM::for_table('entangled_timeline')
    ->select('timeline.title', 'timeline_title')
    ->select('entangled.id', 'entangled_id')
    ->select('entangled.title', 'entangled_title')
    ->select('entangled.user_id', 'user_id')
    ->select('user.realname', 'user_realname')
    ->left_outer_join('timeline', array('entangled_timeline.timeline_id', '=', 'timeline.id'))
    ->left_outer_join('entangled', array('entangled_timeline.entangled_id', '=', 'entangled.id'))
    ->left_outer_join('user', array('entangled.user_id', '=', 'user.id'))
    ->order_by_asc('user.id', 'entangled.id', 'timeline.id')
    ->find_result_set();

  $locations = ORM::for_table('location')
    ->order_by_asc('title')
    ->find_result_set();
    
  render('edit', array(
    'page_title' => 'Edit',
    'user' => $user,
    'subscriptions' => $subscriptions,
    'timelines' => $timelines,
    'displays' => $displays,
    'locations' => $locations,
  ));
});

/**
 * Save a user for both acount registration and editing
 */
function save_user($user, $redirect) {

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

/**
 * Add a subscription (disguised as user)
 */
on('POST', '/add', function () {
  $user = ORM::for_table('user')->create();
  
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
  
  $user->created = $now;
  
  $user->save();
  
  json_out(array('success' => "Subscription created"));
});

/**
 * Take data from a user registration form and create a new user account
 * In addition, create the required database entries and a first event. 
 */
on('POST', '/register', function () {
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
});

/**
 * Save data from editing the logged-in user
 */
on('POST', '/edit_profile', function () {
  $user = ORM::for_table('user')
    ->find_one($_SESSION['user']->id);

  save_user($user, '/user/edit');
  
  flash('success', 'User has been saved');
  redirect('/user/edit');
});

/**
 * Replicate events from a remote user
 */
on('GET', '/update/:user_id', function () {
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
});

on('POST', '/del/:id', function () {
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

  json_out(array('success' => "{$type} #{$id} deleted"));
});