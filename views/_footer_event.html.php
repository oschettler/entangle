<!-- Modal -->
<div class="modal fade" id="edit-event" tabindex="-1" role="dialog" aria-labelledby="eventLabel" aria-hidden="true">
  <form method="POST" id="event-form" action="/event/add" role="form" class="modal-dialog">
    <input id="event_id-field" name="event_id" type="hidden">
    <div class="modal-content">
      <div class="modal-header">
        <?php echo partial('alert'); ?>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="loginLabel">Add event</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 form-group">
            <label class="control-label" for="timeline_id">Timeline</label>
            <select id="timeline_id-field" name="timeline_id" class="form-control">
      			<?php
      			$own_timelines = array();
      			$other_timelines = array();
      			foreach ($named_timelines as $tl) {
      			  $tl_title = $tl->title;
      			  if ($tl->user_id == $_SESSION['user']->id) {
      			    $own_timelines[] = (object)array(
      			      'owner' => $tl->user_realname,
      			      'id' => $tl->id, 
      			      'title' => $tl->title
                );
        			}
        			else {
      			    $other_timelines[] = (object)array(
      			      'owner' => $tl->user_realname,
      			      'id' => $tl->id, 
      			      'title' => $tl->user_realname . ': ' . $tl->title
      			    );
      			  }
            }
            $owner = NULL;
            foreach (array_merge($own_timelines, $other_timelines) as $tl) {
              if ($tl->owner != $owner) {
                $owner = $tl->owner;
                if ($owner) {
                  ?></optgroup><?php
                }
                ?>
                <optgroup label="<?php echo addslashes($tl->owner); ?>">
                <?php
              }              
              ?>
              <option value="<?php echo $tl->id; ?>"><?php echo $tl->title; ?></option>
      			  <?php
      		  }
      			?>
            </select>
          </div>
          <div class="col-md-6 form-group">
            <label class="control-label" for="location">Location</label>
            <input class="form-control" id="location-field" name="location">
          </div>
        </div>
        <div class="row">
          <div class="col-md-8">
            <div class="form-group">
              <label class="control-label" for="title">Title</label>
              <input class="form-control" id="title-field" name="title">
            </div>
          </div>
          <div class="col-md-4">
            <div class="checkbox">
              <label>
                <input id="public-field" name="public" type="checkbox"> Event is public
              </label>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="control-label" for="description">Description</label>
          <textarea class="form-control" id="description-field" name="description" rows="3"></textarea>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label class="control-label" for="date_from">From</label>
              <input class="form-control" id="date_from-field" name="date_from">
            </div>
            <div class="form-group">
              <label class="control-label" for="anniversary">Anniversary</label>
              <input class="form-control" id="anniversary-field" name="anniversary">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="control-label" for="date_to">Until</label>
              <input class="form-control" id="date_to-field" name="date_to">
            </div>
            <label class="control-label" for="duration">Duration</label>
            <div class="input-group">
              <input type="text" id="duration-field" name="duration" class="form-control">
              <div class="input-group-btn">
                <input type="hidden" id="unit-field" name="duration_unit">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span id="unit-value"></span> (Unit) <span class="caret"></span></button>
                <ul class="dropdown-menu pull-right">
                  <li><a class="unit" href="#" data-unit="d">Day</a></li>
                  <li><a class="unit" href="#" data-unit="m">Month</a></li>
                  <li><a class="unit" href="#" data-unit="y">Year</a></li>
                </ul>
              </div><!-- /btn-group -->
            </div><!-- /input-group -->
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div><!-- /.modal-content -->
  </form><!-- /.modal-dialog -->
</div><!-- /.modal -->
