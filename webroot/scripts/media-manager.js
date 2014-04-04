function addMarkdownSyntax(file) {
  if (file !== undefined) {
    return '!['+file.caption+']('+UPLOAD_URL+file.name+' "'+file.caption+'")';    
  }
}

function getFileLibrary() {
  var data = {editionId:$('input#EditionId').val()};

  $.ajax({
    url: SITE_URL+"/items/file_library",
    data : data,
    type : 'POST',
    beforeSend: function() {
      $('#file-library').html('');
      var options = {
        lines: 10, // The number of lines to draw
        length: 5, // The length of each line
        width: 2, // The line thickness
        radius: 5, // The radius of the inner circle
        corners: 1, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#666', // #rgb or #rrggbb or array of colors
        speed: 1.7, // Rounds per second
        trail: 80, // Afterglow percentage
        shadow: false, // Whether to render a shadow
        hwaccel: true, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: 'auto', // Top position relative to parent in px
        left: 'auto' // Left position relative to parent in px
      };
      var element = document.getElementById("file-library");
      var spinner = new Spinner(options).spin();
      element.appendChild(spinner.el);
    }
  }).done(function(data) {
    $("#file-library").fadeOut('fast', function() {
      fileList = {};
      $('#insert-media').addClass('disabled');
      $('#file-library').html(data);
      $(this).fadeIn('fast');
    });
  });
}


function mediaManager() { 
  if ($('#media-manager').length !== 0) {
    var fileNotice;
    var fileList = new Array();

  	$("#upload-files").hide();
  	$("#insert-media").addClass('disabled');
    var uploader = new plupload.Uploader({
    	runtimes : 'html5,gears,flash,silverlight,browserplus',
    	browse_button : 'select-files',
    	container: 'file-uploader',
    	max_file_size : '10mb',
    	url : SITE_URL+"/items/file_upload",
    	resize : {width : 320, height : 240, quality : 90},
    	flash_swf_url : SITE_URL+'/assets/plupload/plupload.flash.swf',
    	silverlight_xap_url : SITE_URL+'/assets/plupload.silverlight.xap',
    	filters : [
    		{title : "Image files", extensions : "jpg,gif,png"},
    		{title : "Zip files", extensions : "zip"}
    	],
      multipart_params : {
        'editionId' : $('input#EditionId').val(), 
      }
    });
    
    uploader.bind('Init', function(up, params) {
      if (params.runtime !== null) {
      	$('#filelist #runtime-alert').remove();
      } else {
        $('#filelist #files').remove();
      }
    });
    
    uploader.init();
    
    uploader.bind('FilesAdded', function(up, files) {
    	for (var i in files) {
    	  var file = files[i];
    	  var data = {fileId : file.id, fileName : file.name, fileSize : plupload.formatSize(file.size)};
    	  
        $.post( SITE_URL+"/items/file_item", data).done(function( data ) {
          $('#filelist ul#files').append(data);
        });
    	}
    	if (files.length) {
      	$("#upload-files").fadeIn('fast');
    	}
    	
    	$(document).on('click', '#file-uploader .remove-file', function(event) {
      	var fileId = $(this).closest('.file').attr('id');
      	var file = uploader.getFile(fileId);
      	if (file) {
      	  $('.file#'+fileId).fadeOut('fast', function() {
      	    $(this).remove();
            uploader.removeFile(file);
      	  });
      	}
    	});
    	up.refresh();
    });

  	uploader.bind('Error', function(up, err) {
  	  $("ul#files li#"+err.file.id).remove();
	    var fileId = (err.file ? err.file.id : "");
	    var fileName = (err.file ? err.file.name : "");
	      	    
  	  var data = {fileId : fileId, fileName: fileName, errorCode : err.code, errorMessage : err.message};
      $.post( SITE_URL+"/items/file_error", data).done(function( data ) {
        $('#filelist ul#files').append(data);
      });
  		up.refresh();
  	});
  	
  	uploader.bind('QueueChanged', function(up, files) {
    	if (up.files.length === 0) {
      	$("#upload-files").fadeOut('fast');
    	}
  	});

  	uploader.bind('FileUploaded', function(up, file, info) {
      $('ul#files li#' + file.id + " .remove-file i").removeClass('fa-times-circle').addClass('fa-check-circle').addClass('text-success');
      $('ul#files li#' + file.id + " .remove-file").removeClass('remove-file').addClass('file-success');
      $('ul#files li#' + file.id + " .progress").fadeOut('slow');
  	});    

  	uploader.bind('UploadComplete', function(up, files) {
  	  $("#upload-files").fadeOut('fast');
  	  if ($.type(fileList) === "object") {
  	    fileList = [];
  	  }
  	  for (var i in files) {
  	    var file = files[i];
        var file = {name: file.name, caption : file.name};
  	    var filename = addMarkdownSyntax(file);
  	    fileList.push(filename);
  	  }
  	  $("#insert-media").removeClass('disabled');
  	});
    
    uploader.bind('UploadProgress', function(up, file) {
      $('#filelist ul#files li#'+file.id+' span.progress-bar').width(file.percent+'%').attr('aria-valuenow', file.percent);
    });
    
    $('#upload-files').on('click', function(event) {
      uploader.start();
      event.preventDefault();
    });
    
    $("#insert-media").on('click', function(event) {
      if ($.type(fileList) === "object") {
        var newFileList = [];       
        for (var fileId in fileList) {
          var file = fileList[fileId]
          var file = addMarkdownSyntax(file);
          newFileList.push(file);
        }
        var markdown = newFileList.join("\n")+"\n";
      } else {
        var markdown = fileList.join("\n")+"\n";        
      }
      $('textarea.text-editor:visible').insertAtCaret(markdown);
      $('#media-manager').modal('hide');
      $('#modal-toggles a').removeClass('disabled');
    });
    
    $('.btn-group a').on('click', function(event) {
      if (!$(this).hasClass('disabled')) {
        $('.btn-group a').toggleClass('disabled');
        $('#file-uploader, #file-library').toggle();
        if ($(this).attr('id') === 'toggle-file-library') {
          getFileLibrary();
        }
      }
      event.preventDefault();
    });
    
    $(document).on('click', '#file-library a.thumbnail', function(event) {
      var itemId = $(this).attr('id');
      if ( $(event.target).closest('.delete-file').length > 0 ) {
        var fileItem = $(this);
        var fileId = fileItem.attr('id').substr(5);
        var data = {fileId:fileId,editionId:$('input#EditionId').val()};
        $.post( SITE_URL+"/items/file_delete", data).done(function( data ) {
          fileItem.remove();
          if (fileList[itemId] !== null) {
            delete fileList[itemId];
          }
        });
      } else {
        $(this).toggleClass('selected');
        if ($(this).hasClass('selected')) {
          var file = {name: $('img', this).attr('data-filename'), caption : $('img', this).attr('alt')};
          fileList[itemId] = file;
        } else if (fileList[itemId] !== null) {
          delete fileList[itemId];
        }
        if (fileList !== undefined) {
          if (!$.isEmptyObject(fileList)) {
            $('#insert-media').removeClass('disabled');
          } else {
            $('#insert-media').addClass('disabled');
          }        
        }        
      }        
      event.preventDefault();
    });
    
    $('#media-manager').on('hidden.bs.modal', function () {
      uploader.files = [];
      $('#upload-files').hide();
      $('ul#files li').remove();
      uploader.refresh();
      fileList = '';   
      $('#file-library a.thumbnail').removeClass('selected');
      $('#modal-toggles a').removeClass('disabled');
    });
    
    $('#media-manager').on('show.bs.modal', function () {
      getFileLibrary();
    });
  }
}

function coverUploader() {
  var uploader = new plupload.Uploader({
  	runtimes : 'html5,gears,flash,silverlight,browserplus',
  	browse_button : 'cover-image-upload',
  	container: 'cover-uploader',
  	drop_element : 'cover-image-upload',
  	max_file_size : '10mb',
  	url : SITE_URL+"/items/file_upload",
  	resize : {width : 320, height : 240, quality : 90},
  	flash_swf_url : SITE_URL+'/assets/plupload/plupload.flash.swf',
  	silverlight_xap_url : SITE_URL+'/assets/plupload.silverlight.xap',
  	filters : [
  		{title : "Image files", extensions : "jpg,gif,png"},
  		{title : "Zip files", extensions : "zip"}
  	],
    multipart_params : {
      'editionId' : $('input#EditionId').val(), 
      'type' : 'cover'
    }
  });
  uploader.init();
  
  uploader.bind('FilesAdded', function(up, files) {
    up.start();
  });

  uploader.bind('UploadComplete', function(up, files) {
    $('#cover-image-upload span.fa-plus').show('');
    $('#cover-image-upload .spinner').remove('');
    
    var id = $('input#EditionId').val();
    $.get( SITE_URL+"/editions/get_cover/"+id ).done(function( data ) {
      $('#cover-image-current').css({'background-image' : 'url("'+UPLOAD_URL+data+'")'});
    });
  });

  uploader.bind('BeforeUpload', function(up, files) {
    var opts = {
      lines: 11, // The number of lines to draw
      length: 6, // The length of each line
      width: 2, // The line thickness
      radius: 5, // The radius of the inner circle
      corners: 1, // Corner roundness (0..1)
      rotate: 0, // The rotation offset
      direction: 1, // 1: clockwise, -1: counterclockwise
      color: '#333', // #rgb or #rrggbb or array of colors
      speed: 1, // Rounds per second
      trail: 60, // Afterglow percentage
      shadow: false, // Whether to render a shadow
      hwaccel: false, // Whether to use hardware acceleration
      className: 'spinner', // The CSS class to assign to the spinner
      zIndex: 2e9, // The z-index (defaults to 2000000000)
      top: 'auto', // Top position relative to parent in px
      left: 'auto' // Left position relative to parent in px
    };
    $('#cover-image-upload span.fa-plus').hide('');
    var target = document.getElementById('cover-image-upload');
    var spinner = new Spinner(opts).spin(target);
  });
}

$(document).ready(function() {
  mediaManager();
  coverUploader();
  $('body').tooltip({
    selector: '[data-toggle=tooltip]'
  });
});