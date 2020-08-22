wp.domReady( function() {
    // List all available blocks
    // wp.data.select( 'core/blocks' ).getBlockTypes()

    // Disable gutenberg blocks
    wp.blocks.unregisterBlockType('divi/layout')
    wp.blocks.unregisterBlockType('filebird/block-filebird-gallery')
} );