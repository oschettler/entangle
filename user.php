<?php

before(function () {
  if (path() != '/user/login' && empty($_SESSION['user'])) {
    flash('error', 'Not logged in');
    redirect('/');
  }
});

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

on('GET', '/logout', function () {
  unset($_SESSION['user']);
  flash('success', 'You are logged out');
  redirect('/');
});

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

on('POST', '/edit_profile', function () {
  $user = ORM::for_table('user')
    ->find_one($_SESSION['user']->id);

  // All fields required
  foreach (array('username', 'email', 'password', 'realname') as $field) {
    if (empty($field)) {
      flash('error', 'All fields are required');
      redirect('/user/edit');
    }
  }

  // username, email must be unique
  foreach (array('username', 'email') as $field) {
    $other = ORM::for_table('user')
      ->select_expr('COUNT(*)', 'count')
      ->where('username', $_POST[$field])
      ->where_not_equal('id', $_SESSION['user']->id)
      ->find_one();
    if ($other->count) {
      flash('error', ucfirst($field) . ' "' . addslashes($_POST[$field]) . '" already taken');
      redirect('/user/edit');
    }
  }
  
  $user->username = $_POST['username'];
  $user->email = $_POST['email'];

  $user->password = md5($_POST['password']);

  $user->realname = $_POST['realname'];
  $user->updated = strftime('%Y-%m-%d %H:%M:%S');
  $user->save();
  flash('success', 'User has been saved');
  redirect('/user/edit');
});
