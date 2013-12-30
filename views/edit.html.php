<?php 
stack('styles', 'edit'); 
stack('scripts', 'edit');
stack('footer', partial('footer_edit'));
?>

<h1><?php echo $page_title; ?></h1>

<!-- Nav tabs -->
<ul class="nav nav-tabs">
  <li class="active"><a href="#profile" data-toggle="tab">Profile</a></li>
  <li><a href="#subscriptions" data-toggle="tab">Subscriptions</a></li>
  <li><a href="#timelines" data-toggle="tab">Timelines</a></li>
  <li><a href="#displays" data-toggle="tab">Displays</a></li>
  <li><a href="#locations" data-toggle="tab">Locations</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
  <div class="tab-pane active" id="profile"><?php echo partial('edit_profile', array('user' => $user)); ?></div>
  <div class="tab-pane" id="subscriptions"><?php echo partial('edit_subscriptions', array('subscriptions' => $subscriptions)); ?></div>
  <div class="tab-pane" id="timelines"><?php echo partial('edit_timelines', array('timelines' => $timelines)); ?></div>
  <div class="tab-pane" id="displays"><?php echo partial('edit_displays', array('displays' => $displays)); ?></div>
  <div class="tab-pane" id="locations"><?php echo partial('edit_locations', array('locations' => $locations)); ?></div>
</div>
