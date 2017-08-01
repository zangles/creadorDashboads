function getItems($root, root) {
  root = root || {};
  var $children = $root.children('.item');

  if($children.length) {
    $children.each(function() {
      var id = $(this).attr('id');
      root[id] = getItems($(this), root[id]);
    });
    return root;
  } else {
    return $root.attr('id');
  }
}

function addItem($node,text) {
  $($node).append('<div class="item list-group-item" id="'+text+'">'+text+'<button class="btn btn-danger removeItem" data-text="'+text+'">X</button></div>');
  addHierarchyTrans(text);
}

$("#addChildNode").click(function(){
  var parent = $(this).parents('.list-group');
  var node = $(parent).find('.active');
  if (node.length > 0) {
    addItem($(node), $("#node-name").val());
  }
});

$("#addBaseNode").click(function(){

  $('.list-group').find('.item').removeClass('active');


  var parent = $(this).parents('.list-group');
  addItem($(parent), $("#node-name").val());
  $("#"+$("#node-name").val()).addClass('active');

});

$("#getStructure").click(function(){
  console.debug(getStructure());
});

function getStructure() {
  var $body = $('.tree');
  var tree = getItems($body);
  return JSON.stringify(tree, null, 4);
}

$("body").on('click', '.item', function(){
  var parent = $(this).parents('.list-group');
  $(parent).find('.item').removeClass('active');
  $(this).addClass('active')
});

$("body").on('click', '.removeItem', function(){
  deleteTrans($(this).data('text'));
  $(this).parent().remove();
});



