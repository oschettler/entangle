<?php

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

  if (empty($_SESSION['user'])) {
    flash('error', 'Not logged in');
    redirect('/');
  }

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
    'page_title' => 'Edit user',
    'user' => $user,
    'timelines' => $timelines,
    'displays' => $displays,
    'locations' => $locations,
  ));
});