// Construct the suggestion engine, Bloodhound
// Use both prefetch and remote data from GDS
$(document).ready(function() {
  var products = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    local: 'calldatastore.php'
  });

  // Use a minimum character length, 3, before suggestions start...
  // highlight the suggestion...
  // limit the number of suggestions, 10
  $('#productsuggest .typeahead').typeahead({
    minLength: 3,
    highlight: true
  }, {
    name: 'product-entry',
    display: 'name',
    limit: 10,
    source: products
  });
});
