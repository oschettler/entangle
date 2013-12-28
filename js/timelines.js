/**
 */
jQuery(function () {
  var 
    c = document.getElementById("spans"),
    ctx = c.getContext("2d");

  var top = $('#dates-1').get(0).getBoundingClientRect();
  
  $spans = $('#spans');
  $table = $('table.events');
  
  $spans.attr('width', 60);
  $spans.attr('height', $table.height());
  $spans.css('top', $('tbody', $table).position().top);
  
  ctx.font = "16px Arial";
  ctx.lineWidth = 2;
  
  var intervals = {};
  
  $('span.date-end').each(function (i) {
    var 
      here = $(this).get(0).getBoundingClientRect(),
      id = $(this).attr('x-id'),
      there = $('span.date-from[x-id="' + id + '"]').get(0).getBoundingClientRect(),
      y_top = here.bottom - top.top,
      y_bottom = there.top - top.top + 16,
      x = 0;

    // Find free horizontal slot
    for (var y in intervals) {
      if (intervals[y].y > y_top) {
        x = intervals[y].x + 1;
      }
    }
    //ctx.fillText(i + ': x=' + x, 10 + 6 * x, y_top);

    intervals[y_top] = { y: y_bottom, x: x };

    x = 2 + 4 * x;

    ctx.moveTo(x + 2, y_top);
    ctx.lineTo(x, y_top);

    ctx.moveTo(x, y_top);

    ctx.lineTo(x, y_bottom);

    ctx.moveTo(x, y_bottom);
    ctx.lineTo(x + 2, y_bottom);

    ctx.stroke();
  });

  $('a.add-event').click(function (e) {

    var $form = $('#event-form');
    $form.attr('action', '/event/add');

    $('#event_id-field').val('');
    $('#timeline_id-field').val('');
    $('#location-field').val('');
    $('#title-field').val('');
    $('#description-field').val('');
    $('#date_from-field').val('');
    $('#date_to-field').val('');
    $('#duration-field').val('');

    $('#edit-event').modal('show');

    e.preventDefault();
  });

  $('a.edit-event').click(function (e) {
    var event_id = $(this).attr('data-id');    

    $.getJSON('/event/' + event_id, function (event) {
      var $form = $('#event-form');
      $form.attr('action', '/event/edit');

      $('#event_id-field').val(event.id);
      $('#timeline_id-field').val(event.timeline_id);
      $('#location-field').val(event.location);
      $('#title-field').val(event.title);
      $('#description-field').val(event.description);
      $('#date_from-field').val(event.date_from);
      $('#date_to-field').val(event.date_to);
      $('#duration-field').val(event.duration);
      
      if (event.duration_unit) {
        $('#unit-field').val(event.duration_unit);
        $('#unit-value').text(event.duration_unit);
      }
      $('#anniversary-field').val(event.anniversary);
      
      $('#edit-event').modal('show');
    });

    e.preventDefault();
  });

  $('a.unit').click(function (e) {
	  var unit = $(this).attr('data-unit');
    $('#unit-field').val(unit);
    $('#unit-value').text(unit);
    $(this).parents('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle');
    
    e.preventDefault();
  });
  
  $('#event-form').submit(function (e) {
    var 
      $form = $(this),
      action = $form.attr('action'),
      method = $form.attr('method');
    
    var valid = true;
    var msg = [];
    $('input,textarea', $form).each(function () {
      var 
        name = $(this).attr('name'),
        val = $(this).val();
      
      switch (name) {
        case 'title':
          if (val.match(/^\s*$/)) {
            $(this).parents('div.form-group').addClass('has-error');
            valid = false;
            msg.push(name + ': May not be empty');
          }
          break;
      
        case 'date_from':
        case 'date_to':
          if (name == 'date_to' && val == '') {
            return;
          }
          if (val.match(/^\d{4}$/)) {
            return;
          }
          if (val.match(/^\d{4}-\d{2}$/)) {
            return;
          }
          if (val.match(/^\d{4}-\d{2}-\d{2}$/)) {
            return;
          }
          $(this).parents('div.form-group').addClass('has-error');
          valid = false;
          msg.push(name + ': Give either YYYY, YYYY-mm, or YYYY-mm-dd');
          break;
        
        case 'duration':
          if (val == '' || val.match(/^\d+(\.\d*)?$/)) {
            return;
          }
          $(this).parents('div.form-group').addClass('has-error');
          valid = false;
          msg.push(name + ': Give a number');
          break;

        default:
          break;
      }
    });
    
    var $alert = $('.modal-content .alert');
    
    if (valid) {
      $.ajax({
        url: action,
        type: method,
        data: $(this).serialize(),
        dataType: 'json',
        success: function (data) {
          $.cookie('_F', JSON.stringify(data));
          location.href = '/';
        },
        error: function (xhr, textStatus, errorThrown) {
          $('.message', $alert).text(errorThrown);
          
          $alert.show();
  
          setTimeout(function () {
            $alert.slideUp('fast');
          }, 3000);
        }
      });
    }
    else {
      $('.message', $alert).html(msg.join('<br>'));
      
      $alert.show();

      setTimeout(function () {
        $alert.slideUp('fast');
      }, 3000);
    }
        
    e.preventDefault();
  });
  
  $('tbody tr').hover(function () {
    $('a.edit-event', this).show();
  }, function () {
    $('a.edit-event', this).hide();
  });
  
  $('td.event span').popover();

  //$('table.events').fixedHeaderTable();
  
});