<!-- Modal -->
<div class="modal fade" id="edit-subscription" tabindex="-1" role="dialog" aria-labelledby="subscriberLabel" aria-hidden="true">
  <form method="POST" id="subscription-form" action="/user/edit" role="form" class="modal-dialog edit-"><input id="sub-id-field" name="id" type="hidden">

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
</div><!-- /.modal -->
