<?php 
stack('styles', 'timelines'); 
stack('scripts', 'timelines');
//stack('scripts', '//rawgithub.com/markmalek/Fixed-Header-Table/master/jquery.fixedheadertable.min.js');
stack('scripts', '//rawgithub.com/carhartl/jquery-cookie/master/jquery.cookie.js');
stack('footer', partial('event', array('named_timelines' => $named_timelines)));
?>

<canvas id="spans"></canvas>

<table class="events table table-striped">
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
      <th class="action">
        <a class="add-event" href="#"><span class="glyphicon glyphicon-plus-sign"></span></a>
      </th>
    </tr>
  </thead>
  <tbody>
  <?php
  $i = 0;
  foreach ($points as $ts => $point_list) {
    foreach ($point_list as $point) {
      $event = $point->event;
      ?>
      <tr>
        <td class="dates">
          <?php //echo '[', $point->type, '-&gt;', !empty($point->to_ix) ? $point->to_ix : '', ':', strftime('%Y-%m-%d %H:%M', $ts), ']'; ?>
          <?php
          if ($point->type == 'to') {
            ?>
            <span class="date-end" id="dates-<?php echo $i+1; ?>" x-id="<?php echo $event->id; ?>"><?php echo strftime('%Y-%m-%d', $ts), '<br>(seit ', $event->date_from, ')'; ?></span>
            <?php
          }
          else {
            ?>
            <span class="date-<?php echo $point->type; ?>" id="dates-<?php echo $i+1; ?>" x-id="<?php echo $event->id; ?>"><?php echo strftime('%Y-%m-%d', $ts); ?></span>
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
                <br>- <span class="date-to"><?php echo $event->date_to; ?></span>
                <?php
              }
            }
            else {
              echo "<br>- heute";
            }
          }
          ?>
        </td>
        <?php        
        $tl = 0;
        foreach ($timelines as $timeline) {
          $event_timelines = 0 == count($timeline->timelines) ? array($timeline->id) : $timeline->timelines; 
          
          if ($tl == 0 && $event->timeline_id == NULL || in_array($event->timeline_id, $event_timelines)) {
            ?>
            <td class="event"><span data-toggle="popover" data-content="<?php echo !empty($event->description) ? addslashes($event->description) : ''; ?>" data-triger="hover" data-placement="bottom">
              <?php
              if ($event->user_id != $_SESSION['user']->id) {
                echo "{$event->user_realname}: ";
              }
              
              if ($point->type == 'to') {
                echo '<strong>Ende:</strong> ';
              }
              
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
            </span></td>
          <?php
          }
          else {
            ?>
            <td class="event empty">&nbsp;</td>
            <?php
          }
          $tl++;
        } // timeline
        
        $i++;
        ?>
        <td class="action">
          <a href="#" class="edit-event" data-id="<?php echo $event->id; ?>"><span class="glyphicon glyphicon-pencil"></span></a>
        </td>
      </tr>
      <?php
    } // point_list per ts
  } // points
  ?>
  </tbody>
</table>
