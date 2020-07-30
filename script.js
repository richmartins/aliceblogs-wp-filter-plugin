jQuery(document).ready(function($){

    $.ajax({
        url: url,
        type: "POST",
        data: {
          'action': 'get_years'
        }
      }).done(function(results) {
        let years = JSON.parse(results)
        for (index in years) {
            console.log(years[index])
            $('#aliceblogs-filter-year')
            .append($('<input type="radio" id="' + years[index].term_id + '" name="year" value="' + years[index].name + '">'))
            .append($('<label for="' + years[index].term_id + '" >' + years[index].name + '</label>'))
            .append($('<br>'))
        }
        /*
            $('#aliceblogs-filter-year')
            .append($('<input type="radio" id="' + 2020 + '" name="year" value="' + 2020 + '">'))
            .append($('<label for="' + 2020 + '" >' + 2020 + '</label>'))*/
      });

      $("#aliceblogs-filter-year>input[type='radio']").on('change', function() {
        alert("OK"); 
     });
    $('#aliceblogs-filter-year').change( function() {        
        let year = $('#aliceblogs-filter-year').find(":checked").text();
        console.log("year")        
        $.ajax({
          url: url,
          type: "POST",
          data: {
            'action': 'get_categories',
            'year': year
          }
        }).done(function(results) {
            $('#aliceblogs-filter-categories').empty()
            let categories = JSON.parse(results)
            for (index in categories) {
                console.log(categories[index])
                $('#aliceblogs-filter-categories')
                .append($('<option>', {value: categories[index].term_taxonomy_id, text: categories[index].name}))
            }
            $('#aliceblogs-filter-categories').show();
        });
        
    });
});