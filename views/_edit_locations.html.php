<h2>Locations</h2>

<table class="table table-striped">
  <thead>
    <tr><th>Title</th><th>Longitude</th><th>Latitude</th></tr>
  </thead>
  <tbody>
  <?php
  foreach ($locations as $loc):
    ?>
    <tr>
      <td><?php echo $loc->title; ?></td>
      <td><?php echo $loc->longitude; ?></td>
      <td><?php echo $loc->latitude; ?></td>
    </tr>
    <?php
  endforeach;
  ?>
  </tbody>
</table>
