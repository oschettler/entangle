<h2>Displays</h2>

<table class="table table-striped">
  <thead>
    <tr><th>User</th><th>Display</th><th>Timelines</th></tr>
  </thead>
  <tbody>
  <?php
  $entangled_id = NULL;
  foreach ($displays as $i => $d) {
    if ($entangled_id != $d->entangled_id) {
      $entangled_id = $d->entangled_id;
      if ($i) {
        ?>
          </ul></td>
        </tr>
        <?php
      }
      ?>
      <tr>
        <td><?php echo $d->user_realname; ?></td>
        <td><?php echo $d->entangled_title; ?></td>
        <td><ul>
        <?php
    }
    ?>
    <li><?php echo $d->timeline_title; ?></li>
    <?php
  }
  ?>
  </tbody>
</table>
