// Remove WP Gutenberg panels
// List of all panels(metabox): https://github.com/WordPress/gutenberg/tree/master/packages/edit-post/src/components/sidebar

wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'taxonomy-panel-category' ) ; // category
wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'post-link' ); // permalink
wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'page-attributes' ); // page attributes
wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'post-excerpt' ); // Excerpt
wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'discussion-panel' ); // Discussion
wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'meta-box-members-cp' );
wp.data.dispatch('core/edit-post').removeEditorPanel( 'meta-box-et_settings_meta_box_gutenberg' );
wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'meta-box-postcostum' );