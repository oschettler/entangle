/**
 */

jQuery(function ($) {
  setTimeout(function () {
    $('.alert').slideUp('fast');
  }, 3000);


  var 
    c = document.getElementById("spans"),
    ctx = c.getContext("2d");

  ctx.lineWidth = 6;
  ctx.font = "30px Arial";
  
  var top = $('#dates-1').get(0).getBoundingClientRect();
  
  $spans = $('#spans');
  $spans.attr('width', $spans.width());
  $spans.attr('height', $spans.height());
  
  $('span.date-from').each(function (i) {
    var 
      rect = $(this).get(0).getBoundingClientRect(),
      x = 2 + 2*i;

    ctx.fillText(i, x + 10, rect.top - top.top);
    ctx.moveTo(2 + 2*i, rect.top - top.top);
    ctx.lineTo(2 + 2*i, rect.bottom - top.top);
    ctx.stroke();
    
    /*
    $(this).siblings('.coord').html(
      ' i=' + i + ', t=' + (rect.top - top.top) + ', b=' + (rect.bottom - top.top)
    );
    */
  });
});
