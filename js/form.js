jQuery(document).ready(function($) {
  // $( "#table-select" ).change(function() {
  //   if ($(this).val()==="genetic_stocks") {
  //     $("#genetic-stocks-form-section").show()
  //   } else {
  //     $("#germplasm-form-section").hide()
  //   }
  // });
  $( "#table-select" ).change(function() {
    var url = $(this).val(); // get selected value
    if (url) { // require a URL
        window.location = 'http://wgrc.local?table=' + url; // redirect
    }
    return false;
    // var url = $(this).val(); // get selected value
    
    // if (url) { // require a URL
    //     window.location = url; // redirect
    // }
    // return false;
    // var your_selected_value = $('#table-select option:selected').val();
    // console.log(your_selected_value);
    // $.ajax({
    //     type: "POST",
    //     url: "http://wgrc.local/",
    //     data: {duh:your_selected_value},
    //     success: function(data) {
    //         // if (data.selected == "germplasm") {
    //           var content = $( data ).find( "#content" );
    //             console.log(data.duh);
    //         // } else {
    //           // console.log(data);            }
    //     }

    // $.post( "http://wgrc.local", { name: "John", time: "2pm" })
    //   .done(function( data ) {
    //     alert( "Data Loaded: " + data );
    // });
  });


    
})