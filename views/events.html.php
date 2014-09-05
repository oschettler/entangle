<?php
if (count($events) == 1): 
  ?>
	<div class="jumbotron">
		<h1>Content, Commerce, Internet&nbsp;of&nbsp;Things</h1>
		<p class="lead">Agil. Reaktiv. Verbunden.</p>
		<a class="btn btn-large btn-success" href="#">Kontakt!</a>
	</div>

  <?php 
endif;
?>

<div class="row-fluid events">
  <?php
  foreach ($columns as $i => $column):
    ?>
  	<div class="span<?php echo $column_width; ?>">
		  <?php
		  if ($i == count($columns) - 1):
        ?>
        <div class="events topics">
          <h4>Themen</h4>
          <p>
            <?php
            foreach ($tags as $tag):
              ?>
              <a class="label" href="/event/tag/<?php echo $tag->slug; ?>"><?php echo $tag->name; ?> (<?php echo $tag->num_times; ?>)
              </a>
              <?php
            endforeach;
          ?>            
          </p>
        </div>
        <?php
      endif;
      ?>
  		<?php
  		foreach ($column as $event):
  		  ?>
        <div class="event">
          <h4>
            <a href="<?php echo '/event/', $event->id; ?>">
              <?php echo $event->title; ?>
            </a>
          </h4>
          <p class="catchline"><?php echo $event->catchline; ?></p>
          <a href="<?php echo '/event/' . $event->id; ?>">
            <?php echo image($event->image, 'medium', 'event-detail'); ?>
          </a>
          <p><?php echo truncatewords($event->about, 40); ?></p>
        </div>
        <?php
      endforeach;
    ?>
		</div>
    <?php
  endforeach;
  ?>
</div>

<?php echo partial('_paginate'); ?>
