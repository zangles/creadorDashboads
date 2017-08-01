$("body").on('click', '.deleteFilter', function(){
  var parent = $(this).parents('.input-group');
  deleteTrans($(this).data('text'));
  $(parent).remove();
});

function addFilter(filterName) {
  var $body = $('.filters');



  $body.append('<div class="input-group">\n' +
    '        <input type="text" class="form-control" name="filters[]" value="'+filterName+'">\n' +
    '        <span class="input-group-btn">\n' +
    '            <button class="btn btn-danger deleteFilter" data-text="'+filterName+'" type="button">X</button>\n' +
    '        </span>\n' +
    '    </div>');
}



$("#addFilterBtn").click(function(){
  var filterName = $('#filterName').val();

  addFilter(filterName);
  addFilterTrans(filterName)
});