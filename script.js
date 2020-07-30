jQuery(document).ready(function($){
    
    function get_posts(categories = null){
      $.ajax({
            url: url,
            type: "POST",
            data: {
                'action': 'get_posts',
                'categories': categories
            }
      }).done(function (results) {
            $('#aliceblogs-carddeck').empty()
            let posts = JSON.parse(results)
            for (index in posts) {
                $('#aliceblogs-carddeck')
                .append($('<p>' + posts[index].title + '<p>'))
                .append($('<br>'))
            }
      });
    }

    $.ajax({
        url: url,
        type: "POST",
        data: {
            'action': 'get_years'
        }
        }).done(function(results) {
        let years = JSON.parse(results)
        for (index in years) {
            $('#aliceblogs-filter-year')
            .append($('<input type="radio" id="' + years[index].term_id + '" name="year" value="' + years[index].name + '">'))
            .append($('<label for="' + years[index].term_id + '" >' + years[index].name + '</label>'))
            .append($('<br>'))
        }
    });

    $('#aliceblogs-filter-year').change( function() {        
        let year_id = $('#aliceblogs-filter-year').find(":checked").attr('id');
        $.ajax({
          url: url,
          type: "POST",
          data: {
            'action': 'get_degrees',
            'year_id': year_id
          }
        }).done(function(results) {
            console.log(year_id)
            get_posts(year_id);
            $('#aliceblogs-filter-degrees').empty()
            $('#aliceblogs-filter-categories').empty()
            let degrees = JSON.parse(results)
            for (index in degrees) {
                $('#aliceblogs-filter-degrees')
                .append($('<input type="radio" id="' + degrees[index].term_taxonomy_id + '" name="degrees" value="' + degrees[index].name + '">'))
                .append($('<label for="' + degrees[index].term_taxonomy_id + '" >' + degrees[index].name + '</label>'))
                .append($('<br>'))
            }
        });
    });

    $('#aliceblogs-filter-degrees').change(function () {
        let degree_id = $('#aliceblogs-filter-degrees').find(":checked").attr('id');
        $.ajax({
          url: url,
          type: "POST",
          data: {
            'action': 'get_categories',
            'degree_id': degree_id
          }
        }).done(function(results) {
            $('#aliceblogs-filter-categories').empty()
            let categories = JSON.parse(results)
            for (index in categories) {
                $('#aliceblogs-filter-categories')
                .append($('<input type="checkbox" id="' + categories[index].term_taxonomy_id + '" name="categories" value="' + categories[index].name + '">'))
                .append($('<label for="' + categories[index].term_taxonomy_id + '" >' + categories[index].name + '</label>'))
                .append($('<br>'))
            }
        });
    })
});