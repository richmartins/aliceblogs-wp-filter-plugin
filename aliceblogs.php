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
        add_action('wp_ajax_get_degrees', [$this, 'get_degrees']);
        add_action('wp_ajax_get_posts', [$this, 'get_posts']);
        add_action('wp_ajax_nopriv_get_posts', [$this, 'get_posts']);
        add_action('wp_ajax_nopriv_get_categories', [$this, 'get_categories']);
        add_action('wp_ajax_nopriv_get_degrees', [$this, 'get_degrees']);
        add_action('wp_ajax_nopriv_get_years', [$this, 'get_years']);
    }

    public function get_posts(){
        //$categories = isset($_POST['categories']) ? $_POST['categories'] : '';
        $args = [
            'numberposts' => -1,
            'category' => $_POST['categories']
        ];
        $posts = [];
        
        foreach(get_posts($args) as $post){
            $posts[$post->ID] = [
                'title' => $post->post_title,
                'url' => $post->guid,
                'thumbnail' => get_the_post_thumbnail($post->ID)
            ];
        }
        echo json_encode($posts);
        die();
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
    public function get_degrees(){

        if (isset($_POST['year_id'])) {
            $taxonomies = [ 
                'taxonomy' => 'category'
            ];
            $args = [
                'parent' => $_POST['year_id'],
                'hide_empty' => false
            ];
            $terms = get_terms($taxonomies, $args);
            echo json_encode($terms);
        }
        die();
    }
    public function get_categories() {
        if(isset($_POST['degree_id'])){
            $taxonomies = [ 
                'taxonomy' => 'category'
            ];
            $args = [
                'parent'     => $_POST['degree_id'],
                'hide_empty' => false,
                'exclude'    => 1
            ];
            $terms = get_terms($taxonomies, $args);
            echo json_encode($terms);
        }
        die();
    }

}

new Aliceblogs();