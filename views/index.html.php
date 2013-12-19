<div class="jumbotron">
  <h1><?php echo $page_title; ?></h1>
  <p>Entangle!</p>  
</div>

<table class="table table-striped">
  <thead>
    <tr>
      <th class="dates">Daten</th>
      <th class="spans">&nbsp;</th>
      <?php
      $width = 80 / count($timelines);
      foreach ($timelines as $timeline) {
        ?>
        <th style="width:<?php echo $width; ?>%"><?php echo $timeline->title; ?></th>
        <?php
      }
      ?>
    </tr>
  </thead>
  <tbody>
  <?php
  $i = 0;
  foreach ($points as $ts => $point) {
    $event = $point->event;
    ?>
    <tr>
      <td class="dates" id="dates-<?php echo $i+1; ?>"><?php echo '[', $point->type, '-&gt;', $point->to_ix, ':', strftime('%Y-%m-%d %H:%M', $ts), ']'; ?>
        <span class="date-from"><?php echo $event->date_from; ?></span><span class="coord"></span>
        <?php
        if (!empty($event->duration)) {
          if (!($event->duration == 1 && $event->duration_unit == 'd')) {
            ?>
            (<span class="duration"><?php echo $event->duration, $event->duration_unit; ?></span>)
            <?php
          }
        }
        else
        if (!empty($event->date_to)) {
          if ($event->date_from != $event->date_to) {
            ?>
            - <span class="date-to"><?php echo $event->date_to; ?></span>
            <?php
          }
        }
        else {
          echo "- heute";
        }
        ?>
      </td>
      <?php
      if ($i == 0) {
        echo '<td class="spans" rowspan="', count($points), '"><canvas id="spans"></canvas></td>';
      }
      
      foreach ($timelines as $timeline) {
        if ($timeline->id == $event->timeline_id) {
          ?>
          <td class="event">
            <?php
            if (!empty($point->title)) {
              echo $point->title; 
            }
            else {
              echo $event->title; 
              if (!empty($event->location_title)) {
                echo ' (', $event->location_title, ')'; 
              }
            }
            ?>
          </td>
        <?php
        }
        else {
          ?>
          <td class="event empty">&nbsp;</td>
          <?php
        }
      } // timeline
      
      $i++;
      ?>
    </tr>
    <?php
  } // event
  ?>
  </tbody>
</table>
