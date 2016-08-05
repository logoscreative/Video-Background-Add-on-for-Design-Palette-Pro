//********************************************************************************************************************************
// now start the engine
//********************************************************************************************************************************
jQuery(document).ready( function($) {

//********************************************************************************************************************************
// quick helper to check for an existance of an element
//********************************************************************************************************************************
  $.fn.divExists = function (callback) {
    // slice some args
    var args = [].slice.call(arguments, 1);
    // check for length
    if (this.length) {
      callback.call(this, args);
    }
    // return it
    return this;
  };

//********************************************************************************************************************************
// media uploader for video-background file
//********************************************************************************************************************************
  jQuery('div.gppro-video-background-input').divExists(function () {
    // Uploading files
    var file_frame;
    // our click action
    jQuery('div.gppro-video-background-input').on('click', 'input.gppro-video-background-upload', function () {
      // If the media frame already exists, reopen it.
      if (file_frame) {
        file_frame.open();
        return;
      }
      // Create the media frame.
      file_frame = wp.media.frames.file_frame = wp.media({
        title: adminVideoBackgroundData.videobgtitle,
        button: {
          text: adminData.uploadbutton
        },
        library: {
          type: 'video'
        },
        multiple: false
      });

      // When an image is selected, run a callback.
      file_frame.on('select', function () {
        // We set multiple to false so only get one image from the uploader
        attachment = file_frame.state().get('selection').first().toJSON();
        // bail if nothing is there or there is no URL or subtype value
        if (!attachment || attachment.url === '' || attachment.subtype === '') {
          return;
        }
        // check file type to only allow .png .gif or .ico
        if (jQuery.inArray(attachment.subtype, ['mp4']) == -1) {
          return;
        }
        // fetch the URL of the attachment grab the relevant data
        userImage = attachment.url;
        // populate the appropriate areas
        jQuery('div.gppro-video-background-wrap').find('input.gppro-video-background-field').val(userImage);
      });
      // Finally, open the modal
      file_frame.open();
    });
  });

  //********************************************************************************************************************************
// media uploader for video-poster file
//********************************************************************************************************************************
  jQuery('div.gppro-video-poster-input').divExists(function () {
    // Uploading files
    var file_frame;
    // our click action
    jQuery('div.gppro-video-poster-input').on('click', 'input.gppro-video-poster-upload', function () {
      // If the media frame already exists, reopen it.
      if (file_frame) {
        file_frame.open();
        return;
      }
      // Create the media frame.
      file_frame = wp.media.frames.file_frame = wp.media({
        title: adminVideoBackgroundData.postertitle,
        button: {
          text: adminData.uploadbutton
        },
        library: {
          type: 'image'
        },
        multiple: false
      });

      // When an image is selected, run a callback.
      file_frame.on('select', function () {
        // We set multiple to false so only get one image from the uploader
        attachment = file_frame.state().get('selection').first().toJSON();
        // bail if nothing is there or there is no URL or subtype value
        if (!attachment || attachment.url === '' || attachment.subtype === '') {
          return;
        }
        // check file type to only allow .png .gif or .ico
        if (jQuery.inArray(attachment.subtype, ['jpg', 'jpeg', 'png', 'gif']) == -1) {
          return;
        }
        // fetch the URL of the attachment grab the relevant data
        userImage = attachment.url;
        // populate the appropriate areas
        jQuery('div.gppro-video-poster-wrap').find('input.gppro-video-poster-field').val(userImage);
      });
      // Finally, open the modal
      file_frame.open();
    });
  });

});