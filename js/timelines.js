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
});