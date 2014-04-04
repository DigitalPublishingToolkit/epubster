var epub;

$.fn.extend({
insertAtCaret: function(myValue){
  return this.each(function(i) {
    if (document.selection) {
      //For browsers like Internet Explorer
      this.focus();
      var sel = document.selection.createRange();
      sel.text = myValue;
      this.focus();
    }
    else if (this.selectionStart || this.selectionStart == '0') {
      //For browsers like Firefox and Webkit based
      var startPos = this.selectionStart;
      var endPos = this.selectionEnd;
      var scrollTop = this.scrollTop;
      this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
      this.focus();
      this.selectionStart = startPos + myValue.length;
      this.selectionEnd = startPos + myValue.length;
      this.scrollTop = scrollTop;
    } else {
      this.value += myValue;
      this.focus();
    }
  });
}
});

function slugify(slug) {
    return slug
        .toLowerCase()
        .replace(/[^\w ]+/g,'')
        .replace(/ +/g,'-');
}

function sectionTabs() {
  var currentTab = $.jStorage.get("selected-tab");
  if (currentTab) {
    $('#edition-tabs a[href="'+currentTab+'"]').tab('show');
  }
  $(document).on('click', '#edition-tabs a, #section-tabs a[id!="add-new-section"]', function (event) {
    $('.tab-settings').popover('destroy');
    event.preventDefault();
    $(this).tab('show');
    $.jStorage.set("selected-tab", $(this).attr('href'));
  });

  $('#section-tabs a#add-new-section').on('click', function (event) {
    var clickedElement = $(this);
    var href = clickedElement.attr('href');
    $.get(href, function(response) {
      var tabTitle = $('input.section-title', response).val();
      var tabSlug = slugify(tabTitle);
      $('<li><a href="#section-'+tabSlug+'">'+tabTitle+'</a>').insertBefore('#section-tabs li:last');
      $('#tab-content .tab-content').append(response);
      $('#section-tabs a[href="#section-'+tabSlug+'"]').tab('show');

      var count = href.match(/\d+$/);
      if (count) {
        var count = parseInt(count[0])+1;
      }      
      var alteredHref = href.replace(/\d+$/, count);
      clickedElement.attr('href', alteredHref);
    });
    event.preventDefault();
  });
  
  $('select#EditionChapters').on('change', function() {
    var tab = '#section-'+$(this).val();
    $('#section-tabs a[href="'+tab+'"]').tab('show')
  });
}

$(':not(#anything)').on('click', function (e) {
  $('.tab-settings').each(function () {
    if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
      $(this).popover('hide');
      return;
    }
  });
});

function sectionSort() {
  $( "#section-sorting" ).sortable({
    placeholder: "ui-state-highlight",
    stop : function() {
      var sectionOrder = [];
      $('ul#section-sorting li').each(function(index, element) {
        var id = $(element).attr('id').substr(8);
        var order = {id : id, order : index+1};
        sectionOrder.push(order);
      });
      var sectionOrder = JSON.stringify(sectionOrder);
      $('input#EditionSection-order').val(sectionOrder);
      $('#section-sorting .section-delete').show();
      $('#section-sorting .section-delete:first').hide();
    }
  });
  $( "#section-sorting" ).disableSelection();
  
  $('#section-sorting .section-delete').on('click', function(event) {
    var id = $(this).attr('id').substr(15);
    var sectionDelete = $('input#EditionSection-delete').val();
    if (sectionDelete) {
      var sectionDelete = JSON.parse(sectionDelete);
    } else {
      var sectionDelete = [];  
    }
    sectionDelete.push(id);
    var sectionDelete = JSON.stringify(sectionDelete);
    $('input#EditionSection-delete').val(sectionDelete);

    $(this).parent().fadeOut('fast', function() {
      $(this).remove();
    });
    event.preventDefault();
  });
}

function generateEPUB(id) {
  $.ajax({
    url: SITE_URL+"editions/generate/"+id,
    beforeSend : function() {
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
      var target = document.getElementById('epub-container');
      var spinner = new Spinner(opts).spin(target);
    },
    success : function(response) {
      if (response) {
        $('#epub-container').fadeOut('fast', function() {
          epub = response+'.epub';
          $(this).html('').append('<iframe id="page-epub" src="'+SITE_URL+'/assets/epub-js/epub-js.html"></iframe>').fadeIn('fast');
        });        
      }
    }
  });
}

$(document).ready(function() {
  $('.markdown-editor').markItUp(mySettings);
  sectionTabs();
  sectionSort();
  $('body').popover({
    selector: '[rel="popover"]',
    html:true
  });
});