import $ from 'jquery';
import Routing from 'fos-js-routing';
import Translator from 'bazinga-translator';
import 'fine-uploader/jquery.fine-uploader/jquery.fine-uploader.min.js';

const locale = $('html').attr('lang');
const $form = $('form[name="appbuild_application"]');

const $displayImageUploadContainer = $('#appbuild_application_displayImageFile');
$displayImageUploadContainer.fineUploader(Object.assign(
  {
    template: 'upload-container-template',
    request: {
      endpoint: Routing.generate('appbuild_admin_application_display_image_upload', {
        _locale: locale,
      }),
      inputName: 'displayImageFile',
    },
    multiple: false,
    validation: {
      allowedExtensions: ['jpeg', 'jpg', 'png'],
      itemLimit: 1,
    },
    text: {
      defaultResponseError: Translator.trans('admin.upload.message.default'),
      failUpload: Translator.trans('admin.upload.message.upload_failure'),
      formatProgress: Translator.trans('admin.upload.message.progress_bar'),
      waitingForResponse: Translator.trans('admin.upload.message.waiting_for_response'),
    },
    messages: {
      typeError: Translator.trans('admin.upload.message.invalid_extension'),
      onLeave: Translator.trans('admin.upload.message.on_leave'),
      tooManyItemsError: Translator.trans('admin.upload.message.too_many_items'),
    },
  },
  !$form.data('id') ? {} : {
    session: {
      endpoint: Routing.generate('appbuild_admin_application_get_display_image', {
        _locale: locale,
        id: $form.data('id'),
      }),
    },
  }
)).on('submitted', () => {
  // Remove the ability to send files
  $form.find('.qq-upload-button-selector, qq-upload-drop-area-selector').hide();
}).on('cancel', () => {
  // Reset the ability to send files
  $form.find('.qq-upload-button-selector, qq-upload-drop-area-selector').show();
}).on('error', (event, id, name, errorReason) => {
  // Clear filename field
  $('#appbuild_application_displayImageFilename').val('');
  // Show error message
  alert(errorReason);
}).on('complete', (event, id, name, responseJSON) => {
  if (responseJSON.success) {
    // Set filename field
    $('#appbuild_application_displayImageFilename').val(responseJSON.filename);
  }
}).on('sessionRequestComplete', (event, response) => {
  // if (responseJSON.success) {
  //   // Set filename field
  //   $('#appbuild_application_displayImageFilename').val(responseJSON.filename);
  // }
});
