/**
 * WattDrop admin: Media Library icon picker.
 * Opens the native WP media window, stores the chosen attachment ID
 * in the hidden field and refreshes the preview.
 */
(function ($) {
  'use strict';

  var $idField = $('#wattdrop-icon-id');
  var $preview = $('#wattdrop-preview');
  var $removeBtn = $('#wattdrop-remove');
  var frame;

  $('#wattdrop-choose').on('click', function (e) {
    e.preventDefault();

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: wattdrop.chooseTitle,
      button: { text: wattdrop.chooseText },
      library: { type: 'image' },
      multiple: false
    });

    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $idField.val(attachment.id);
      $preview.html(attachment.url
        ? '<img src="' + attachment.url + '" width="64" height="64" style="border-radius:6px;" alt="">'
        : '<span class="description">ID ' + attachment.id + '</span>');
      $removeBtn.show();
    });

    frame.open();
  });

  $removeBtn.on('click', function (e) {
    e.preventDefault();
    $idField.val('0');
    $preview.html('<span class="description">' + wattdrop.removeText + '</span>');
    $removeBtn.hide();
  });

  // Hide the remove button when no icon is set yet.
  if ('' === $idField.val() || '0' === $idField.val()) {
    $removeBtn.hide();
  }
})(jQuery);