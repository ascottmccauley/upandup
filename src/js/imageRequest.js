// DOWNLOAD ZIP FILE FOR IMAGES
// Image Download button fallback
jQuery(document).ready(function() {
  // make line of images selectable
  imageItems = [];
  jQuery('.imageRequestItem').each(function() {
    jQuery(this).on('click', function(e) {
      e.preventDefault();
      var sku = jQuery(this).data('sku');
      if(imageItems.indexOf(sku) != -1) {
        jQuery(this).removeClass('selected');
          imageItems.splice(imageItems.indexOf(sku), 1);
      } else {
        jQuery(this).addClass('selected');
          imageItems.push(sku);
      }
    });
  });
  jQuery('.imageRequestLink').each(function() {
    jQuery(this).on('click', function(e) {
      // stop page refresh on click
      e.preventDefault();
    });
  });

  // send email
  jQuery('.requestImagesForm').submit(function(event) {

    // checkboxes = jQuery('.requestImagesForm input:checked').each(function() {
    //   imageItems.push($(this).val());
    // });
    console.log(imageItems);
    if(imageItems.length > 0) {
      jQuery('.requestImages').addClass('submitting');
      data = { 'action': 'request_images',
        'nonce' : wpVars.nonce,
        'image_items': imageItems,
      };
      console.log(data);
      jQuery.post(wpVars.ajaxurl, data, function(response) {
        console.log(response);
        // if successful
        if(response[4]) {
          jQuery('.requestSuccess').removeClass('hide');
          jQuery('.imageRequestEmail').addClass('hide');
          jQuery('.requestImages').removeClass('submitting')
            .addClass('success')
            .prop("disabled",true)
            .text('Submitted');

        }
      });
    }
    // prevent form from submitting and reloading the page
    return false;
  });
  jQuery('.downloadImage').each(function() {
    jQuery(this).on('click', function(e) {
      e.preventDefault();
  		var imageItems = e.target.dataset.files.split(' ');

      console.log(imageItems);
      if(imageItems.length > 0) {
        jQuery('.requestImages').addClass('submitting');
        data = { 'action': 'download_images',
          'nonce' : wpVars.nonce,
          'image_items': imageItems,
        };
        console.log(data);
        jQuery.post(wpVars.ajaxurl, data, function(response) {
          console.log(response);
          // if successful
          if(response[4]) {
            var zipUrl = response[3].zip_url;
            var blob = new Blob([zipUrl], {type: "text/csv;charset=utf-8"});
            if (window.navigator.msSaveOrOpenBlob) {
              window.navigator.msSaveBlob(blob, 'Marathon_Images');
            } else {
              var link = document.createElement("a");
              link.setAttribute("download", response[5].zip_name);
              link.href = zipUrl;
              document.body.appendChild(link);
              link.click();
              document.body.removeChild(link);
            }

            jQuery('#download').addClass('downloaded').text('Downloaded').prop("disabled",true);
          }
        });
      }
  	});
  });
}); // end document ready