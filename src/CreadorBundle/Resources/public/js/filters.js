var filter = {
  init: function() {
    var self = this;

    $("body").on('click', '.deleteFilter', function(){
      var parent = $(this).parents('.input-group');
      deleteTrans($(this).data('text'));
      $(parent).remove();
    });

    $("#addFilterBtn").click(function(){
      var filterName = $('#filterName').val();
      self.addFilter(filterName);
    });
  },
  addFilter: function(filterName, locale) {
    locale = locale || '';
    var $body = $('.filters');

    $body.append('<div class="input-group">\n' +
      '        <input type="text" class="form-control" name="filters[]" value="'+filterName+'">\n' +
      '        <span class="input-group-btn">\n' +
      '            <button class="btn btn-danger deleteFilter" data-text="'+filterName+'" type="button"><i class="fa fa-trash" aria-hidden="true"></i>\n</button>\n' +
      '        </span>\n' +
      '    </div>');

    addFilterTrans(filterName, locale);
  }
};