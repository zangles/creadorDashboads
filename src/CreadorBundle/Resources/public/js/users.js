$("body").on('click', '.deleteUser', function(){
  var parent = $(this).parents('.input-group');
  deleteTrans($(this).data('text'));
  $(parent).remove();

});

function addUser(userPositionName, removable, locale) {
  removable = removable !== false;
  locale = locale || '';

  var $body = $('.usersTypes');

  var $html =
    '<div class="input-group">\n' +
    '        <input type="text" class="form-control" name="usertypes[]" value="'+userPositionName+'">\n' +
    '        <span class="input-group-btn">\n';


  if (removable === true) {
    $html +='<button class="btn btn-danger deleteUser" data-text="'+userPositionName+'" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>\n';
  } else {
    $html +='<button class="btn btn-danger deleteUser" data-text="'+userPositionName+'" type="button" disabled="disabled"><i class="fa fa-trash" aria-hidden="true"></i></button>\n';
  }

  $html += '        </span>\n' +
    '</div>';

  $body.append($html);
  addUserPositionTrans(userPositionName, locale);
}

$("#addUserBtn").click(function(){
  var userPositionName = $('#userPositionName').val();

  addUser(userPositionName);
});
