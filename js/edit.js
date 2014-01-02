/**
 */
jQuery(function () {

  $('ul.nav-tabs a').click(function () {
    location.hash = this.hash;
    return true;
  });
  
  window.onhashchange = function () {
    var hash = window.location.hash;
    hash && $('ul.nav-tabs a[href="' + hash + '"]').tab('show');
  };
  var hash = window.location.hash;
  hash && $('ul.nav-tabs a[href="' + hash + '"]').tab('show');
  
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
    $form.attr('action', '/user/edit_subscription');
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
  
  $('#subscription-form').submit(function (e) {
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
        case 'source_url':
          if (val.match(/^\s*$/)) {
            $(this).parents('div.form-group').addClass('has-error');
            valid = false;
            msg.push(name + ': May not be empty');
          }
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
          location.reload();
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