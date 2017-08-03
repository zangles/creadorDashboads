function loadYML(yml){
  clientName = Object.keys(yml)[0];

  $("#clientName").val(clientName);
  client = yml[clientName];

  var dictionary = client.dictionary.es;

  $(client.users.types).each(function(k,v){
    addUser(v, true, dictionary['USER_POSITION_'+v]);
  });

  $(client.metrics.filter).each(function(k,v){
    var filterKeys = Object.keys(v);
    var filterValues = Object.values(v);
    filter.addFilter(filterKeys[0], dictionary[ filterValues[0] ] )
  });

  $.each(dictionary, function (key, value) {
    addTrans(key,value, true);
  });


}



