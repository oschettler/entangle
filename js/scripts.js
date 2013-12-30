/**
 */

jQuery(function ($) {
  setTimeout(function () {
    $('.alert').slideUp('fast');
  }, 3000);

  $('tbody tr').hover(function () {
    $('a.edit,a.del', this).show();
  }, function () {
    $('a.edit,a.del', this).hide();
  });
  
  $('a.del').click(function (e) {
    if (confirm('Really delete ' + $(this).attr('data-title') + '?')) {
      $.post($(this).attr('href'), function (data) {
        $.cookie('_F', JSON.stringify(data));
        location.reload();
      }, 'json');
    }
    e.preventDefault();
  })
});

