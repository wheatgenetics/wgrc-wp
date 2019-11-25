function getBaseUrl() {
  var re = new RegExp(/^.*\//);
  return re.exec(window.location.href);
}

jQuery(document).ready(function($) {
  $( "#table-select" ).change(function() {
    var url = $(this).val(); // get selected value
    
    if (url) { // require a URL
      window.location = getBaseUrl() + '?table=' + url; // redirect
    }

    return false;
  });

  $( "#type-select" ).change(function() {
    var url = $(this).val(); // get selected value
    
    if (url) { // require a URL
      window.location = getBaseUrl() + '?table=genetic_stocks&stock_type=' + url; // redirect
    }

    return false;
  });

  $( "#gene-select" ).change(function() {
    var url = $(this).val(); // get selected value
    
    if (url) { // require a URL
      window.location = getBaseUrl() + '?table=genetic_stocks&genes=' + url; // redirect
    }

    return false;
  });

  $( "#chromosome-select" ).change(function() {
    var url = $(this).val(); // get selected value
    
    if (url) { // require a URL
      window.location = getBaseUrl() + '?table=genetic_stocks&chromosome_of_interest=' + url; // redirect
    }

    return false;
  });

  $( "#genus-select" ).change(function() {
    var url = $(this).val(); // get selected value
    
    if (url) { // require a URL
      window.location = getBaseUrl() + '?table=germplasm&genus=' + url; // redirect
    }

    return false;
  });

  $( "#species-select" ).change(function() {
    var url = $(this).val(); // get selected value
    
    if (url) { // require a URL
      window.location = getBaseUrl() + '?table=germplasm&species=' + url; // redirect
    }

    return false;
  });

  $( "#subtaxa-select" ).change(function() {
    var url = $(this).val(); // get selected value
    
    if (url) { // require a URL
      window.location = getBaseUrl() + '?table=germplasm&subtaxa=' + url; // redirect
    }

    return false;
  });
})