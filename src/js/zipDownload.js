// DOWNLOAD ZIP FILE FOR IMAGES
// Image Download button fallback
jQuery(document).ready(function() {
  function downloadAll(urls) {
    var link = document.createElement('a');

    link.setAttribute('download', null);
    link.style.display = 'none';

    document.body.appendChild(link);

    for (var i = 0; i < urls.length; i++) {
      var title = urls[i].toString().match(/.*\/(.+?)\./)[1];
      link.setAttribute('href', urls[i]);
      link.setAttribute('title', title);
      link.setAttribute('download', title);
      link.click();
   }

   document.body.removeChild(link);
  }

  function urlToPromise(url) {
   return new Promise(function(resolve, reject) {
     JSZipUtils.getBinaryContent(url, function (err, data) {
       if(err) {
         reject(err);
       } else {
         resolve(data);
       }
     });
   });
  }
  jQuery('#download').click(function() {
   var files = jQuery(this).data('files').split(' ');
   console.log(files);
   if(!JSZip.support.blob) {
     downloadAll(files);
   } else {
     var zip = new JSZip();
     for (var i = 0; i < files.length; i++) {
       console.log(files[i]);
       var url = files[i];
       var filename = url.replace(/.*\//g, "");
       zip.file(filename, urlToPromise(url), {binary:true});
     }
     console.log(zip);

     // when everything has been downloaded, we can trigger the dl
     zip.generateAsync({type:"blob"}, function updateCallback(metadata) {
      //  var msg = "progression : " + metadata.percent.toFixed(2) + " %";
       if(metadata.currentFile) {
        //  msg += ", current file = " + metadata.currentFile;
       }
      //  updateProgress(metadata.percent|0);
      })
      .then(function callback(blob) {
        var d = new Date();
        var today = d.toISOString().substring(0, 10);
        var uniqid = today + '-' + Math.random().toString(36).substr(2, 5);
        var zipName = 'Marathon - ' + uniqid + '.zip';
        // download using FileSaver.js
        saveAs(blob, zipName);
      }, function (e) {
      });
    }
  });
}); // end document ready