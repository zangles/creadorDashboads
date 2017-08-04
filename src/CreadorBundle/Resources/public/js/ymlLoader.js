function loadYML(yml){
  clientName = Object.keys(yml)[0];

  $("#clientName").val(clientName);
  client = yml[clientName];

  var dictionary = client.dictionary.es;

  $(client.users.types).each(function(k,v){
    addUser(v, true, dictionary['USER_POSITION_'+v]);
  });


  console.debug(client.metrics.filter);

  var filters = client.metrics.filter;
  Object.keys(filters).forEach(function(k){
    v = filters[k]
    filter.addFilter(k, dictionary[ v ] )
  });

  $.each(dictionary, function (key, value) {
    addTrans(key,value, true);
  });


}



