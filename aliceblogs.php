<?php
/**
 * Plugin Name:       AliceBlogs Plugin
 * Version:           0.0.1
 * Author:            Richard Tenorio & Hadrien Louis
 */

defined('ABSPATH' ) or die( 'No script kiddies please!' );
require_once dirname( __FILE__ ) . '/aliceblogs-disable-user.php';

class Aliceblogs {

    const default_wp_roles = [
        'administrator',
        'editor',
        'author',
        'contributor',
        'subscriber',
        'aliceblogs_teacher'
    ];

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
        add_filter('the_content', [$this, 'single_post_metadata']);
        add_action('admin_menu', [$this, 'add_sidebar_menu_item']);
        add_filter('wp_mail_from', [$this, 'wp_sender_email']);
        add_filter('wp_mail_from_name', [$this, 'wp_sender_name']);
        add_action('admin_menu', [$this, 'disable_dashboard_widgets']);
        add_action('admin_menu', [$this, 'remove_tools']);
        add_action('init', [$this, 'hide_gutenberg_panels']);
        add_action('add_meta_boxes', [$this, 'add_categories_metabox']);
        add_action('save_post', [$this, 'save_categories_metabox']);
        add_action('admin_init', [$this, 'create_teacher_role']);
    }

    /**
     * Load Filter scripts only on home page
     */
    public function load_scripts()
    {
        if (is_front_page()) {
            wp_enqueue_script('index', plugin_dir_url(__FILE__)  . '/js/script.js', array ( 'jquery' ));
            wp_localize_script('index', 'url', admin_url('admin-ajax.php'));
        }
    }

    /**
     * Hide Gutenberg panels for non-admin
     */
    public function hide_gutenberg_panels() {
        if (!current_user_can('administrator')) {
            // script file
            wp_register_script(
                'cc-block-script',
                plugin_dir_url(__FILE__) .'/js/block-script.js', // adjust the path to the JS file
                array( 'wp-blocks', 'wp-edit-post' )
            );
            // register block editor script
            register_block_type( 'cc/ma-block-files', array(
                'editor_script' => 'cc-block-script'
            ) );
        }
    }

    /**
     * Disable Divi custom post type : Projects
     */
    public function remove_divi_projects(){
        unregister_post_type('project');
    }

    /**
     * Change default WP email
     */
    public function wp_sender_email() {
        return 'no-reply@aliceblogs.ch';
    }
     
    /**
     * Change default WP email user name 
     */
    public function wp_sender_name() {
        return 'AliceBlogs';
    }

    /**
     * Hide WP Tools menu to non-admin
     */
    public function remove_tools() {
        if (!current_user_can('administrator')) {
            global $submenu;
            unset($submenu['tools.php'][5]);
            remove_menu_page('tools.php');
        }
    }

    // Remove WP admin dashboard widgets
    public function disable_dashboard_widgets() {
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); // Remove "At a Glance"
        //remove_meta_box('dashboard_activity', 'dashboard', 'normal'); // Remove "Activity" which includes "Recent Comments"
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side'); // Remove Quick Draft
        remove_meta_box('dashboard_primary', 'dashboard', 'core'); // Remove WordPress Events and News
    }

    /**
     * Remove WP Comments link on admin sidebar
     */
    public function remove_wp_comments() {
        remove_menu_page('edit-comments.php');
    }

    public function add_sidebar_menu_item() {
        add_menu_page('AliceBlogs', 'AliceBlogs', 'aliceblogs_manage', 'aliceblogs', [$this, 'aliceblogs_page_home'], NULL, 20);
        add_submenu_page('aliceblogs', 'Ajouter', 'Ajouter', 'aliceblogs_manage', 'add-user', [$this, 'dispatch']);
        add_submenu_page('aliceblogs', 'Modifier', 'Modifier', 'aliceblogs_manage', 'edit-user', [$this, 'aliceblogs_edit_user']);
        add_submenu_page('aliceblogs', 'Studio', 'Studio', 'aliceblogs_manage', 'add-studio', [$this, 'aliceblogs_add_studio_dispatch']);
    }

    /**
     * Plugin Home Page
     */
    public function aliceblogs_page_home() {
     ?>
     <h1>Page de gestion AliceBlogs</h1>
     <h3>Créez et modifiez les utilisateurs et les studios</h3>
     <div id="aliceblogs-page-home">
        <p class="submit"><a href="<?= admin_url('admin.php?page=add-user') ?>" class="button button-primary">Ajouter un utilisateur</a></p>
        <p class="submit"><a href="<?= admin_url('admin.php?page=edit-user') ?>" class="button button-primary">Modifier un utilisateur</a></p>
        <p class="submit"><a href="<?= admin_url('admin.php?page=add-studio') ?>" class="button button-primary">Ajouter un studio</a></p>
     </div>
     <?php
    }

    /**
     * Render checkbox/input list with all roles sorted by year & degree
     */
    public function render_roles_list($type = 'checkbox', $checked = '') {
        global $wp_roles;
        $roles = $wp_roles->roles;

        // Order roles by year
        $sorted_roles = [];
        foreach($roles as $role_slug => $role) {
            if (in_array($role_slug, self::default_wp_roles)) {
                continue;
            }
            $sorted_roles[explode('-', $role_slug)[1]][explode('-', $role_slug)[2]][] = [
                'slug' =>$role_slug,
                'name' => $role['name']
            ];
        }

        // sort by year DESC
        krsort($sorted_roles);
        
        // display role
        // loop years
        foreach($sorted_roles as $year => $roles) {
            echo '<h2>' . $year .'</h2>';
            // sort by degree
            ksort($roles);
            // loop degrees
            foreach($roles as $degree => $roles_per_year) {
                echo '<h2>' . $degree .'</h2>';
                // loop roles
                foreach($roles_per_year as $role) {
                    ?>
                    <label>
                        <?php
                        if (is_array($checked) && in_array($role['slug'], $checked)) {
                            echo '<input type="' . $type . '" name="members_user_roles[]" value="' . $role['slug'] . '" checked >';
                        } else {
                            echo '<input type="' . $type . '" name="members_user_roles[]" value="' . $role['slug'] . '">';
                        }
                        echo $role['name'];
                        ?>
                    </label>
                    <br>
                    <?php
                }
            }
        }

    }

    /**
     * Register Teacher role
     */
    public function create_teacher_role() {
        $default_capabilities = [
            'read' => true,
            'edit_posts' => true,
            'upload_files' => true,
            'delete_posts' => true,
            'publish_posts' => true,
            'read_private_posts' => true,
            'delete_published_posts' => true,
            'delete_others_posts' => true,
            'delete_private_posts' => true,
            'edit_published_posts' => true,
            'edit_others_posts' => true,
            'edit_private_posts' => true,
            'aliceblogs_manage' => true,
            'manage_categories' => true
        ];

        add_role('aliceblogs_teacher', 'Teacher', $default_capabilities);
    }

    /**
     * Custom edit user page
     */
    public function aliceblogs_edit_user() {
        if(isset($_POST['first_name'])){
            if(!empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['members_user_roles'])){
                //save to db
                wp_update_user([
                    'ID' => $_POST['ID'], 
                    'first_name' => $_POST['first_name'],
                    'last_name' => $_POST['last_name'],
                    'display_name' => $_POST['first_name'] . ' ' . $_POST['last_name']
                ]);

                $user = get_user_by('ID', $_POST['ID']);
                foreach($user->roles as $role) {
                    $user->remove_role($role);
                }
                foreach($_POST['members_user_roles'] as $role) {
                    $user->add_role($role);
                }

                // disable user
                if (!isset($_POST['aliceblogs_disable_user'])) {
                    $disabled = 0;
                } else {
                    $disabled = $_POST['aliceblogs_disable_user'];
                }
                update_user_meta($user->ID, 'aliceblogs_disable_user', $disabled);

                ?>
                    <div class="notice notice-success is-dismissible aliceblogos-notice">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>L'utilisateur a bien été modifié</p>
                    </div>
                <?php
                } else {
                ?>
                    <div class="notice notice-error is-dismissible aliceblogos-notice">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Merci de bien vouloir remplir tous les champs</p>
                    </div>
                <?php 
            }
        }
        ?>
        <h1>Modifier un utilisateur</h1>
        <form action="" method="post">
            <select name="user-edit">
                <?php 
                    foreach(get_users() as $user) {
                        if (user_can( $user->ID, 'manage_options' )) {
                            continue;
                        }
                        
                        $role_slug = get_userdata($user->ID)->roles[0];
                        if (in_array($role_slug, self::default_wp_roles)) {
                            continue;
                        }
                        
                        if(isset($_POST['user-edit']) && $_POST['user-edit'] == $user->ID){
                            echo '<option value="' . $user->ID .'" selected >' . $user->display_name . ' (' . $user->user_login . ')' . '</option>';
                        } else {
                            echo '<option value="' . $user->ID .'">' . $user->display_name . ' (' . $user->user_login . ')' . '</option>';
                        }
                    }
                ?>
            </select>
            <input class="button button-primary" type="submit" value="Charger"/>
        </form>
        <?php
        if(isset($_POST['user-edit'])) {
            $user = get_userdata($_POST['user-edit']);
        ?>
        <form method="post">
            <table class="form-table">
                <tbody>
                    <tr class="form-field">
                        <th scope="row"><label for="first_name">Prénom </label></th>
                        <td><input name="first_name" class="aliceblogs-field-width" type="text" id="first_name" value="<?= $user->first_name ?>"></td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label for="last_name">Nom </label></th>
                        <td><input name="last_name" class="aliceblogs-field-width" type="text" id="last_name" value="<?= $user->last_name ?>"></td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label>Rôle utilisateur </label></th>
                        <td>
                            <div id="aliceblogs-newuser-role" class="wp-tab-panel aliceblogs-field-width">
                                <?php
                                self::render_roles_list('checkbox', $user->roles);
                                ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th>
                            <label for="disable_user">Désactiver le compte</label>
                        </th>
                        <td>
                            <input type="checkbox" id="aliceblogs_disable_user" name="aliceblogs_disable_user" value="1" <?php checked(1, get_the_author_meta('aliceblogs_disable_user', $user->ID)); ?> />
                            <span class="description">Si la case est cochée, l'utilisateur ne pourra plus se connecter</span>
                        </td>
                    </tr>
                </tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" class="button button-primary" value="Modifier"></p>
            <input type="hidden" name="ID" value= <?= $_POST['user-edit']?>>
        </form>
        <?php
        }
    }

    public function aliceblogs_add_studio_dispatch() {
        if (isset($_POST['role_name'])) {
            self::register_new_studio();
        } else {
            self::add_studio_form();
        }
    }

    public function register_new_studio() {
        if (!empty($_POST['role_name']) && !empty($_POST['role_year']) && !empty($_POST['role_degree'])) {
            $default_capabilities = [
                'read' => true,
                'edit_posts' => true,
                'upload_files' => true,
                'delete_posts' => true,
                'publish_posts' => true,
                'delete_published_posts' => true,
                'edit_published_posts' => true
            ];

            $role_name = $_POST['role_name'];
            $role_slug = strtolower(str_replace(' ', '_', $_POST['role_name'])) . '-' . $_POST['role_year'] . '-' . strtolower($_POST['role_degree']);
            
            $result = add_role($role_slug, $role_name, $default_capabilities);
            if ($result instanceof WP_Role) {
                $_POST = [];
                self::add_studio_form(true);
            } else {
                self::add_studio_form(false, 'Une erreur est survenue, merci de bien vouloir réessayer');
            }
            
        } else {
            self::add_studio_form(false, 'Merci de bien vouloir remplir tous les champs');
        }
    }

    /**
     * New studio html page
     */
    public function add_studio_form($valid = null, $message = '') {
        if ($valid){
            ?>
            <div class="notice notice-success is-dismissible aliceblogos-notice">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Le studio a bien été ajouté</p>
            </div>
            <?php
        } else if ($valid === false) { 
            ?>
            <div class="notice notice-error is-dismissible aliceblogos-notice">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Une erreur s'est produite. <?= $message ?></p>
            </div>
            <?php
        }
        ?>
        <h1>Ajouter un studio</h1>
        <form method="post">
            <table class="form-table">
                <tbody>
                    <tr class="form-field">
                        <th scope="row"><label for="role_name">Nom du studio </label></th>
                        <td><input name="role_name" class="aliceblogs-field-width" type="text" id="role_name" value="<?= $_POST['role_name'] ?>" placeholder="ex: Studio ALICE"></td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label for="role_year">Année </label></th>
                        <td><input name="role_year" class="aliceblogs-field-width" type="text" id="role_year" value="<?= $_POST['role_year'] ?>" placeholder="ex: <?= date('Y') ?>"></td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label for="role_degree">Degré </label></th>
                        <td>
                            <select name="role_degree" id="role_degree">
                                <option>Y1</option>
                                <option>Y5</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" class="button button-primary" value="Créer le studio"></p>
            <h4>Une fois le studio créé, vous aurez la possibilité d'y ajouter des membres</h4>
        </form>
        <?php
    }

    public function dispatch() {
        if (isset($_POST['user_login'])) {
            self::register_new_user();
        } else {
            self::add_user_form();
        }
    }

    /**
     * Verfiy form fields & insert user in DB & send new user email
     */
    public function register_new_user() {

        if (!empty($_POST['user_login']) && !empty($_POST['email']) && is_email($_POST['email']) && !empty($_POST['first_name']) 
            && !empty($_POST['last_name']) && !empty($_POST['members_user_roles'])) {
            $userdata = [
                'user_login'    =>  $_POST['user_login'],
                'user_email'    =>  $_POST['email'],
                'user_pass'     =>  wp_generate_password(20),
                'first_name'    =>  $_POST['first_name'],
                'last_name'     =>  $_POST['last_name'],
                'role'          =>  $_POST['members_user_roles'][0]
            ];

            $message = 'Bonjour ' . $_POST['first_name'] . ', <br><br>Votre compte sur Aliceblogs vient d\'être créé. <br><br> Pour vous y connecter voici vos informations d\'identification : <br><br> Nom d\'utilisateur : ' 
                        . $_POST['user_login'] . '<br> Mot de passe : ' . $userdata['user_pass'] . '<br> Connexion au site : ' . wp_login_url() . '<br><br>Une fois connecté vous aurez la possibilité de changer votre mot de passe dans les réglages de votre compte. 
                        <br><br>Merci <br><br> EPFL Alice';
            
            var_dump($userdata);

            $result = wp_insert_user($userdata);
            if ($result instanceof WP_Error) {
                // WP error
                self::add_user_form(false, $result->get_error_message());
            } else {
                var_dump($result);
                // Create user
                wp_mail($_POST['email'], 'Bienvenue sur Aliceblogs', $message, ['Content-Type: text/html; charset=UTF-8']);
                $_POST = [];
                self::add_user_form(true);
            }
        } else {
            // empty fields
            self::add_user_form(false, 'Merci de bien vouloir remplir tous les champs');
        }
    }

    /**
     * Custom add user form
     */
    public function add_user_form($valid = null, $message = '') {
        global $wp_roles;
        $roles = $wp_roles->roles;
        $roles_ordered = [];
        $years = [];

        // Show status message
        if ($valid){
            ?>
            <div class="notice notice-success is-dismissible aliceblogos-notice">
                <i class="fas fa-exclamation-triangle"></i>
                <p>L'utilisateur a bien été ajouté</p>
            </div>
            <?php
        } else if ($valid === false) { 
            ?>
            <div class="notice notice-error is-dismissible aliceblogos-notice">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Une erreur s'est produite. <?= $message ?></p>
            </div>
            <?php
        }

        ?>
        <h1>Ajouter un utilisateur</h1>
        <h3>Créer un nouvel utilisateur et l'ajouter à ce site</h3>
        <form method="post">
            <table class="form-table">
                <tbody>
                    <tr class="form-field form-required">
                        <th scope="row"><label for="user_login">Identifiant</label></th>
                        <td><input name="user_login" class="aliceblogs-field-width" type="text" id="user_login" value="<?= $_POST['user_login'] ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60"></td>
                    </tr>
                    <tr class="form-field form-required">
                        <th scope="row"><label for="email">Adresse de messagerie</label></th>
                        <td><input name="email" class="aliceblogs-field-width" type="email" id="email" value="<?= $_POST['email'] ?>"></td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label for="first_name">Prénom </label></th>
                        <td><input name="first_name" class="aliceblogs-field-width" type="text" id="first_name" value="<?= $_POST['first_name'] ?>"></td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label for="last_name">Nom </label></th>
                        <td><input name="last_name" class="aliceblogs-field-width" type="text" id="last_name" value="<?= $_POST['last_name'] ?>"></td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label>Rôle utilisateur </label></th>
                        <td>
                            <div id="aliceblogs-newuser-role" class="wp-tab-panel aliceblogs-field-width">
                                <?php
                                self::render_roles_list('radio', $_POST['members_user_roles']);
                                ?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" class="button button-primary" value="Ajouter un utilisateur"></p>
        </form>
        <h4>Le nouvel utilisateur recevra automatiquement un email avec ses identifiants de connexion ainsi qu'un mot de passe généré automatiquement qu'il pourra changer</h4>
        <?php
    }

     /**
     * Create categories metabox 
     */
    public function add_categories_metabox() {
        $screens = ['post'];
        foreach ($screens as $screen) {
            add_meta_box(
                'categories-box',           
                'Choisir une catégorie',  
                [$this, 'categories_metabox_content'],  
                $screen,                   
                'side'
            );
        }
    }

    /**
     * Categories metabox - save data
     */
    public function save_categories_metabox($post_id) {
        if (array_key_exists('aliceblogs-categories', $_POST)) {
            wp_set_post_categories($post_id, $_POST['aliceblogs-categories']);
        }
    }

    /**
     * Categories metabox content
     */
    public function categories_metabox_content($post) {
        // getting studios (role of user) by author ID
        $role_slug = get_userdata($post->post_author)->roles[0];
        // Find category with this pattern "degree-year" : y1-2020 or y5-2019
        $degree = explode('-', $role_slug)[2];
        $year = explode('-', $role_slug)[1];
        $year_category = get_category_by_slug($degree . '-' . $year)->term_id;

        $args = [
            'hide_empty'               => FALSE,
            'hierarchical'             => 1,
            'taxonomy'                 => 'category',
            'child_of'                 => $year_category,
        ];

        ?>
        <div>
            <h2 id="aliceblogs-metabox-categories-title"><?= $year . ' - ' . $degree ?></h2>
            <?php
            $parents = [];

            // Find child categories to degree-year cat
            $categories = get_categories($args);
            // Get post category 
            $post_category = get_the_category($post->ID);
            foreach($categories as $category) {
                // autoselected post category
                $checked = '';
                if ($category->slug == $post_category[0]->slug) {
                    $checked = 'checked';
                }
                ?>
                <div>
                    <input id="<?= $category->slug ?>" type="radio" value="<?= $category->term_id ?>" name="aliceblogs-categories[]" <?= $checked ?>>
                    <label class="aliceblogs-metabox-category" for="<?= $category->slug ?>"><?= $category->name ?></label>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }

    /**
     * Adds post tags below post content
     */
    function single_post_metadata($content) {

        if (get_post_type() != 'post') {
            return $content;
        }

        $author = get_the_author();
        $user = get_userdata( get_the_author_meta('ID') );
        $date = get_the_date('j/m/Y');
        $categories = get_the_category();
        $posttags = get_the_tags();

        // getting studios (role of user) by author ID
        global $wp_roles;                  // getting role name by role slug
        $role_name = $wp_roles->role_names[get_role(get_userdata(get_the_author_meta('ID'))->roles[0])->name];

        // Show post tags
        if ($posttags) {
            $tags_badges = [];
            foreach($posttags as $tag) {
                array_push($tags_badges,'<a class="aliceblogs-post-tag" href="/tag/' . $tag->slug . '/">#' . $tag->name . '</a>');
            }
            $tags = '<div id="aliceblogs-post-tags-container">' . implode(' ', $tags_badges) . '</div>';
        }

        // Show post categories
        if ($categories) {
            $cats_badges = '';
            foreach ($categories as $category) {
                $cats_badges .= $category->name;
            }
        }

        /* Bulding post's meta data */
        $content = 'par ' . $author . ' | ' . $date . ' | ' .  $cats_badges . ' | ' . $role_name . ' ' . $tags . '<br><br>' . $content;

        return $content;
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
                'thumbnail' => get_the_post_thumbnail_url((int)$post->ID) ? get_the_post_thumbnail_url((int)$post->ID) : plugin_dir_url( __FILE__ ) . 'images/missing_img.svg',
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
                $user_roles = get_userdata($author_id)->roles;

                // loop on all user roles
                foreach($user_roles as $role) {
                    $role_slug = get_role($role)->name;
                    $role_name = $wp_roles->role_names[$role_slug];

                    // Exclude the default WP roles to prevent them from appearing in the filter. 
                    if (in_array($role_slug, self::default_wp_roles)) {
                        continue;
                    }
                    
                    $roles[$role_slug] = $role_name;
                }
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
                    'thumbnail' => get_the_post_thumbnail_url((int)$result->ID) ? get_the_post_thumbnail_url((int)$result->ID) : plugin_dir_url( __FILE__ ) . 'images/missing_img.svg',
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
            $tags[$tag->slug] = '#' . $tag->name;
        }

        echo json_encode($tags);
        die();
    }
}

new Aliceblogs();