var epub;
var editor;
var savedSelection;
var footnote;
var footnoteValue;

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
        .replace(/.xhtml/,'')        
        .replace(/[^\w ]+/g,'-')
        .replace(/ +/g,'-');
}

function sectionTabs() {
  var currentTab = $.jStorage.get("selected-tab");
  if (currentTab) {
    $('#edition-tabs a[href="'+currentTab+'"]').tab('show');
  }
  
  var currentSectionTab = $.jStorage.get("selected-section-tab");
  if (currentSectionTab) {
    $('#section-tabs a[href="'+currentSectionTab+'"]').tab('show');
  }

  $(document).on('click', '#edition-tabs a, #section-tabs a[id!="add-new-section"]', function (event) {
    var parent = $(this).parents('ul');
    $('.tab-settings').popover('destroy');
    event.preventDefault();
    $(this).tab('show');
    
    if (parent.attr('id') === 'section-tabs') {
      $.jStorage.set("selected-section-tab", $(this).attr('href'));      
    } else {
      $.jStorage.set("selected-tab", $(this).attr('href'));
    }
    
    if (editor) {
      editor.deactivate(); 
    }
    setupMediumEditor();
  });

  $('#section-tabs a#add-new-section').on('click', function (event) {
    var clickedElement = $(this);
    var href = clickedElement.attr('href');
    $.get(href, function(response) {
      var tabTitle = $('input.section-title', response).val();
      var tabSlug = slugify(tabTitle);
      $('<li><a href="#section-'+tabSlug+'">'+tabTitle+'</a>').insertBefore('#section-tabs li:last');
      $('#tab-content .tab-content').append(response);
      var tab = '#section-'+tabSlug;
      $('#section-tabs a[href="'+tab+'"]').tab('show')

      editor.deactivate();
      setupMediumEditor();

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
    $('#section-tabs a[href="'+tab+'"]').tab('show');
    
    editor.deactivate();
    setupMediumEditor();

  });
  
  $('.editions .btn-primary[type="submit"]').on('click', function(event) {    
    $('.form-control-feedback').remove();
    $('.has-feedback').removeClass('has-success').removeClass('has-error').removeClass('has-feedback');
    
    var error = 0;
    var emptyInput = '';
    $(':input[required]', '.editions form').each(function(index, element) {
      var formGroup = $(this).parents('.form-group');
      formGroup.addClass('has-feedback');
            
      if($(this).val() == ''){
        formGroup.addClass('has-error').append('<span class="fa fa-ban form-control-feedback"></span>');
        if (index === 0) {
          emptyInput = $(this);
        }
        if(error == 0){ $(this).focus(); }
        error = 1;
      } else {
        formGroup.addClass('has-success').append('<span class="fa fa-check-circle form-control-feedback"></span>');   
      }
    });

    if (error == 1) {
      if (emptyInput) {
        emptyInput.focus();        
      }
      var tab = emptyInput.closest('.tab-pane').attr('id');
      $('#edition-tabs a[href="#' + tab + '"]').tab('show');
      $('#form-validation-error').show();
      event.preventDefault();
    }
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

function generateEdition() {
  $('#generate-epub').on('click', function(event) {
    var id = $('#EditionId').val();
    generateEPUB(id, true)
    event.preventDefault();
  });
}

function generateEPUB(id, download) {
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
      if (download) {
        $('#epub-generator-overlay').fadeIn('fast');
        $('body, html').css('overflow', 'hidden');
      } else {
        var spinner = new Spinner(opts).spin(target);      
        var target = document.getElementById('epub-container');
      }
    },
    success : function(response) {
      if (response) {
        if (download) {
          epub = response+'.epub';
          window.location.replace(epub);
          $('#epub-generator-overlay').fadeOut('fast', function() {
            $('body, html').css('overflow', 'auto');
          });
        } else {
          $('#epub-container').fadeOut('fast', function() {
            epub = response+'.epub';
            $(this).html('').append('<iframe id="page-epub" src="'+SITE_URL+'/assets/epub-js/epub-js.html"></iframe>').fadeIn('fast');
          });          
        }
      }
    }
  });
}

function cleanFootnote(footnote) {
  if (footnote !== undefined) {
    return footnote.replace(/\[\^](.*?)\]/, function(str, value) { return value; }); 
  }
}

/* Mark persons */
function Person() {
  rangy.init();

  this.button = document.createElement('button');
  this.button.className = 'medium-editor-action';
  this.button.innerHTML = '<i class="fa fa-user"></i>';
  this.button.onclick = this.onClick.bind(this);
  
  this.classApplier = rangy.createCssClassApplier("is-person", {
    elementTagName: 'span',
    normalize: true
  });
}

Person.prototype.onClick = function(node) {
  this.classApplier.toggleSelection();
}

Person.prototype.getButton = function() {
  return this.button;
}

Person.prototype.checkState = function (node) {
  var node = $(node);
  if (node.hasClass('is-person')) {
    this.button.classList.add('medium-editor-button-active');
  }
}

/* Mark index words */
function Index() {
  rangy.init();

  this.button = document.createElement('button');
  this.button.className = 'medium-editor-action';
  this.button.innerHTML = '<i class="fa fa-thumb-tack"></i>';
  this.button.onclick = this.onClick.bind(this);
  
  this.classApplier = rangy.createCssClassApplier("is-highlight", {
    elementTagName: 'span',
    normalize: true
  });
}

Index.prototype.onClick = function(node) {
  this.classApplier.toggleSelection();
}

Index.prototype.getButton = function() {
  return this.button;
}

Index.prototype.checkState = function (node) {
  var node = $(node);
  if (node.hasClass('is-highlight')) {
    this.button.classList.add('medium-editor-button-active');
  }
}

/* Poorly cobbled together footnote functions, portions lifted from medium-editor.js (https://github.com/daviferreira/medium-editor/blob/master/dist/js/medium-editor.js) */

function Notes() {
  this.button = document.createElement('button');
  this.button.className = 'medium-editor-action';
  this.button.innerHTML = '<i class="fa fa-comment"></i>';
  this.button.onclick = this.onClick.bind(this);
  
  if ($('.medium-editor-footnote-preview').length === 0) {
    $("body").append('<div id="medium-editor-footnote-preview-container" class="medium-editor-anchor-preview medium-toolbar-arrow-over" style="top: 973px; left: 632.5px;"><div class="medium-editor-toolbar-anchor-preview" id="medium-editor-toolbar-footnote-preview"><span class="medium-editor-toolbar-footnote-preview-inner"></span></div></div>'); 
  }
}

Notes.prototype.onClick = function(node) {
  if ($('.medium-editor-toolbar-form-note').length === 0) {
    $(".medium-editor-toolbar").append('<div class="medium-editor-toolbar-form-note"><div class="medium-editor-toolbar-form-note-input" contenteditable=true></div><textarea class="hidden"></textarea><a class="toolbar-close" href="#">×</a> <a class="toolbar-done" href="#"><span class="fa fa-check"></span></a></div>'); 
    if ($('.medium-editor-toolbar-form-note-input').html() == '' && footnoteValue !== '') {
      $('.medium-editor-toolbar-form-note-input').html(footnoteValue); 
    }
  }

  savedSelection = saveSelection();

  editor.toolbarActions.style.display = 'none';
  $('.medium-editor-toolbar-form-note').show();
  editor.keepToolbarAlive = true;
  $('.medium-editor-toolbar-form-note-input').focus();
  
  $('.medium-editor-toolbar-form-note a').on('click', function (event) {
    if (!$(this).hasClass('toolbar-done')) {
      event.preventDefault();
      editor.showToolbarActions();
      $('.medium-editor-toolbar-form-note').hide();
      $('.medium-editor-toolbar-form-note-input').html('');
      
      restoreSelection(savedSelection);    
      var footnote = document.getSelection().anchorNode.parentNode;
      if ($(footnote).hasClass('has-footnote')) {
        var text = document.getSelection().anchorNode.nodeValue;
        footnote.remove();
        document.execCommand("insertText", false, text);
      }      
    }
  });

  $('.medium-editor-toolbar-form-note a.toolbar-done').on('click', function (event) {
      var note = $('.medium-editor-toolbar-form-note-input').html();
      $('.medium-editor-toolbar-form-note-input textarea').html(note);
      event.preventDefault();
      $(footnote).remove();
      restoreSelection(savedSelection);
      var value = note;
      if (value) {
        insertHTMLAtCursor('<span class="has-footnote">'+document.getSelection()+'<span class="inline-footnote-content">[^] '+value+']</span></span>', false);
        
        var doubleWrappedFootnotes = $('.has-footnote .has-footnote');
        if (doubleWrappedFootnotes.length !== 0) {
          $('.has-footnote .has-footnote').each(function(index, element) {
            $(this).parent().replaceWith($(this));
          });
        }
      }
      editor.hideToolbarActions();
      $('.medium-editor-toolbar-form-note').hide();
      $('.medium-editor-toolbar-form-note-input').html('');
  });
}

Notes.prototype.getButton = function() {
  return this.button;
}

Notes.prototype.checkState = function (node) {
  var node = $(node);
  if (node.hasClass('has-footnote')) {
    if (footnote === undefined || footnote === null || footnote === '') {
      footnote = $('.inline-footnote-content', node); 
    }
    footnoteValue = $('.inline-footnote-content', node).first().html();
    footnoteValue = cleanFootnote(footnoteValue);
    $('.medium-editor-toolbar-form-note-input').html(footnoteValue);

    this.button.classList.add('medium-editor-button-active');
  }
}

function previewFootnote() {
  $( document ).on("mouseenter mouseleave", '.has-footnote', function(event) {
    var preview = $('#medium-editor-footnote-preview-container');
    var footnote = cleanFootnote($('.inline-footnote-content', this).html());

    if (event.type === 'mouseenter') {
      preview.addClass('medium-editor-anchor-preview-active');
      $('#medium-editor-toolbar-footnote-preview .medium-editor-toolbar-footnote-preview-inner').append('<span class="footnote-preview">'+footnote+'</span>');

      var boundary = $(this).offset();
      preview.css({
        'top': boundary.top - (preview.height() + $(this).height())+100,
        'left': boundary.left - ( preview.width() - $(this).width()  ) /2
      });

    } else {
      preview.removeClass('medium-editor-anchor-preview-active');
      $('#medium-editor-toolbar-footnote-preview .medium-editor-toolbar-footnote-preview-inner span.footnote-preview').remove();
    }
  });
}

// http://stackoverflow.com/questions/5605401/insert-link-in-contenteditable-element
// by Tim Down
function saveSelection() {
  var i,
      len,
      ranges,
      sel = window.getSelection();
  if (sel.getRangeAt && sel.rangeCount) {
      ranges = [];
      for (i = 0, len = sel.rangeCount; i < len; i += 1) {
          ranges.push(sel.getRangeAt(i));
      }
      return ranges;
  }
  return null;
}

function restoreSelection(savedSel) {
  var i,
      len,
      sel = window.getSelection();
  if (savedSel) {
      sel.removeAllRanges();
      for (i = 0, len = savedSel.length; i < len; i += 1) {
          sel.addRange(savedSel[i]);
      }
  }
}

function setupMediumEditor() {
  editor = new MediumEditor('.html-editor', {
    buttons: ['bold', 'italic', 'underline', 'quote', 'link', 'anchor', 'orderedlist', 'unorderedlist', 'header1', 'header2', 'note', 'index', 'person'],
    buttonLabels : 'fontawesome',
    forcePlainText: false,
    cleanPastedHTML: true,
    placeholder : 'Start writing the content of this section...',
    extensions: {
      'note': new Notes(),
      'index': new Index(),
      'person': new Person()
    }
  });
}

function copyTextfield() {
  $('.html-editor').each(function(index, element) {
    $('.inline-footnote-content').show();
    var id = $(element).attr('id').substr(7);
    var text = $(element).html().trim();
    $('#textarea-'+id).html(text);
  });
}

function setupEditors() {
  var currentEditor = $.jStorage.get("current-editor");

  if (currentEditor === 'plain-text') {
    $('.wysiwyg, .plain-text, .plain-text-editor, .html-editor').toggleClass('hidden');
    $('.rich-text-processor').toggleClass('disabled');
    $('.btn-primary').off();
  }
  if (currentEditor === 'wysiwyg' || currentEditor === null) {
    setupMediumEditor();
    
    $('body').on('click', '.btn-primary', copyTextfield);

  }
  
  //$('body').on('click', '.btn-primary', function(event) { event.preventDefault(); });
  
  //$('.markdown-editor').markItUp(mySettings);
  
  $('.wysiwyg, .plain-text').on('click', function(event) {
    $('.wysiwyg, .plain-text, .plain-text-editor, .html-editor').toggleClass('hidden');
    $('.rich-text-processor').toggleClass('disabled');
    
    if ($(this).hasClass('wysiwyg')) {
      if (editor === undefined) {
        setupMediumEditor();
      } else {
        editor.activate();
      }
      $.jStorage.set("current-editor", 'wysiwyg');
      $('body').on('click', '.btn-primary', copyTextfield);
    } else {
      copyTextfield();
      editor.deactivate();
      $.jStorage.set("current-editor", 'plain-text');
      $('body').off('click', '.btn-primary', copyTextfield);
    }
    event.preventDefault();
  });
}

$(document).ready(function() {
  setupEditors();
  sectionTabs();
  sectionSort();
  previewFootnote();
  generateEdition();
  
  $('body').popover({
    selector: '[rel="popover"]',
    html:true
  });
});