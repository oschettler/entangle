<div class="container">
  <h3>Queries</h3>
  <table class="table table-striped">
    <thead>
    </thead>
    <tbody>
      <?php foreach (ORM::get_query_log() as $query): ?>
      <tr><td>
        <?= $query ?>
      </td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
