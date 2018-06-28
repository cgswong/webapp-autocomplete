// Construct the suggestion engine, Bloodhound, using remote data from GDS
$(document).ready(function() {
  var products = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace(),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: '/getproduct.php?searchtext=%QUERY',
      wilcard: '%QUERY'
    }
  });

  products.initialize();

  // Use a minimum character length, 3, before suggestions start...
  // highlight the suggestion...
  // limit the number of suggestions, 10
  $('#productsuggest').typeahead({
    minLength: 3,
    highlight: true
  }, {
    name: 'productsuggest',
    display: 'productsuggest',
    limit: 10,
    source: function(query, process) {
      return $.get('/getproduct.php?searchtext=' + query, function(data) {
        return process(data);
      });
    }
    //source: products
  });
});
