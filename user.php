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
