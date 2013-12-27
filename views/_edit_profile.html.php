<?php
if (!empty($page_title)) {
  ?><h1><?php echo $page_title; ?></h1><?php
}
else {
  ?><h2>Profile</h2><?php
}
?>
<form method="POST" class="form" autocomplete="off" action="<?php echo empty($action) ? '/user/edit_profile' : $action; ?>">
  <div class="form-group">
    <label class="control-label" for="username">Username</label>
    <input class="form-control" id="username-field" name="username" value="<?php echo addslashes($user->username); ?>">
  </div>
  <div class="form-group">
    <label class="control-label" for="location">Password</label>
    <input class="form-control" type="password" id="password-field" name="password">
  </div>
  <div class="form-group">
    <label class="control-label" for="email">E-Mail</label>
    <input class="form-control" id="email-field" name="email" value="<?php echo addslashes($user->email); ?>">
  </div>
  <div class="form-group">
    <label class="control-label" for="realname">Real name</label>
    <input class="form-control" id="realname-field" name="realname" value="<?php echo addslashes($user->realname); ?>">
  </div>
  <button type="submit" class="btn btn-primary">Save</button>
</form>
