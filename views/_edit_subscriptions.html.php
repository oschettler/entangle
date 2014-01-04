<?php
if (!empty($page_title)) {
  ?><h1><?php echo $page_title; ?></h1><?php
}
else {
  ?><h2>Subscriptions</h2><?php
}

?>
<table class="subscriptions table table-striped">
  <thead>
    <tr>
      <th>ID</th>
      <th>Real name</th>
      <th>Source URL</th>
      <th class="action">
        <a class="add-subscription" href="#"><span class="glyphicon glyphicon-plus-sign"></span></a>
      </th>
    </tr>
  </thead>
  <tbody>
  <?php
  foreach ($subscriptions as $sub) {
    ?>
    <tr>
      <td class="id-val"><?php echo $sub->id; ?></td>
      <td class="realname-val"><?php echo $sub->realname; ?></td>
      <td class="source_url-val"><?php echo $sub->source_url; ?></td>
      <td class="action">
        <a href="#" class="edit edit-subscription" data-id="<?php echo $sub->id; ?>"><span class="glyphicon glyphicon-edit"></span></a>
        <a href="/user/del/<?php echo $sub->id; ?>" class="del del-subscription" data-title="Subscription #<?php echo $sub->id; ?>"><span class="glyphicon glyphicon-trash"></span></a>
      </td>
    </tr>
    <?php
  }
  ?>
  </tbody>
</table>

<!-- Modal: edit-subscription -->
<div class="modal fade" id="edit-subscription" tabindex="-1" role="dialog" aria-labelledby="subscriberLabel" aria-hidden="true">
  <form method="POST" id="subscription-form" action="/user/edit_subscription" role="form" class="modal-dialog edit-"><input id="sub-id-field" name="id" type="hidden">

    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="subscriberLabel">Edit subscriber</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label" for="sub-realname">Real name</label>
          <input class="form-control" id="sub-realname-field" name="realname">
        </div>
        <div class="form-group">
          <label class="control-label" for="sub-source_url">Source URL</label>
          <input class="form-control" id="sub-source_url-field" name="source_url">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div><!-- /.modal-content -->
  </form><!-- /.modal-dialog -->
</div><!-- /.modal edit-subscription -->
