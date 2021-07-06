jQuery(document).ready(function($){
    // Uncheck all medias from other categories in metabox
    $("#aliceblogs-categories-container-checkbox input[type='checkbox']").change(function() {
        let parent_id = $(this).parent().parent().attr('id')
        $(".aliceblogs-categories-checkbox[id!='" + parent_id + "']").each(function() {
          $(this).children().children().prop('checked',false)
        });
    })
})

wp.domReady( function() {
    // List all available blocks
    // wp.data.select( 'core/blocks' ).getBlockTypes()

    // Unregister specific embed block
    // wp.blocks.unregisterBlockVariation( 'core/embed', 'twitter' )

    // Unregister all embed blocks
    wp.blocks.unregisterBlockType('core/embed')

    // Disable specific gutenberg blocks
    wp.blocks.unregisterBlockType('divi/layout')
    wp.blocks.unregisterBlockType('filebird/block-filebird-gallery')
    //wp.blocks.unregisterBlockType('core-embed/twitter')
    // wp.blocks.unregisterBlockType('core-embed/facebook')
    // wp.blocks.unregisterBlockType('core-embed/instagram')
    // wp.blocks.unregisterBlockType('core-embed/wordpress')
    // wp.blocks.unregisterBlockType('core-embed/soundcloud')
    // wp.blocks.unregisterBlockType('core-embed/spotify')
    // wp.blocks.unregisterBlockType('core-embed/flickr')
    // wp.blocks.unregisterBlockType('core-embed/animoto')
    // wp.blocks.unregisterBlockType('core-embed/cloudup')
    // wp.blocks.unregisterBlockType('core-embed/collegehumor')
    // wp.blocks.unregisterBlockType('core-embed/crowdsignal')
    // wp.blocks.unregisterBlockType('core-embed/imgur')
    // wp.blocks.unregisterBlockType('core-embed/issuu')
    // wp.blocks.unregisterBlockType('core-embed/kickstarter')
    // wp.blocks.unregisterBlockType('core-embed/meetup-com')
    // wp.blocks.unregisterBlockType('core-embed/mixcloud')
    // wp.blocks.unregisterBlockType('core-embed/polldaddy')
    // wp.blocks.unregisterBlockType('core-embed/reddit')
    // wp.blocks.unregisterBlockType('core-embed/reverbnation')
    // wp.blocks.unregisterBlockType('core-embed/screencast')
    // wp.blocks.unregisterBlockType('core-embed/scribd')
    // wp.blocks.unregisterBlockType('core-embed/slideshare')
    // wp.blocks.unregisterBlockType('core-embed/smugmug')
    // wp.blocks.unregisterBlockType('core-embed/speaker')
    // wp.blocks.unregisterBlockType('core-embed/speaker-deck')
    // wp.blocks.unregisterBlockType('core-embed/tiktok')
    // wp.blocks.unregisterBlockType('core-embed/ted')
    // wp.blocks.unregisterBlockType('core-embed/tumblr')
    // wp.blocks.unregisterBlockType('core-embed/videopress')
    // wp.blocks.unregisterBlockType('core-embed/wordpress-tv')
    // wp.blocks.unregisterBlockType('core-embed/amazon-kindle')
    // wp.blocks.unregisterBlockType('core/gallery')
    // wp.blocks.unregisterBlockType('core/code')
    // wp.blocks.unregisterBlockType('core/audio')
    // wp.blocks.unregisterBlockType('core/file')
    wp.blocks.unregisterBlockType('core/shortcode')
    wp.blocks.unregisterBlockType('core/archives')
    wp.blocks.unregisterBlockType('core/calendar')
    wp.blocks.unregisterBlockType('core/categories')
    wp.blocks.unregisterBlockType('core/html')
    wp.blocks.unregisterBlockType('core/latest-comments')
    wp.blocks.unregisterBlockType('core/latest-posts')
    wp.blocks.unregisterBlockType('core/rss')
    wp.blocks.unregisterBlockType('core/search')
    wp.blocks.unregisterBlockType('core/social-links')
    wp.blocks.unregisterBlockType('core/social-link')
    wp.blocks.unregisterBlockType('core/tag-cloud')
} );