<!-- Modal -->
<div class="modal fade" id="login" tabindex="-1" role="dialog" aria-labelledby="loginLabel" aria-hidden="true">
  <form method="POST" action="/user/login" role="form" class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="loginLabel">Please login</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <!--label for="email">Email address</label-->
          <input type="text" class="form-control" name="username" placeholder="Username">
        </div>
        <div class="form-group">
          <!--label for="password">Password</label-->
          <input type="password" class="form-control" name="password" placeholder="Password">
        </div>
      </div>
      <div class="modal-footer">
        <div class="pull-left">
          <a href="/user/register">Register account</a>
          <!-- &middot; <a href="/user/password">Forgot password</a> -->
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
    </div><!-- /.modal-content -->
  </form><!-- /.modal-dialog -->
</div><!-- /.modal -->
