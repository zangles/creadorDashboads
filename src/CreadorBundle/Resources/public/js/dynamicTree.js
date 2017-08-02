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
  $($node).append('<div class="item list-group-item" id="'+text+'">'+text+'<button class="btn btn-danger removeItem" data-text="'+text+'"><i class="fa fa-trash" aria-hidden="true"></i></button></div>');
  addHierarchyTrans(text);
}

$("#addChildNode").click(function(){
  var node = $(".hierarchyTree").find('.active');
  if (node.length > 0) {
    addItem($(node), $("#node-name").val());
  }
});

$("#addBaseNode").click(function(){
  $(".hierarchyTree").find('.item').removeClass('active');

  addItem($(".hierarchyTree"), $("#node-name").val());
  console.debug($("#"+$("#node-name").val()));
  $("#"+$("#node-name").val()).addClass('active');

});

$("#getStructure").click(function(){
  console.debug(getStructure());
});

function getStructure() {
  var $body = $('.hierarchyTree');
  var tree = getItems($body);
  return JSON.stringify(tree, null, 4);
}

$("body").on('click', '.item', function(){
  $(".hierarchyTree").find('.item').removeClass('active');
  $(this).addClass('active')
});

$("body").on('click', '.removeItem', function(){
  deleteTrans($(this).data('text'));
  $(this).parent().remove();
});



