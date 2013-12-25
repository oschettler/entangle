/**
 */
jQuery(function () {
  var 
    c = document.getElementById("spans"),
    ctx = c.getContext("2d");

  var top = $('#dates-1').get(0).getBoundingClientRect();
  
  $spans = $('#spans');
  $spans.attr('width', $spans.width());
  $spans.attr('height', $spans.height());
  
  ctx.font = "16px Arial";
  ctx.lineWidth = 2;
  
  var intervals = {};
  
  $('span.date-end').each(function (i) {
    var 
      here = $(this).get(0).getBoundingClientRect(),
      id = $(this).attr('x-id'),
      there = $('span.date-from[x-id="' + id + '"]').get(0).getBoundingClientRect(),
      y_top = here.bottom - top.top,
      y_bottom = there.top - top.top,
      x = 0;

    // Find free horizontal slot
    for (var y in intervals) {
      if (intervals[y].y > y_top) {
        x = intervals[y].x + 1;
      }
    }
    //ctx.fillText(i + ': x=' + x, 10 + 6 * x, y_top);

    intervals[y_top] = { y: y_bottom, x: x };

    x = 4 + 4 * x;

    ctx.moveTo(x - 2, y_top);
    ctx.lineTo(x, y_top);

    ctx.moveTo(x, y_top);

    ctx.lineTo(x, y_bottom);

    ctx.moveTo(x, y_bottom);
    ctx.lineTo(x - 2, y_bottom);

    ctx.stroke();
  });

  $('a.unit').click(function (e) {
	  var unit = $(this).attr('data-unit');
    $('#unit-field').val(unit);
    $('#unit-value').text(unit);
    $(this).parents('.dropdown-menu').sibling('.dropdown-toggle').dropdown('toggle');
    
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
          if (val == '' || val.match(/^\d+(\.\d*)$/)) {
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
        success: function () {
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

});