<!-- Modal -->
<div class="modal fade" id="add-event" tabindex="-1" role="dialog" aria-labelledby="eventLabel" aria-hidden="true">
  <form method="POST" action="/event/add" role="form" class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="loginLabel">Add event</h4>
      </div>
      <div class="modal-body">

        <div class="row">
          <div class="col-md-6 form-group">
            <label for="timeline_id">Timeline</label>
            <select name="timeline_id" class="form-control">
              <option>1</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
              <option>5</option>
            </select>
          </div>
          <div class="col-md-6 form-group">
            <label for="location">Location</label>
            <input class="form-control" name="location">
          </div>
        </div>
        <div class="form-group">
          <label for="title">Title</label>
          <input class="form-control" name="title">
        </div>
        <div class="form-group">
          <label for="description">Description</label>
          <textarea class="form-control" name="description" rows="3"></textarea>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="date_from">From</label>
              <input class="form-control" name="date_from">
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" name="anniversary"> Anniversary
              </label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="date_to">Until</label>
              <input class="form-control" name="date_to">
            </div>
            <label for="duration">Duration</label>
            <div class="input-group">
              <input type="text" name="duration" class="form-control">
              <div class="input-group-btn">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Unit <span class="caret"></span></button>
                <ul class="dropdown-menu pull-right">
                  <li><a href="#">Day</a></li>
                  <li><a href="#">Month</a></li>
                  <li><a href="#">Year</a></li>
                </ul>
              </div><!-- /btn-group -->
            </div><!-- /input-group -->
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div><!-- /.modal-content -->
  </form><!-- /.modal-dialog -->
</div><!-- /.modal -->
