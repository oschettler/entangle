<?php
if (!empty($page_title)) {
  ?><h1><?php echo $page_title; ?></h1><?php
}
else {
  ?><h2>Subscriptions</h2><?php
}

?>
<table class="subscriptions table table-striped">
  <thead>
    <tr>
      <th>ID</th>
      <th>Real name</th>
      <th>Source URL</th>
      <th class="action">
        <a class="add-subscription" href="#"><span class="glyphicon glyphicon-plus-sign"></span></a>
      </th>
    </tr>
  </thead>
  <tbody>
  <?php
  foreach ($subscriptions as $sub) {
    ?>
    <tr>
      <td class="id-val"><?php echo $sub->id; ?></td>
      <td class="realname-val"><?php echo $sub->realname; ?></td>
      <td class="source_url-val"><?php echo $sub->source_url; ?></td>
      <td class="action">
        <a href="#" class="edit edit-subscription" data-id="<?php echo $sub->id; ?>"><span class="glyphicon glyphicon-edit"></span></a>
        <a href="/user/del/<?php echo $sub->id; ?>" class="del del-subscription" data-title="Subscription #<?php echo $sub->id; ?>"><span class="glyphicon glyphicon-trash"></span></a>
      </td>

    </tr>
    <?php
  }
  ?>
  </tbody>
</table>
