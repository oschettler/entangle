<div class="navbar navbar-default navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/"><?php echo $site_name; ?></a>
    </div>
    <div class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
        <li>
          <a href="http://docs.entangle.de/">Documentation</a>
        </li>
        <li>
          <a href="http://docs.entangle.de/about.html">About</a>
        </li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <?php
        if (!empty($_SESSION['user'])):
          $user = $_SESSION['user'];
          ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $user->username; ?> <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li>
                <a href="/user/edit">Edit</a>
              </li>
              <li>
                <a href="/user/logout">Logout</a>
              </li>
            </ul>
          </li>
        <?php
        else:
        ?>
          <li>
            <a href="#" data-toggle="modal" data-target="#login">Login</a>
          </li>
          <?php
        endif;
        ?>
      </ul>
    </div>
  </div>
</div>