$("body").on('click', '.deleteUser', function(){
  var parent = $(this).parents('.input-group');
  deleteTrans($(this).data('text'));
  $(parent).remove();

});

function addUser(userPositionName) {
  var $body = $('.usersTypes');



  $body.append('<div class="input-group">\n' +
    '        <input type="text" class="form-control" name="usertypes[]" value="'+userPositionName+'">\n' +
    '        <span class="input-group-btn">\n' +
    '            <button class="btn btn-danger deleteUser" data-text="'+userPositionName+'" type="button"><i class="fa fa-trash" aria-hidden="true"></i>\n</button>\n' +
    '        </span>\n' +
    '    </div>');
}



$("#addUserBtn").click(function(){
  var userPositionName = $('#userPositionName').val();

  addUser(userPositionName);
  addUserPositionTrans(userPositionName)
});

addUserPositionTrans('REGION', 'Region');
addUserPositionTrans('COUNTRY', 'Pais');
addUserPositionTrans('STORE', 'Tienda');
addUserPositionTrans('NON_HIERARCHICAL', 'NON_HIERARCHICAL');