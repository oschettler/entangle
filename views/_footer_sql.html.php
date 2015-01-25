<?php
use Granada\ORM;
?>
<div class="container">
  <h3>Queries</h3>
  <table class="table table-striped">
    <thead>
    </thead>
    <tbody>
      <?php
      $logging = ORM::get_config('logging');
      $queries = ORM::get_query_log();
      foreach ($queries as $query):
        ?>
        <tr><td>
          <?= $query ?>
        </td></tr>
      <?php
      endforeach;
      ?>
    </tbody>
  </table>
</div>
