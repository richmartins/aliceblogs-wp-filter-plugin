jQuery(document).ready(function($){
    get_posts();

    function get_posts(categories = null, studios = null, students = null){
      $.ajax({
            url: url,
            type: "POST",
            data: {
                'action': 'get_posts',
                'categories': categories,
                'roles': studios,
                'users': students
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
            .append($('<input class="checkbox-tools" type="radio" id="year-' + years[index].term_id + '" name="year" value="' + years[index].name + '">'))
            .append($('<label class="for-checkbox-tools" for="year-' + years[index].term_id + '" >' + years[index].name + '</label>'))
            .append($('<br>'))
        }
    });

    $('#aliceblogs-filter-year').change( function() {
        let year_id = $('#aliceblogs-filter-year').find(":checked").attr('id');
        year_id = year_id.replace('year-', '')
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
            $('#aliceblogs-filter-students').empty()
            let degrees = JSON.parse(results)
            for (index in degrees) {
                $('#aliceblogs-filter-degrees')
                .append($('<input class="checkbox-tools" type="radio" id="degree-' + degrees[index].term_taxonomy_id + '" name="degrees" value="' + degrees[index].name + '">'))
                .append($('<label class="for-checkbox-tools" for="degree-' + degrees[index].term_taxonomy_id + '" >' + degrees[index].name + '</label>'))
                .append($('<br>'))
            }
        });
    });

    $('#aliceblogs-filter-degrees').change(function () {
        let degree_id = $('#aliceblogs-filter-degrees').find(":checked").attr('id');
        degree_id = degree_id.replace('degree-', '')
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
            $('#aliceblogs-filter-students').empty()
            let elements = JSON.parse(results)
            for (index in elements) {
                $('#aliceblogs-filter-elements')
                .append($('<input class="checkbox-tools" type="checkbox" id="element-' + elements[index].term_taxonomy_id + '" name="elements" value="' + elements[index].name + '">'))
                .append($('<label class="for-checkbox-tools" for="element-' + elements[index].term_taxonomy_id + '" >' + elements[index].name + '</label>'))
                .append($('<br>'))
            }
        });
    })

    $('#aliceblogs-filter-elements').change(function () {
      let degree_id = $('#aliceblogs-filter-degrees').find(":checked").attr('id');
      degree_id = degree_id.replace('degree-', '')
      let elements_ids = $("#aliceblogs-filter-elements>input:checkbox:checked").map(function(){
        return $(this).attr('id').replace('element-', '');
      }).get();
      $('#aliceblogs-filter-studios').empty()
      $('#aliceblogs-filter-students').empty()
      if (elements_ids.length === 0) {
        get_posts(degree_id)
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
                .append($('<input class="checkbox-tools" type="checkbox" id="studio-' + index + '" name="studios" value="' + studios[index] + '">'))
                .append($('<label class="for-checkbox-tools" for="studio-' + index + '" >' + studios[index] + '</label>'))
                .append($('<br>'))
            }
        });
      }
    })

    $('#aliceblogs-filter-studios').change(function () {
      let elements_ids = $("#aliceblogs-filter-elements>input:checkbox:checked").map(function(){
        return $(this).attr('id').replace('element-', '');
      }).get();
      let studios_names = $("#aliceblogs-filter-studios>input:checkbox:checked").map(function(){
        return $(this).attr('id').replace('studio-', '');
      }).get();

      if (studios_names.length === 0) {
        get_posts(elements_ids)
        $('#aliceblogs-filter-students').empty()
      } else {
        get_posts(elements_ids, studios_names)
        
        $.ajax({
          url: url,
          type: "POST",
          data: {
            'action': 'get_students',
            'studios_names': studios_names
          }
        }).done(function(results) {
          $('#aliceblogs-filter-students').empty()
            let students = JSON.parse(results)
            for (index in students) {
                $('#aliceblogs-filter-students')
                .append($('<input class="checkbox-tools" type="checkbox" id="student-' + index + '" name="students" value="' + students[index] + '">'))
                .append($('<label class="for-checkbox-tools" for="student-' + index + '" >' + students[index] + '</label>'))
                .append($('<br>'))
            }
        });
        
      }
    })
    $('#aliceblogs-filter-students').change(function () { 

      //preparing data filters
      let students_ids = $("#aliceblogs-filter-students>input:checkbox:checked").map(function () {
        return $(this).attr('id').replace('student-', '');
      }).get(); 

      let studios_names = $("#aliceblogs-filter-studios>input:checkbox:checked").map(function () {
        return $(this).attr('id').replace('studio-', '');
      }).get();

      let elements_ids = $("#aliceblogs-filter-elements>input:checkbox:checked").map(function () {
        return $(this).attr('id').replace('element-', '');
      }).get();

      if (students_ids.length === 0) {
        get_posts(elements_ids, studios_names)
      } else {
        studios_names = null
        get_posts(elements_ids, studios_names, students_ids)
      }
    })
});