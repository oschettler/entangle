<h2>Locations</h2>

<table class="table table-striped">
  <thead>
    <tr>
      <th>Title</th>
      <th>Longitude</th>
      <th>Latitude</th>
      <th class="action">
        <a class="add-location" href="#"><span class="glyphicon glyphicon-plus-sign"></span></a>
      </th>
    </tr>
  </thead>
  <tbody>
  <?php
  foreach ($locations as $loc):
    ?>
    <tr>
      <td class="title-val"><?php echo $loc->title; ?></td>
      <td class="longitude-val"><?php echo $loc->longitude; ?></td>
      <td class="latitude-val"><?php echo $loc->latitude; ?></td>
      <td class="action">
        <a href="#" class="edit edit-location" data-id="<?php echo $loc->id; ?>"><span class="glyphicon glyphicon-edit"></span></a>
        <a href="/user/del_location/<?php echo $loc->id; ?>" class="del del-location" data-title="Location #<?php echo $loc->id; ?>"><span class="glyphicon glyphicon-trash"></span></a>
      </td>
    </tr>
    <?php
  endforeach;
  ?>
  </tbody>
</table>

<!-- Modal: edit-location -->
<div class="modal fade" id="edit-location" tabindex="-1" role="dialog" aria-labelledby="locationLabel" aria-hidden="true">
  <form method="POST" id="location-form" action="/user/edit_location" role="form" class="modal-dialog edit-"><input id="loc-id-field" name="id" type="hidden">

    <div class="modal-content">
      <div class="modal-header">
        <?php echo partial('alert'); ?>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="locationLabel">Edit location</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label" for="loc-title">Title</label>
          <input class="form-control required" id="loc-title-field" name="title">
        </div>
        <div class="form-group">
          <label class="control-label" for="loc-longitude">Longitude</label>
          <input class="form-control" id="loc-longitude-field" name="longitude">
        </div>
        <div class="form-group">
          <label class="control-label" for="loc-latitude">Latitude</label>
          <input class="form-control" id="loc-latitude-field" name="latitude">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div><!-- /.modal-content -->
  </form><!-- /.modal-dialog -->
</div><!-- /.modal edit-location -->
