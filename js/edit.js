/**
 */
jQuery(function () {
  $('a.add-subscription').click(function (e) {

    var $form = $('#subscription-form');
    $form.attr('action', '/user/add');
    $('h4.modal-title', $form).text('Add subscription');

    $('#sub-id-field').val('');
    $('#sub-source_url-field').val('');
    $('#sub-realname-field').val('');

    $('#edit-subscription').modal('show');

    e.preventDefault();
  });

  $('a.edit-subscription').click(function (e) {
    var user_id = $(this).attr('data-id');    

    var $form = $('#subscription-form');
    $form.attr('action', '/user/edit');
    $('h4.modal-title', $form).text('Edit subscription');

    var
      $parent = $(this).parents('td'),
      id = $parent.siblings('td.id-val').text()
      source_url = $parent.siblings('td.source_url-val').text(),
      realname = $parent.siblings('td.realname-val').text();

    $('#sub-id-field').val(id);
    $('#sub-source_url-field').val(source_url);
    $('#sub-realname-field').val(realname);

    $('#edit-subscription').modal('show');

    e.preventDefault();
  });
  
});