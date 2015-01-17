<h2>Displays</h2>

<table class="table table-striped">
  <thead>
    <tr>
      <?php
      if ($_SESSION['user']->id == 1):
        ?>
        <th>User</th>
        <?php
      endif;
      ?>
      <th>Display</th>
      <th>Timelines</th>
      <th class="action">
        <a class="add-display" href="#"><span class="glyphicon glyphicon-plus-sign"></span></a>
      </th>
    </tr>
  </thead>
  <tbody>
  <?php
  function close_display_row($entangled_id) {
    ?>
      </ul></td>
      <td class="action">
        <a href="#" class="edit edit-display" data-id="<?php echo $entangled_id; ?>"><span class="glyphicon glyphicon-edit"></span></a>
        <a href="/user/del_display/<?php echo $entangled_id; ?>" class="del del-display" data-title="Display #<?php echo $entangled_id; ?>"><span class="glyphicon glyphicon-trash"></span></a>
      </td>
    </tr>
    <?php
  };
  
  $entangled_id = NULL;
  foreach ($displays as $i => $d) {
    if ($entangled_id != $d->entangled_id) {
      if ($i) {
        close_display_row($entangled_id);
      }
      $entangled_id = $d->entangled_id;
      ?>
      <tr>
        <?php
        if ($_SESSION['user']->id == 1):
          ?>
          <td class="user_id-val" data-user_id="<?php echo $d->user_id; ?>"><?php echo $d->user_realname; ?></td>
          <?php
        endif;
        ?>
        <td class="title-val"><?php echo $d->entangled_title; ?></td>
        <td class="timelines-val"><ul>
        <?php
    }
    ?>
    <li data-id="<?php echo $d->timeline_id; ?>"><?php echo "#{$d->timeline_id} {$d->timeline_title}"; ?></li>
    <?php
  }
  close_display_row($d->entangled_id);
  ?>
  </tbody>
</table>

<!-- Modal: edit-display -->
<div class="modal fade" id="edit-display" tabindex="-1" role="dialog" aria-labelledby="displayLabel" aria-hidden="true">
  <form method="POST" id="display-form" action="/user/edit_display" role="form" class="modal-dialog edit-"><input id="dis-id-field" name="id" type="hidden">

    <div class="modal-content">
      <div class="modal-header">
        <?php echo partial('alert'); ?>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="displayLabel">Edit display</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label" for="tl-user">User</label>
          <select id="dis-user_id-field" name="user_id" class="form-control required">
            <?php
            foreach ($users as $user):
              ?>
              <option value="<?php echo $user->id; ?>"><?php echo "{$user->realname} ({$user->username})"; ?></option>
              <?php
            endforeach;
            ?>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label" for="dis-title">Title</label>
          <input class="form-control required" id="dis-title-field" name="title">
        </div>
        <div class="form-group">
          <label class="control-label" for="dis-timelines">Timelines</label>
          <input class="form-control" id="dis-timelines-field" name="timelines">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div><!-- /.modal-content -->
  </form><!-- /.modal-dialog -->
</div><!-- /.modal edit-display -->
