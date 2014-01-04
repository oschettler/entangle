<h2>Timelines</h2>

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
      <th>ID</th>
      <th>Name</th>
      <th>Title</th>
      <th>Timelines</th>
      <th class="action">
        <a class="add-timeline" href="#"><span class="glyphicon glyphicon-plus-sign"></span></a>
      </th>
    </tr>
  </thead>
  <tbody>
  <?php
  foreach ($timelines as $tl):
    ?>
    <tr>
      <?php
      if ($_SESSION['user']->id == 1):
        ?>
        <td class="user_id-val" data-user_id="<?php echo $tl->user_id; ?>"><?php echo $tl->user_realname; ?></td>
        <?php
      endif;
      ?>
      <td class="id-val"><?php echo $tl->id; ?></td>
      <td class="name-val"><?php echo $tl->name; ?></td>
      <td class="title-val"><?php echo $tl->title; ?></td>
      <td class="timelines-val"><?php echo $tl->timelines; ?></td>
      <td class="action">
        <a href="#" class="edit edit-timeline" data-id="<?php echo $tl->id; ?>"><span class="glyphicon glyphicon-edit"></span></a>
        <a href="/user/del_timeline/<?php echo $tl->id; ?>" class="del del-timeline" data-title="Timeline #<?php echo $tl->id; ?>"><span class="glyphicon glyphicon-trash"></span></a>
      </td>
    </tr>
    <?php
  endforeach;
  ?>
  </tbody>
</table>

<!-- Modal: edit-timeline -->
<div class="modal fade" id="edit-timeline" tabindex="-1" role="dialog" aria-labelledby="timelineLabel" aria-hidden="true">
  <form method="POST" id="timeline-form" action="/user/edit_timeline" role="form" class="modal-dialog edit-"><input id="tl-id-field" name="id" type="hidden">

    <div class="modal-content">
      <div class="modal-header">
        <?php echo partial('alert'); ?>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="timelineLabel">Edit timeline</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label" for="tl-user">User</label>
          <select id="tl-user_id-field" name="user_id" class="form-control required">
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
          <label class="control-label" for="tl-name">Name</label>
          <input class="form-control required" id="tl-name-field" name="name">
        </div>
        <div class="form-group">
          <label class="control-label" for="tl-title">Title</label>
          <input class="form-control required" id="tl-title-field" name="title">
        </div>
        <div class="form-group">
          <label class="control-label" for="tl-timelines">Timelines</label>
          <input class="form-control" id="tl-timelines-field" name="timelines">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div><!-- /.modal-content -->
  </form><!-- /.modal-dialog -->
</div><!-- /.modal edit-timeline -->
