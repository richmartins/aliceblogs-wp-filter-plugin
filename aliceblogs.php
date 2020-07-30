<?php
/**
 * Plugin Name:       AliceBlogs Plugin
 * Version:           0.0.1
 * Author:            Richard Tenorio & Hadrien Louis
 */

defined('ABSPATH' ) or die( 'No script kiddies please!' );

class Aliceblogs {
    public function __construct(){
        wp_enqueue_style('custom', plugin_dir_url(__FILE__) . '/custom.css');
        wp_enqueue_script('index', plugin_dir_url(__FILE__)  . '/script.js', array ( 'jquery' ));
        wp_localize_script('index', 'url', admin_url('admin-ajax.php'));
        add_action('wp_ajax_get_categories', [$this, 'get_categories']);
        add_action('wp_ajax_get_years', [$this, 'get_years']);
    }

    public function get_years() {
        $taxonomies = [ 
            'taxonomy' => 'category'
        ];
        $args = [
            'parent' => 0,
            'hide_empty' => false,
            'exclude'=> 1
    ];
        $terms = get_terms($taxonomies, $args);
        echo json_encode($terms);
        die();
    }

    public function get_categories() {
        if (isset($_POST['year'])) {
            $taxonomies = [ 
                'taxonomy' => 'category'
            ];
            $args = [
                    'parent' => get_cat_ID($_POST['year']),
                    'hide_empty' => false
            ];
            $terms = get_terms($taxonomies, $args);
            echo json_encode($terms);
        }
        die();
        
    }

}

new Aliceblogs();