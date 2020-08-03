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
        add_action('wp_ajax_get_studios', [$this, 'get_studios']);
        add_action('wp_ajax_get_students', [$this, 'get_students']);
        add_action('wp_ajax_nopriv_get_posts', [$this, 'get_posts']);
        add_action('wp_ajax_nopriv_get_categories', [$this, 'get_categories']);
        add_action('wp_ajax_nopriv_get_degrees', [$this, 'get_degrees']);
        add_action('wp_ajax_nopriv_get_years', [$this, 'get_years']);
        add_action('wp_ajax_nopriv_get_studios', [$this, 'get_studios']);
        add_action('wp_ajax_nopriv_get_students', [$this, 'get_students']);

        add_action('show_user_profile', [$this, 'custom_user_profile_fields'], 10, 1);
        add_action('edit_user_profile', [$this, 'custom_user_profile_fields'], 10, 1);
        add_action('user_new_form', [$this, 'custom_user_profile_fields'], 10, 1);
    }

    /**
     * Create custom field in WP new-user page settings
     * Work in progress
     */
    public function custom_user_profile_fields($profileuser) {
        ?>
            <table class="form-table">
                <tr>
                    <th>
                        <label for="user_testfield"><?php _e('Test'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="user_testfield" id="user_testfield" value="" class="regular-text" />
                        <br><span class="description"><?php _e('Your test field.', 'text-domain'); ?></span>
                    </td>
                </tr>
            </table>
        <?php
    }

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
                'thumbnail' => get_the_post_thumbnail_url($post->ID)
            ];
        }
        echo json_encode($posts);
        // echo json_encode($_POST['roles']);
        die();
    }

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
            // echo json_encode($users_belongs_to_role);
        }
        die();
    }
}

new Aliceblogs();