jQuery(document).ready(function($){
    get_posts();

    function get_posts(categories = null, studios = null){
      $.ajax({
            url: url,
            type: "POST",
            data: {
                'action': 'get_posts',
                'categories': categories,
                'roles': studios
            },
            beforeSend: function() {
              loader()
            }
      }).done(function (results) {
            $('#aliceblogs-carddeck').empty()
            let posts = JSON.parse(results)
            html = ''
            for (index in posts) {
              html += '<div class="aliceblogs-card">'
              html += '<a href="' + posts[index].url + '">'
              html += '<div class="hvrbox">'
              html += '<img alt="'+ posts[index].title +'" class="hvrbox-layer_bottom" src="'+ posts[index].thumbnail + '" />'
              html += '<div class="hvrbox-layer_top">'
              html += '<div class="hvrbox-text">'+ posts[index].title +'</div>'
              html += '</div></div></div></a></div>'
            }
            
            $('#aliceblogs-carddeck').html(html) 
      });
    }

    function loader() {
      $('#aliceblogs-carddeck').html('<div id="container-loader"><div class="loader"></div></div>')
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
            get_posts(year_id);
            $('#aliceblogs-filter-degrees').empty()
            $('#aliceblogs-filter-elements').empty()
            $('#aliceblogs-filter-studios').empty()
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
            get_posts(degree_id);
            $('#aliceblogs-filter-elements').empty()
            $('#aliceblogs-filter-studios').empty()
            let elements = JSON.parse(results)
            for (index in elements) {
                $('#aliceblogs-filter-elements')
                .append($('<input type="checkbox" id="' + elements[index].term_taxonomy_id + '" name="elements" value="' + elements[index].name + '">'))
                .append($('<label for="' + elements[index].term_taxonomy_id + '" >' + elements[index].name + '</label>'))
                .append($('<br>'))
            }
        });
    })

    $('#aliceblogs-filter-elements').change(function () {
      let degree_id = $('#aliceblogs-filter-degrees').find(":checked").attr('id');
      let elements_ids = $("#aliceblogs-filter-elements>input:checkbox:checked").map(function(){
        return $(this).attr('id');
      }).get();
      if (elements_ids.length === 0) {
        get_posts(degree_id)
        $('#aliceblogs-filter-studios').empty()
      } else {
        get_posts(elements_ids)
        
        $.ajax({
          url: url,
          type: "POST",
          data: {
            'action': 'get_studios',
            'elements_ids': elements_ids
          }
        }).done(function(results) {
          $('#aliceblogs-filter-studios').empty()
            let studios = JSON.parse(results)
            for (index in studios) {
                $('#aliceblogs-filter-studios')
                .append($('<input type="checkbox" id="' + index + '" name="studios" value="' + studios[index] + '">'))
                .append($('<label for="' + index + '" >' + studios[index] + '</label>'))
                .append($('<br>'))
            }
        });
      }
    })

    $('#aliceblogs-filter-studios').change(function () {
      let degree_id = $('#aliceblogs-filter-degrees').find(":checked").attr('id');
      let elements_ids = $("#aliceblogs-filter-elements>input:checkbox:checked").map(function(){
        return $(this).attr('id');
      }).get();
      let studios_ids = $("#aliceblogs-filter-studios>input:checkbox:checked").map(function(){
        return $(this).attr('id');
      }).get();

      if (studios_ids.length === 0) {
        get_posts(elements_ids)
        //$('#aliceblogs-filter-studios').empty()
      } else {
        get_posts(elements_ids, studios_ids)
        /*
        $.ajax({
          url: url,
          type: "POST",
          data: {
            'action': 'get_studios',
            'elements_ids': elements_ids
          }
        }).done(function(results) {
          $('#aliceblogs-filter-studios').empty()
            let studios = JSON.parse(results)
            for (index in studios) {
                $('#aliceblogs-filter-studios')
                .append($('<input type="checkbox" id="' + index + '" name="studios" value="' + studios[index] + '">'))
                .append($('<label for="' + index + '" >' + studios[index] + '</label>'))
                .append($('<br>'))
            }
        });
        */
      }
    })
});