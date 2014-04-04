function insertMarkdownAtCaret() {
  $('#insert-rich-text').on('click', function() {
    //This is not foolproof
    window.setTimeout(function() {
      var markdown = $('#rich-text-processed').val();
      $('textarea.text-editor:visible').insertAtCaret(markdown);
      $('#rich-text-processor').modal('hide');
    }, 100);
  });
  
  $('#rich-text-processor').on('hidden.bs.modal', function () {
    $('#modal-toggles a').removeClass('disabled');
    $('#rich-text-preprocessor, #rich-text-processed').text('');
  });
}

$(document).ready(function() {
  insertMarkdownAtCaret();
});