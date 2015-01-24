<?php
use Entangle\App;

App::stack('styles', 'timelines');
App::stack('scripts', 'timelines');
//stack('scripts', '//rawgithub.com/markmalek/Fixed-Header-Table/master/jquery.fixedheadertable.min.js');

if (!empty($named_timelines)) {
  App::stack('footer', partial('footer_event', array('named_timelines' => $named_timelines)));
}
?>

<h1><?php echo strip_tags($page_title); ?></h1>

<canvas id="spans"></canvas>

<table class="events table table-striped">
  <thead>
    <tr>
      <th class="dates">Daten</th>
      <?php
      $width = 80 / count($timelines);
      foreach ($timelines as $timeline) {
        ?>
        <th style="width:<?php echo $width; ?>%"><?php echo $timeline->title; ?></th>
        <?php
      }
      
      if (!empty($named_timelines)):
        ?>
        <th class="action">
          <a class="add-event" href="#"><span class="glyphicon glyphicon-plus-sign"></span></a>
        </th>
        <?php
      endif;
      ?>
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
        <td class="dates<?php if (!empty($named_timelines) && $event->public) { echo " public"; } ?>">
          <?php //echo '[', $point->type, '-&gt;', !empty($point->to_ix) ? $point->to_ix : '', ':', strftime('%Y-%m-%d %H:%M', $ts), ']'; ?>
          <?php
          if ($point->type == 'to') {
            ?>
            <span class="date-end" id="dates-<?php echo $i+1; ?>" x-id="<?php echo $event->id; ?>"><?php echo empty($event->date_to) ? $ts : $event->date_to, '<br>(seit ', $event->date_from, ')'; ?></span>
            <?php
          }
          else {
            ?>
            <span class="date-<?php echo $point->type; ?>" id="dates-<?php echo $i+1; ?>" x-id="<?php echo $event->id; ?>"><?php echo $point->type == 'anniversary' ? $ts : $event->date_from; ?></span>
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
            <td class="event">
              <?php
              $text = '';
              if (!empty($_SESSION['user']->id) 
                && $event->user_id != $_SESSION['user']->id) {

                $text .= "{$event->user_realname}: ";
              }
              
              if ($point->type == 'to') {
                $text .= '<strong>Ende:</strong> ';
              }
              
              if (!empty($point->title)) {
                $text .= $point->title; 
              }
              else {
                $text .= $event->title; 
                if (!empty($event->location_title)) {
                  $text .= ' (' . $event->location_title . ')'; 
                }
              }
              
              if (empty($event->description)) {
                echo $text;
              }
              else {
                ?>
                <span class="with-details" data-toggle="popover" data-content="<?php echo addslashes(preg_replace("/\s*\n+\s*/", '. ', $event->description)); ?>" data-triger="hover" data-placement="bottom"><?php echo $text; ?></span>
                <?php
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
          $tl++;
        } // timeline
        
        $i++;
        
        if (!empty($named_timelines)):
          ?>
          <td class="action">
            <a href="#" class="edit edit-event" data-id="<?php echo $event->id; ?>"><span class="glyphicon glyphicon-edit"></span></a>
          </td>
          <?php
        endif;
        ?>
      </tr>
      <?php
    } // point_list per ts
  } // points
  ?>
  </tbody>
</table>
