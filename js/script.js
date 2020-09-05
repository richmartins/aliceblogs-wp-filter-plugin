jQuery(document).ready(function($){
    let view_options = 'card'
    let posts = ''

    $('#aliceblogs-filter-degrees-title').hide()
    $('#aliceblogs-filter-elements-title').hide()
    $('#aliceblogs-filter-medias-title').hide()
    $('#aliceblogs-filter-studios-title').hide()
    $('#aliceblogs-filter-students-title').hide()

    /**
     * Empty search field when clicked on browser back to last page button
     */
    $(window).bind("pageshow", function() {
      let searchParams = new URLSearchParams(window.location.search)
      if (searchParams.get('q') == null) {
        $('#aliceblogs-searchbar').val('')
      } else {
        $('#aliceblogs-searchbar').val(searchParams.get('q'))
        $('#aliceblogs-filter').hide()
      }
    })

    $('#aliceblogs-view-mosaic').click( function() {
      if(!$('#aliceblogs-view-list').hasClass("aliceblogs-view-active")) {
        $('#aliceblogs-view-mosaic').removeClass("aliceblogs-view-active")
        $('#aliceblogs-view-list').addClass("aliceblogs-view-active")
        view_options = 'card'
        render_posts();
      }
    })

    $('#aliceblogs-view-list').click( function() {
      if(!$('#aliceblogs-view-mosaic').hasClass("aliceblogs-view-active")) {
        $('#aliceblogs-view-mosaic').addClass("aliceblogs-view-active")
        $('#aliceblogs-view-list').removeClass("aliceblogs-view-active")
        view_options = 'list'
        render_posts();
      }
    })

    function render_posts(){
      $('#aliceblogs-carddeck').empty()
      $('#aliceblogs-listdeck').empty()
      $('#aliceblogs-nocard').empty()
      html = ''
      if (posts === null || posts.length === 0) {
        html += '<h3>Aucun article n\'a été trouvé</h3>'
        $('#aliceblogs-nocard').html(html)
      } else {
        for (index in posts) {
          if (view_options == 'card') {
            html += '<a class="aliceblogs-card animate__animated animate__fadeIn" href="' + posts[index].url + '">'
            html += '<div class="aliceblogs-card-container">'
            html += '<img alt="'+ posts[index].title +'" class="aliceblogs-card-img" src="'+ posts[index].thumbnail + '" />'
            html += '<h4 class="aliceblogs-card-text">'+ posts[index].title +'</h4>'
            html += '</div>'
            html += '</a>'
          } else if (view_options == 'list') {
            html += '<div class="animate__animated animate__fadeIn aliceblogs-list">'
            html += '<div class="aliceblogs-list-title"><h1><a href="' + posts[index].url + '">' + posts[index].title + '</a></h1></div>'
            html += '<div class="aliceblogs-list-subtitle"><h6>par ' + posts[index].author + ' | ' + posts[index].date + '</h6></div>'
            html += '<div class="aliceblogs-list-content">' + posts[index].content + '</div>'
            html += '<div class="aliceblogs-list-footer"></div>'
            html += '</div>'
          }
        }
        
        if(view_options == 'card'){
          $('#aliceblogs-carddeck').html(html)
          reorder_mansory_layout_carddeck();
        } else if (view_options == 'list'){
          $('#aliceblogs-listdeck').html(html)
        }
      }
    }

    /**
     * Toggle loader on/off
     */
    function toggle_loader() {
      if ($('#container-loader').html() == '') {
        $('#container-loader').html('<div class="loader"></div>')
        $('#aliceblogs-carddeck').empty()
        $('#aliceblogs-nocard').empty()
      } else {
        $('#container-loader').empty()
      }
    }

    function get_posts(categories = null, medias = null, studios = null, students = null){
      $.ajax({
            url: url,
            type: "POST",
            data: {
                'action': 'get_posts',
                'categories': categories,
                'medias': medias,
                'roles': studios,
                'students': students
            },
            beforeSend: function() {
              toggle_loader()
            }
      }).done(function (results) {
          posts = JSON.parse(results)
          toggle_loader()
          render_posts()  
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
          .append($('<input class="checkbox-tools" type="radio" id="year-' + years[index].term_id + '" name="year" value="' + years[index].name + '">'))
          .append($('<label class="for-checkbox-tools" for="year-' + years[index].term_id + '" >' + years[index].name + '</label>'))
        }

        // auto select current school year if exist 
        let current_school_year = ''
        let current_year = new Date().getFullYear().toString().substr(-2)

        // Check if current period is september to december or january to july
        if ((new Date()).getMonth() >= 8) {
          // 1st semester of the school year : current year - next year
          let next_year = (new Date().getFullYear()+1).toString().substr(-2)
          current_school_year = current_year + '-' + next_year
        } else {
          // 2nd semester of the school year : last year - current year
          let last_year = (new Date().getFullYear()+1).toString().substr(-1)
          current_school_year = last_year + '-' + current_year
        }
        selected_year_exist = years.find(year => year.name == current_school_year)

        // Auto call get_degrees
        if (selected_year_exist) {
          $('#year-' + selected_year_exist.term_id).attr('checked','checked')
          get_degrees()
        } else {
          get_posts()
        }
    });

    $.ajax({
      url: url,
      type: "POST",
      data: {
          'action': 'get_most_used_tags'
      }
      }).done(function(results) {
        let tags = JSON.parse(results)
        html = ''
        // html += '<p id="aliceblogs-searchbar-proposal-title">Trends : </p>'
        for(index in tags) {
          html += '<div class="proposal-tag">' + tags[index] + '</div>'
        }
        $('#aliceblogs-searchbar-proposal').html(html)
    });

    function get_degrees() {
      let year_id = $('#aliceblogs-filter-year').find(":checked").attr('id').replace('year-', '')
      $.ajax({
        url: url,
        type: "POST",
        data: {
          'action': 'get_degrees',
          'year_id': year_id
        }
      }).done(function(results) {
          if ($('#aliceblogs-searchbar').val() != '') {
            search_posts()
          } else {
            get_posts(year_id);
          }

          $('#aliceblogs-filter-degrees').empty()
          $('#aliceblogs-filter-elements').empty()
          $('#aliceblogs-filter-studios').empty()
          $('#aliceblogs-filter-students').empty()
          $('#aliceblogs-filter-medias').empty()
          $('#aliceblogs-filter-degrees-title').show()
          $('#aliceblogs-filter-elements-title').hide()
          $('#aliceblogs-filter-studios-title').hide()
          $('#aliceblogs-filter-students-title').hide()
          $('#aliceblogs-filter-medias-title').hide()

          let degrees = JSON.parse(results)
          for (index in degrees) {
              $('#aliceblogs-filter-degrees')
              .append($('<input class="checkbox-tools" type="radio" id="degree-' + degrees[index].term_taxonomy_id + '" name="degrees" value="' + degrees[index].name + '">'))
              .append($('<label class="for-checkbox-tools" for="degree-' + degrees[index].term_taxonomy_id + '" >' + degrees[index].name + '</label>'))
          }
      });
    }

    $('#aliceblogs-filter-year').change( function() {
      get_degrees()
    });

    $('#aliceblogs-filter-degrees').change(function () {
        let degree_id = $('#aliceblogs-filter-degrees').find(":checked").attr('id').replace('degree-', '');
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
            $('#aliceblogs-filter-medias').empty()
            $('#aliceblogs-filter-elements-title').show()
            $('#aliceblogs-filter-studios-title').hide()
            $('#aliceblogs-filter-students-title').hide()
            $('#aliceblogs-filter-medias-title').hide()

            let elements = JSON.parse(results)
            for (index in elements) {
                $('#aliceblogs-filter-elements')
                .append($('<input class="checkbox-tools" type="checkbox" id="element-' + elements[index].term_taxonomy_id + '" name="elements" value="' + elements[index].name + '">'))
                .append($('<label class="for-checkbox-tools" for="element-' + elements[index].term_taxonomy_id + '" >' + elements[index].name + '</label>'))
            }
            
            // Add All button
            addAllBtn($('#aliceblogs-filter-elements'));
        });
    }) 

    $('#aliceblogs-filter-elements').change(function () {
      let degree_id = $('#aliceblogs-filter-degrees').find(":checked").attr('id').replace('degree-', '')
      let elements_ids = $("#aliceblogs-filter-elements>input:checkbox:checked").map(function(){
        return $(this).attr('id').replace('element-', '');
      }).get();

      if (elements_ids.length === 0) {
        get_posts(degree_id)
        $('#aliceblogs-filter-studios-title').hide()
        $('#aliceblogs-filter-studios').empty() 
        $('#aliceblogs-filter-medias-title').hide()
        $('#aliceblogs-filter-medias').empty()
        $('#aliceblogs-filter-students').empty()
        $('#aliceblogs-filter-students-title').hide()
      } else {
        get_posts(elements_ids)
        $('#aliceblogs-filter-medias-title').show()

        $.ajax({
          url: url,
          type: "POST",
          data: {
            'action': 'get_medias',
            'elements': elements_ids
          }
        }).done(function(results) {
          $('#aliceblogs-filter-medias').empty()
          $('#aliceblogs-filter-studios').empty()
          $('#aliceblogs-filter-students').empty()
          $('#aliceblogs-filter-studios-title').hide()
          $('#aliceblogs-filter-students-title').hide()
          let medias = JSON.parse(results)
          let id = 0
          for (index in medias) {
            $('#aliceblogs-filter-medias')
              .append($('<input class="checkbox-tools" type="checkbox" data-id="' + Object.keys(medias[index]) + '" id="media-' + id + '" value="' + id + '">'))
              .append($('<label class="for-checkbox-tools" for="media-' + id + '" >' + index + '</label>'))
            id++
          }

          adjust_div_width('#aliceblogs-filter-medias')
          
          // Add All button
          addAllBtn($('#aliceblogs-filter-medias'));
        });
      }
    })

    // Format all ids in array
    function build_medias_ids_list() {
      let medias_ids = $("#aliceblogs-filter-medias>input:checkbox:checked").map(function(){
        return $(this).data('id');
      }).get();
      let medias_ids_formatted = []
      for (const medias of medias_ids) {
        if (typeof medias === 'string') {
            for (const media of medias.split(',')) {
              medias_ids_formatted.push(parseInt(media))
            }
        } else {
          medias_ids_formatted.push(parseInt(medias))
        }
      }
      return medias_ids_formatted
    }

    $('#aliceblogs-filter-medias').change(function () {
      let degree_id = $('#aliceblogs-filter-degrees').find(":checked").attr('id').replace('degree-', '')
      let elements_ids = $("#aliceblogs-filter-elements>input:checkbox:checked").map(function(){
        return $(this).attr('id').replace('element-', '');
      }).get();

      if (elements_ids.length === 0) {
        get_posts(degree_id)
        $('#aliceblogs-filter-studios').empty()
      } else if (build_medias_ids_list().length === 0) {
        get_posts(elements_ids)
        $('#aliceblogs-filter-studios-title').hide()
        $('#aliceblogs-filter-studios').empty()
      } else {
        get_posts(elements_ids, build_medias_ids_list())
        
        $.ajax({
          url: url,
          type: "POST",
          data: {
            'action': 'get_studios',
            'elements_ids': elements_ids,
            'medias_ids': build_medias_ids_list()
          }
        }).done(function(results) {
          $('#aliceblogs-filter-studios').empty()
          $('#aliceblogs-filter-students').empty()
          $('#aliceblogs-filter-studios-title').show()
          $('#aliceblogs-filter-students-title').hide()
            let studios = JSON.parse(results)
            for (index in studios) {

                $('#aliceblogs-filter-studios')
                .append($('<input class="checkbox-tools" type="checkbox" id="studio-' + index + '" name="studios" value="' + studios[index] + '">'))
                  .append($('<label class="for-checkbox-tools" for="studio-' + index + '" >' + studios[index].replace(/studio/gi, '') + '</label>'))
            }

            adjust_div_width('#aliceblogs-filter-studios')

            // Add All button
            addAllBtn($('#aliceblogs-filter-studios'));
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
        get_posts(elements_ids, build_medias_ids_list())
        $('#aliceblogs-filter-students').empty()
        $('#aliceblogs-filter-students-title').hide()
      } else {
        get_posts(elements_ids, build_medias_ids_list(), studios_names)
        
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
          $('#aliceblogs-filter-students-title').show()
          for (index in students) {
              $('#aliceblogs-filter-students')
              .append($('<input class="checkbox-tools" type="checkbox" id="student-' + index + '" name="students" value="' + students[index] + '">'))
              .append($('<label class="for-checkbox-tools" for="student-' + index + '" >' + students[index] + '</label>'))
          }

          adjust_div_width('#aliceblogs-filter-students')

          // Add All button
          addAllBtn($('#aliceblogs-filter-students'));
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
        get_posts(elements_ids, build_medias_ids_list(), studios_names)
      } else {
        get_posts(elements_ids, build_medias_ids_list(), null, students_ids)
      }
    })

    /**
     * Searchbar - Query search after typing
     */
    var delay = (function(){
      var timer = 0;
      return function(callback, ms){
      clearTimeout (timer);
      timer = setTimeout(callback, ms);
     };
    })();

    function addAllBtn(element){
      if (element.children().length > 0){
        element.append($('<label class="aliceblogs-filter-checkall" >all</label>'));
      }
    }

    function search_posts() {
      $.ajax({
        url: url,
        type: "POST",
        data: {
          'action': 'search_posts',
          'search_text': $('#aliceblogs-searchbar').val()
        },
        beforeSend: function() {
          toggle_loader()
        }
      }).done(function(results) {
        posts = JSON.parse(results)
          toggle_loader()
          render_posts()
      });
    }

    /** 
     * Hide filter when searchbar has text
     */
    $('#aliceblogs-searchbar').on('input', function() {
      window.history.pushState({}, "Home", "/");
      if ($('#aliceblogs-searchbar').val() != '') {
         // query search
        $('#aliceblogs-filter').hide()
        delay(function(){
          search_posts()
        }, 500 );
      } else {
        // searchbar is empty : show/reset filter & show posts
        delay(function(){
          get_degrees()
        }, 500 );
        
        $('#aliceblogs-filter').show()
      }
    });
    
    /**
     * Fill searchbar with clicked tag
     */
    $(document).on('click', '.proposal-tag', function(){
      $('#aliceblogs-searchbar').val($(this).text().substring(1))
      $('#aliceblogs-filter').hide()
      search_posts()
    })

    /**
     * Workaround that fix an issue with flexbox width when using columns
     * This function change the div width based on all elements. Flexbox CSS doesn't adapt the width automatically
     * 
     * According to : 
     * https://stackoverflow.com/questions/23408539/how-can-i-make-a-displayflex-container-expand-horizontally-with-its-wrapped-con/26231447#26231447
     * https://stackoverflow.com/questions/33891709/when-flexbox-items-wrap-in-column-mode-container-does-not-grow-its-width
     * 
     * @param {*} div_id 
     */
    function adjust_div_width(div_id) {
      $(div_id).each(function() {
        var lastChild = $(this).children().last();
        if (lastChild.length > 0){
          var newWidth = lastChild.position().left - $(this).position().left + lastChild.outerWidth(true);
          $(this).width(newWidth);
        }
      })
    }

    /**
     * Check all column checkbox & trigger change event to refresh posts list
     */
    $(document).on('click', '.aliceblogs-filter-checkall', function(){
      $(this).parent().find('input').not(this).prop('checked', 'checked')
      $(this).parent().trigger('change')
    })

    // from : https://github.com/jessekorzan/css-masonry/blob/master/app.js
    function reorder_mansory_layout_carddeck() {
         var _wrapper = $("#aliceblogs-carddeck"),
          _cards = $(".aliceblogs-card"),
          _cols = Number(_wrapper.css("column-count")),
          _out = [],
          _col = 0;

        while (_col < _cols) {
          for (var i = 0; i < _cards.length; i += _cols) {
            var _val = _cards[i + _col];
            if (_val !== undefined)
              _out.push(_val);
          }
          _col++;
        }
        _wrapper.html(_out);
    };
 });