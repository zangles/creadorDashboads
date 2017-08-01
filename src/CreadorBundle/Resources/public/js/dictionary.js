function addTrans(key,value,preexistente) {
  value = value || '';
  preexistente = preexistente || false;

  if(preexistente) {
    $body = $('.preexistente');
  } else {
    $body = $('.dictionary');
  }

  addTransForm($body,key,key,value);
}

function addHierarchyTrans(key) {
  $body = $('.dictionary');
  addTransForm($body,key,'METRIC_'+key,'');
}

function addUserPositionTrans(key, value) {
  value = value || '';
  $body = $('.dictionary');
  addTransForm($body,key,'USER_POSITION_'+key,value);
}

function addTransForm($body,key,label,value) {
  $body.append(
    '<div class="form-group" id="trans_'+key+'">' +
    '    <label for="inputEmail3" class="col-sm-2 control-label">'+label+'</label>\n' +
    '    <div class="col-sm-10">\n' +
    '      <div class="input-group">\n' +
    '        <input type="text" class="form-control" id="inputEmail3" name="trans_'+key+'" value="'+value+'">\n' +
    '        <span class="input-group-btn">\n' +
    '          <button class="btn btn-danger deleteTrans" data-key="'+key+'" type="button">X</button>\n' +
    '        </span>\n' +
    '      </div>\n' +
    '    </div>\n' +
    '</div>'
  );
}

$("body").on('click', '.deleteTrans', function(){
  var key = $(this).data('key');
  deleteTrans(key);
});

function deleteTrans(key) {
  $("#trans_"+key).remove();
}

$("#addTrans").click(function(){
  var transName = $("#trans-name").val();
  if (transName != '') {
    addTrans(transName,'');
  }
});