document.observe('dom:loaded', function() {
  $('cartview').observe('mouseover', function(e) {
    $('cartview-panel').show();
  });
  $('cartview').observe('mouseout', function(e) {
    $('cartview-panel').hide();
  });
});