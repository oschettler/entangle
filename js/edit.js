/**
 */
jQuery(function () {

  function submit_edit_form(form) {
    var 
      $form = $(form),
      action = $form.attr('action'),
      method = $form.attr('method');
    
    var valid = true;
    var msg = [];
    $('input.required,textarea.required', $form).each(function () {
      var 
        name = $(this).attr('name'),
        val = $(this).val();

      if (val.match(/^\s*$/)) {
        $(this).parents('div.form-group').addClass('has-error');
        valid = false;
        msg.push(name + ': May not be empty');
      }
    });
    
    var $alert = $('.modal-content .alert', form);
    
    if (valid) {
      $.ajax({
        url: action,
        type: method,
        data: $form.serialize(),
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
  }

  /*------------------------------------------------------
   * Maintain location.hash
   */
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
  
  /*------------------------------------------------------
   * Subscriptions
   */
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
    var id = $(this).attr('data-id');    

    var $form = $('#subscription-form');
    $form.attr('action', '/user/edit_subscription');
    $('h4.modal-title', $form).text('Edit subscription');

    var
      $parent = $(this).parents('td'),
      source_url = $parent.siblings('td.source_url-val').text(),
      realname = $parent.siblings('td.realname-val').text();

    $('#sub-id-field').val(id);
    $('#sub-source_url-field').val(source_url);
    $('#sub-realname-field').val(realname);

    $('#edit-subscription').modal('show');

    e.preventDefault();
  });
  
  $('#subscription-form').submit(function (e) {
    submit_edit_form(this);
    e.preventDefault();    
  });
  
  /*------------------------------------------------------
   * Timelines
   */
  $('a.add-timeline').click(function (e) {
    var $form = $('#timeline-form');
    $form.attr('action', '/user/add_timeline');
    $('h4.modal-title', $form).text('Add timeline');

    $('#tl-id-field').val('');
    $('tl-user_id-field option:selected').prop('selected', false);
    $('#tl-name-field').val('');
    $('#tl-title-field').val('');
    $('#tl-timelines-field').val('');

    $('#edit-timeline').modal('show');

    e.preventDefault();
  });
  
  $('a.edit-timeline').click(function (e) {
    var id = $(this).attr('data-id');    

    var $form = $('#timeline-form');
    $form.attr('action', '/user/edit_timeline');
    $('h4.modal-title', $form).text('Edit timeline');

    var
      $parent = $(this).parents('td'),
      user_id = $parent.siblings('td.user_id-val').attr('data-user_id'),
      name = $parent.siblings('td.name-val').text(),
      title = $parent.siblings('td.title-val').text(),
      timelines = $parent.siblings('td.timelines-val').text();

    $('#tl-id-field').val(id);
    $('#tl-user_id-field').val(user_id);
    $('#tl-name-field').val(name);
    $('#tl-title-field').val(title);
    $('#tl-timelines-field').val(timelines);

    $('#edit-timeline').modal('show');

    e.preventDefault();
  });

  $('#timeline-form').submit(function (e) {
    submit_edit_form(this);
    e.preventDefault();    
  });

  /*------------------------------------------------------
   * Displays
   */
  $('a.add-display').click(function (e) {
    var $form = $('#display-form');
    $form.attr('action', '/user/add_display');
    $('h4.modal-title', $form).text('Add display');

    $('#dis-user_id-field').val('');
    $('#dis-title-field').val('');
    $('#dis-timelines-field').val('');

    $('#edit-display').modal('show');

    e.preventDefault();
  });
  
  $('a.edit-display').click(function (e) {
    var id = $(this).attr('data-id');    

    var $form = $('#display-form');
    $form.attr('action', '/user/edit_display');
    $('h4.modal-title', $form).text('Edit display');

    var
      $parent = $(this).parents('td'),
      user_id = $parent.siblings('td.user_id-val').attr('data-user_id'),
      title = $parent.siblings('td.title-val').text(),
      timelines = [];
    
    $parent.siblings('td.timelines-val').find('li').each(function () {
      timelines.push($(this).attr('data-id'));
    });

    $('#dis-id-field').val(id);
    $('#dis-user_id-field').val(user_id);
    $('#dis-title-field').val(title);
    $('#dis-timelines-field').val(timelines.join(','));

    $('#edit-display').modal('show');

    e.preventDefault();
  });

  $('#display-form').submit(function (e) {
    submit_edit_form(this);
    e.preventDefault();    
  });

  /*------------------------------------------------------
   * Locations
   */
  $('a.add-location').click(function (e) {
    var $form = $('#location-form');
    $form.attr('action', '/user/add_location');
    $('h4.modal-title', $form).text('Add location');

    $('#loc-id-field').val('');
    $('#loc-title-field').val('');
    $('#loc-longitude-field').val('');
    $('#loc-latitude-field').val('');

    $('#edit-location').modal('show');

    e.preventDefault();
  });
  
  $('a.edit-location').click(function (e) {
    var id = $(this).attr('data-id');    

    var $form = $('#location-form');
    $form.attr('action', '/user/edit_location');
    $('h4.modal-title', $form).text('Edit location');

    var
      $parent = $(this).parents('td'),
      title = $parent.siblings('td.title-val').text(),
      longitude = $parent.siblings('td.longitude-val').text(),
      latitude = $parent.siblings('td.latitude-val').text();

    $('#loc-id-field').val(id);
    $('#loc-title-field').val(title);
    $('#loc-longitude-field').val(longitude);
    $('#loc-latitude-field').val(latitude);

    $('#edit-location').modal('show');

    e.preventDefault();
  });

  $('#location-form').submit(function (e) {
    submit_edit_form(this);
    e.preventDefault();    
  });
  
});