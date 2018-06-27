// Construct the suggestion engine, Bloodhound
// Use both prefetch and remote data from GDS
$(document).ready(function() {
  var products = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('Matches'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: 'calldatastore.php?searchtext=%QUERY',
    wilcard: '%QUERY'
  });

  products.initialize();
  // Use a minimum character length, 3, before suggestions start...
  // highlight the suggestion...
  // limit the number of suggestions, 10
  $('#productsuggest .typeahead').typeahead({
    minLength: 3,
    highlight: true
  }, {
    name: 'product-name',
    display: 'productsuggest',
    limit: 10,
    templates: {
      suggestion: function(data) {
        return '<div>' + data.name + '</div>';
      }
    },
    source: products
  });
});
