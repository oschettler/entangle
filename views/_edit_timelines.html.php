<h2>Timelines</h2>

<table class="table table-striped">
  <thead>
    <tr><th>User</th><th>Name</th><th>Title</th><th>Timelines</th></tr>
  </thead>
  <tbody>
  <?php
  foreach ($timelines as $tl):
    ?>
    <tr>
      <td><?php echo $tl->user_realname; ?></td>
      <td><?php echo $tl->name; ?></td>
      <td><?php echo $tl->title; ?></td>
      <td><?php echo $tl->timelines; ?></td>
    </tr>
    <?php
  endforeach;
  ?>
  </tbody>
</table>
