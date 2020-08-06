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
        wp_enqueue_style('animate', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.min.css');
        add_action('wp_enqueue_scripts', [$this, 'load_scripts']);
        add_action('wp_ajax_get_categories', [$this, 'get_categories']);
        add_action('wp_ajax_get_years', [$this, 'get_years']);
        add_action('wp_ajax_get_degrees', [$this, 'get_degrees']);
        add_action('wp_ajax_get_posts', [$this, 'get_posts']);
        add_action('wp_ajax_get_studios', [$this, 'get_studios']);
        add_action('wp_ajax_get_students', [$this, 'get_students']);
        add_action('wp_ajax_search_posts', [$this, 'search_posts']);
        add_action('wp_ajax_get_most_used_tags', [$this, 'get_most_used_tags']);
        add_action('wp_ajax_nopriv_get_posts', [$this, 'get_posts']);
        add_action('wp_ajax_nopriv_get_categories', [$this, 'get_categories']);
        add_action('wp_ajax_nopriv_get_degrees', [$this, 'get_degrees']);
        add_action('wp_ajax_nopriv_get_years', [$this, 'get_years']);
        add_action('wp_ajax_nopriv_get_studios', [$this, 'get_studios']);
        add_action('wp_ajax_nopriv_get_students', [$this, 'get_students']);
        add_action('wp_ajax_nopriv_search_posts', [$this, 'search_posts']);
        add_action('wp_ajax_nopriv_get_most_used_tags', [$this, 'get_most_used_tags']);
        add_action('init', [$this, 'remove_divi_projects']);
        add_action('admin_menu', [$this, 'remove_wp_comments']);
    }

    /**
     * Load Filter scripts only on home page
     */
    public function load_scripts()
    {
        if (is_front_page()) {
            wp_enqueue_script('index', plugin_dir_url(__FILE__)  . '/script.js', array ( 'jquery' ));
            wp_localize_script('index', 'url', admin_url('admin-ajax.php'));
        }
    }

    /**
     * Disable Divi custom post type : Projects
     */
    public function remove_divi_projects(){
        unregister_post_type('project');
    }
    
    /**
     * Remove WP Comments link on admin sidebar
     */
    public function remove_wp_comments() {
        remove_menu_page('edit-comments.php');
    }
    
    /**
     * get_posts
     *
     * @return void
     */
    public function get_posts(){
        $categories = $_POST['categories'];
        if (is_array($categories)) {
            $categories = implode(",", $categories);
        }

        $users = $_POST['users'];
        // Get all users from selected studios
        if(is_array($_POST['roles'])){
            $users = [];
            foreach($_POST['roles'] as $role) {
                $users_belongs_to_role = get_users(['role' => $role]);
                foreach($users_belongs_to_role as $user) {
                    array_push($users, $user->ID);
                }
            }
        }

        // Create WP Query to filter posts authors & categories
        $args = [
            'posts_per_page' => -1,
            'post_type' => 'post',
            'cat' => [$categories],
            'post_status' => 'publish',
            'author' => implode(",", $users)
        ];
        $query = new WP_Query($args);

        foreach($query->posts as $post){
            $posts[$post->ID] = [
                'title'     => $post->post_title,
                'url'       => $post->guid,
                'thumbnail' => get_the_post_thumbnail_url($post->ID),
                'date'      => get_the_date('d M Y', $post->ID),
                'author'    => get_the_author_meta('display_name', $post->post_author),
                'content'   => $post->post_content
            ];
        }
        echo json_encode($posts);
        die();
    }
    
    /**
     * get_years
     *
     * @return void
     */
    public function get_years() {
        $taxonomies = [ 
            'taxonomy' => 'category'
        ];
        $args = [
            'parent'     => 0,
            'hide_empty' => false,
            'exclude'    => 1
        ];
        $terms = get_terms($taxonomies, $args);
        echo json_encode($terms);
        die();
    }
    
    /**
     * get_degrees
     *
     * @return void
     */
    public function get_degrees(){
        if (isset($_POST['year_id'])) {
            $taxonomies = [ 
                'taxonomy' => 'category'
            ];
            $args = [
                'parent'     => $_POST['year_id'],
                'hide_empty' => false
            ];
            $terms = get_terms($taxonomies, $args);
            echo json_encode($terms);
        }
        die();
    }
    
    /**
     * get_categories
     *
     * @return void
     */
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
    
    /**
     * get_studios
     *
     * @return void
     */
    public function get_studios() {
        if(isset($_POST['elements_ids'])){
            if (is_array($_POST['elements_ids'])) {
                $elements_ids = implode(",", $_POST['elements_ids']);
            }
            $args = [
                'numberposts' => -1,
                'category'    => $elements_ids
            ];
            
            $roles = [];
            foreach(get_posts($args) as $post){
                global $wp_roles;

                $author_id = get_post_field('post_author', $post->ID);
                $role_slug = get_role(get_userdata($author_id)->roles[0])->name;

                // Exclude the default WP roles to prevent them from appearing in the filter. 
                $default_wp_roles = [
                    'administrator',
                    'editor',
                    'author',
                    'contributor',
                    'subscriber'
                ];

                if (in_array($role_slug, $default_wp_roles)) {
                    continue;
                }

                $role_name = $wp_roles->role_names[$role_slug];
                $roles[$role_slug] = $role_name;
            }
            echo json_encode($roles);
        }
        die();
    }
    
    /**
     * get_students
     *
     * @return void
     */
    public function get_students() {
        if(isset($_POST['studios_names'])){
            $users = [];
            foreach($_POST['studios_names'] as $role) {
                $users_belongs_to_role = get_users(['role' => $role]);
                foreach($users_belongs_to_role as $user) {
                    $users[$user->ID] = $user->display_name;
                }
            }
            echo json_encode($users);
        }
        die();
    }

    /**
     * search_posts
     * 
     * @return void
     */
    public function search_posts() {
        if(isset($_POST['search_text'])){
            global $wpdb;
            
            $wp_posts = $wpdb->prefix . "posts";
            $wp_term_relationships = $wpdb->prefix . "term_relationships";
            $wp_terms = $wpdb->prefix . "terms";
            $wp_users = $wpdb->prefix . "users";
            $wp_usermeta = $wpdb->prefix . "usermeta";

            $search = str_replace(' ', '%', $_POST['search_text']);
            
            $sql = "SELECT DISTINCT wp_posts.ID, wp_posts.post_title, wp_posts.post_content, wp_posts.guid, wp_users.display_name, wp_posts.post_date FROM " . $wp_posts . " as wp_posts INNER JOIN " . $wp_term_relationships . " as wp_terms_rel ON (wp_posts.ID = wp_terms_rel.object_id) INNER JOIN " . $wp_terms . " as wp_taxonmy ON (wp_terms_rel.term_taxonomy_id = wp_taxonmy.term_id) INNER JOIN " . $wp_users . " as wp_users ON (wp_posts.post_author = wp_users.ID) INNER JOIN " . $wp_usermeta . " as wp_usermeta ON wp_users.ID = wp_usermeta.user_id AND ( ( wp_taxonmy.name LIKE '%%%s%%' ) OR (wp_users.display_name LIKE '%%%s%%') OR (wp_posts.post_title LIKE '%%%s%%') OR (wp_usermeta.meta_value LIKE '%%%s%%')) AND wp_posts.post_type = 'post' AND (wp_posts.post_status = 'publish')";
            
            $query_results = $wpdb->get_results($wpdb->prepare($sql, $search, $search, $search, $search));
            $results = [];
            
            foreach($query_results as $result) {
                $results[$result->ID] = [
                    'title'     => $result->post_title,
                    'url'       => $result->guid,
                    'thumbnail' => get_the_post_thumbnail_url((int)$result->ID),
                    'date'      => date('d M Y', strtotime($result->post_date)),
                    'author'    => $result->display_name,
                    'content'   => get_post_field('post_content', $result->ID)
                ];
            }

            echo json_encode($results);
        }
        die();
    }

    /**
     * Return 5 most used tags
     * 
     * @return void
     */
    public function get_most_used_tags() {
        $args = [
            'taxonomy' => 'post_tag',
            'orderby' => 'count',
            'order' => 'DESC',
            'number' => 5
        ];

        $tags = [];
        foreach(get_terms($args) as $tag) {
            $tags[$tag->slug] = $tag->name;
        }

        echo json_encode($tags);
        die();
    }
}

new Aliceblogs();