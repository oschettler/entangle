<?php
if (empty($class)) {
  $class = 'danger';
}
if (empty($msg)) {
  $msg = '';
}
?>
<div class="alert alert-<?php echo $class; ?>">
  <a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>
  <span class="message"><?php echo $msg; ?></span>
</div>
