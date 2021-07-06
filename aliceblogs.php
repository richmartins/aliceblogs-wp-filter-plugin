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
        'aliceblogs_teacher',
        'aliceblogs_super_teacher'
    ];

    const default_medias_category_id = 28;

    public function __construct(){
        add_action('wp_enqueue_scripts', [$this, 'load_front_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']); 
        add_action('wp_ajax_get_categories', [$this, 'get_categories']);
        add_action('wp_ajax_get_years', [$this, 'get_years']);
        add_action('wp_ajax_get_degrees', [$this, 'get_degrees']);
        add_action('wp_ajax_get_medias', [$this, 'get_medias']);
        add_action('wp_ajax_get_posts', [$this, 'get_posts']);
        add_action('wp_ajax_get_studios', [$this, 'get_studios']);
        add_action('wp_ajax_get_students', [$this, 'get_students']);
        add_action('wp_ajax_search_posts', [$this, 'search_posts']);
        add_action('wp_ajax_get_most_used_tags', [$this, 'get_most_used_tags']);
        add_action('wp_ajax_nopriv_get_posts', [$this, 'get_posts']);
        add_action('wp_ajax_nopriv_get_categories', [$this, 'get_categories']);
        add_action('wp_ajax_nopriv_get_degrees', [$this, 'get_degrees']);
        add_action('wp_ajax_nopriv_get_years', [$this, 'get_years']);
        add_action('wp_ajax_nopriv_get_medias', [$this, 'get_medias']);
        add_action('wp_ajax_nopriv_get_studios', [$this, 'get_studios']);
        add_action('wp_ajax_nopriv_get_students', [$this, 'get_students']);
        add_action('wp_ajax_nopriv_search_posts', [$this, 'search_posts']);
        add_action('wp_ajax_nopriv_get_most_used_tags', [$this, 'get_most_used_tags']);
        add_action('init', [$this, 'remove_divi_projects']);
        add_action('admin_menu', [$this, 'remove_wp_comments']);
        add_action('admin_menu', [$this, 'add_sidebar_menu_item']);
        add_action('admin_menu', [$this, 'disable_dashboard_widgets']);
        add_action('admin_menu', [$this, 'remove_tools']);
        add_action('admin_enqueue_scripts', [$this, 'hide_gutenberg_elements'], 10, 1);
        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('save_post', [$this, 'save_metabox']);
        add_action('admin_init', [$this, 'create_teacher_role']);
        add_action('after_setup_theme', [$this, 'remove_posts_format'], 15);

        add_filter('wp_mail_from', [$this, 'wp_sender_email']);
        add_filter('wp_mail_from_name', [$this, 'wp_sender_name']);
        add_filter('the_content', [$this, 'single_post_metadata']);
        add_filter('post_row_actions', [$this, 'disable_quick_edit'], 10, 2 );
        add_action('wp_ajax_get_medias2', [$this, 'get_medias_2']);
        add_action('wp_ajax_nopriv_get_medias2', [$this, 'get_medias_2']);

        add_action('wp_before_admin_bar_render', [$this, 'admin_topbar_comments']);
    }

    /**
     * Load JS scripts only on home page
     */
    public function load_front_scripts()
    {
        wp_enqueue_style('custom', plugin_dir_url(__FILE__) . '/css/custom.css');
        if (is_front_page()) {
            wp_enqueue_style('animate', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.min.css');
            wp_enqueue_script('isotope', 'https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js', array ( 'jquery' ));
            wp_enqueue_script('index', plugin_dir_url(__FILE__)  . '/js/script.js', array ( 'jquery' ));
            wp_localize_script('index', 'url', admin_url('admin-ajax.php'));
        }
    }

    /**
     * Load scripts only on backend pages
     */
    public function load_admin_scripts() 
    {
        wp_enqueue_style('custom', plugin_dir_url(__FILE__) . '/css/custom-admin.css');
    }

    /**
     * remove_posts_formats
     *
     * @return void
     */
    public function remove_posts_format(){
        remove_theme_support('post-formats');
    }

    /**
     * Hide Gutenberg categories panel for all non-default WP roles
     */
    public function hide_gutenberg_elements($hook) {
        $user = wp_get_current_user();

        // Hide Gutenberg blocks
        if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
            wp_enqueue_script(
                'wp-admin',
                plugin_dir_url(__FILE__)  . '/js/wp-admin.js',
                ['wp-blocks', 'wp-dom-ready', 'wp-edit-post']
            );
        }
        
        if (!in_array($user->roles[0], self::default_wp_roles)) {
            // script file
            wp_enqueue_script(
                'block-script',
                plugin_dir_url(__FILE__) .'/js/block-script.js',
                [ 'wp-blocks', 'wp-edit-post' ]
            );
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
     * Remove comments icon in WP admin topbar
     */
    public function admin_topbar_comments() {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
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
        
        if (current_user_can('administrator')) {
            add_submenu_page('aliceblogs', 'DEBUG', 'DEBUG', 'aliceblogs_manage', 'debug-studio', [$this, 'aliceblogs_debug']);
        }
    }

    /**
     * Hide WP Quick edit button
     * Reason : if user use quick edit, he can allow post comments & change post categories without beeing restrcited. 
     *          The category list in quick edit is not filtered with user role
     */
    public function disable_quick_edit($actions = [], $post = null) {
        if (!current_user_can('administrator')) {
            if (isset($actions['inline hide-if-no-js'])) {
                unset($actions['inline hide-if-no-js']);
            }
        }
        return $actions;
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
            $role_explode_name = explode('-', $role_slug);
            $sorted_roles[str_replace('_', '-', $role_explode_name[1])][$role_explode_name[2]][] = [
                'slug' => $role_slug,
                'name' => $role['name']
            ];
        }

        // sort by year DESC
        krsort($sorted_roles);
        
        // display role
        // loop years
        foreach($sorted_roles as $year => $roles) {
            // format year like 20-21 and not 20_21
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
            // 'manage_categories' => true
        ];

        $default_super_capabilities = [
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
            'manage_categories' => true,
            'remove_users' => true,
            'create_users' => true,
            'list_users' => true,
            'edit_users' => true,
            'promote_users' => true
        ];

        add_role('aliceblogs_teacher', 'Teacher', $default_capabilities);
        add_role('aliceblogs_super_teacher', 'Super Teacher', $default_super_capabilities);
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
                <option selected="true" disabled="disabled">Veuillez sélectionner un étudiant...</option>
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
        if (!empty($_POST['role_name']) && !empty($_POST['role_degree'])) {
            $default_capabilities = [
                'read' => true,
                'edit_posts' => true,
                'upload_files' => true,
                'delete_posts' => true,
                'publish_posts' => true,
                'delete_published_posts' => true,
                'edit_published_posts' => true
            ];

            // Input :
            // role_name = Studio Alice
            // role degree = y1-21_22
            // Output : studio_alice-21_22-y1

            $role_degree_parts = explode('-', $_POST['role_degree']);
            $role_slug = strtolower(str_replace(' ', '_', $_POST['role_name']) . '-' . $role_degree_parts[1] . '-' . $role_degree_parts[0]);

            $result = add_role($role_slug, $_POST['role_name'], $default_capabilities);
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
        // get years + child without medias

        $taxonomies = [ 
            'taxonomy' => 'category'
        ];

        $args = [
            'exclude' => [
                1, 
                self::default_medias_category_id
            ],
            'hide_empty' => false,
         ];
        
        $terms = get_terms($taxonomies, $args);
        $years = [];

        foreach($terms as $term) {
            if($term->parent == 0){
                $years[$term->term_id] = [
                    'name' => $term->name,
                    'slug' => $term->slug
                ];
            } 
        }

        foreach($terms as $term) {
            if((array_key_exists($term->parent, $years)) && ($term->parent != self::default_medias_category_id)){
                $degrees[$term->term_id] = [
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'parent' => $term->parent
                ];
            }
        }

        //order array by year
        krsort($years);

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
                        <th scope="row"><label for="role_name">Nom</label></th>
                        <td><input name="role_name" class="aliceblogs-field-width" type="text" id="role_name" value="<?= $_POST['role_name'] ?? '' ?>" placeholder="ex: Studio ALICE"></td>
                    </tr>
                    <tr>
                        <th scope="row">Classe</th>
                        <td>
                            <div id="aliceblogs-newuser-role" class="wp-tab-panel aliceblogs-field-width">
                                <?php
                                foreach($years as $id_year => $year) {
                                        echo '<h2>' . $year['name'] .'</h2>';
                                        foreach($degrees as $id_degree => $degree) {
                                            if($degree['parent'] == $id_year) {
                                                ?>
                                                    <input id="<?= $degree['slug'] ?>" type="radio" name="role_degree" value="<?= $degree['slug'] ?>" />
                                                    <label for="<?= $degree['slug']?>"><?= $degree['name'] ?></label><br />
                                                <?php
                                            }
                                        }
                                    }
                                ?>
                            </div>
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
                        <td><input name="user_login" class="aliceblogs-field-width" type="text" id="user_login" value="<?= $_POST['user_login'] ?? '' ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60"></td>
                    </tr>
                    <tr class="form-field form-required">
                        <th scope="row"><label for="email">Adresse de messagerie</label></th>
                        <td><input name="email" class="aliceblogs-field-width" type="email" id="email" value="<?= $_POST['email'] ?? '' ?>"></td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label for="first_name">Prénom </label></th>
                        <td><input name="first_name" class="aliceblogs-field-width" type="text" id="first_name" value="<?= $_POST['first_name'] ?? '' ?>"></td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label for="last_name">Nom </label></th>
                        <td><input name="last_name" class="aliceblogs-field-width" type="text" id="last_name" value="<?= $_POST['last_name'] ?? '' ?>"></td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label>Rôle utilisateur </label></th>
                        <td>
                            <div id="aliceblogs-newuser-role" class="wp-tab-panel aliceblogs-field-width">
                                <?php
                                self::render_roles_list('radio', $_POST['members_user_roles'] ?? '');
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
     * Create categories & medias metabox
     * Show metabox only if user is student
     */
    public function add_metabox() {
        $user = wp_get_current_user();
        $screens = ['post'];
        if (!in_array($user->roles[0], self::default_wp_roles)) {
            foreach ($screens as $screen) {
                add_meta_box(
                    'categories-box',           
                    'Choisir une catégorie',  
                    [$this, 'categories_metabox_content'],  
                    $screen,
                    'side'
                );
            }

            foreach ($screens as $screen) {
                add_meta_box(
                    'participants-box',
                    'Choisir des participants',
                    [$this, 'participants_metabox_content'],
                    $screen,
                    'side'
                );
            }
        }
    }

    
    public function aliceblogs_debug() {
        echo "<h1>DEBUG MODE </h1><br><br>";

        /*
        $sticky = get_option( 'sticky_posts' );
        rsort($sticky);
        $sticky_posts = new WP_Query(['post__in' => $sticky, 'ignore_sticky_posts' => 1]);
        $posts_without_sticky = new WP_Query(['post__not_in' => $sticky, 'ignore_sticky_posts' => 1]);
        $posts = array_merge($sticky_posts->posts, $posts_without_sticky->posts);
        var_dump($posts);
        */
    
    }

    /**
     * Save custom metabox (Categories & Participants)
     */
    public function save_metabox($post_id) {
        $user = wp_get_current_user();

        if (!in_array($user->roles[0], self::default_wp_roles)) {

            if (isset($_POST['aliceblogs-categories'])) {
                $medias = [];
                foreach($_POST['aliceblogs-categories'] as $term) {
                    $media = get_category_by_slug($term);
                    array_push($medias, $media->term_id);
                    $medias['parent'] = $media->parent;
                }

                wp_set_post_categories($post_id, $medias);
            }

            if (isset($_POST['aliceblogs-participants'])) {
                $participants = [];
                foreach($_POST['aliceblogs-participants'] as $user_id){
                    $user = get_user_by('ID', $user_id);
                    $participants[$user_id] = [
                        'name' => $user->user_nicename,
                        'display_name' => $user->display_name
                    ];
                }
                //save the participants
                //DANGER FUNCTION
                update_post_meta($post_id, '_aliceblogs_participants', serialize($participants));
            }
        }
    }

    public function participants_metabox_content ($post) {
        //get users that are already participants
        $users_already_participants = unserialize(get_post_meta($post->ID, '_aliceblogs_participants')[0]);
        //get list of users of the same studio
        foreach(get_userdata($post->post_author)->roles as $role) {
            $args = [
                'role'     => $role,
                'order'    => 'ASC',
                'order_by' => 'display_name'
            ];

            //display a list of users
            $nice_title = preg_replace('/[_-]/', ' ', $role);
            $year = explode('-', $role)[1];
            ?>
                <h3><?php echo ucwords($nice_title); ?></h3>
            <?php
            foreach(get_users($args) as $user){
      
                if($user->ID == $post->post_author) {
                    continue;
                }

                $checked = '';
                if(!empty($users_already_participants) && in_array($user->ID, array_keys($users_already_participants))){
                    $checked = 'checked';
                }
                ?>
                    <div>
                        <input id="<?= $year . '_' . $user->ID ?>" type="checkbox" value="<?= $user->ID ?>" name="aliceblogs-participants[]" <?= $checked ?>>
                        <label class="aliceblogs-metabox-item" for="<?= $year . '_' . $user->ID ?>"><?= $user->display_name ?></label>
                    </div>
                <?php
            }
        }
        //preselect participants
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
            'hide_empty'               => false,
            'hierarchical'             => 1,
            'taxonomy'                 => 'category',
            'child_of'                 => $year_category,
        ];

        ?>
        <div id="aliceblogs-categories-container-checkbox">
            <h2 id="aliceblogs-metabox-title"><?= str_replace('_', '-', $year) . ' - ' . $degree ?></h2>
            <?php
            // Find child categories to degree-year cat
            foreach(get_categories($args) as $category) {
                if ($category->parent !== $year_category) {
                    $sorted_categories[get_category($category->parent)->name][$category->slug] = $category->name;
                }
            }

            // Build array with post's categories
            $post_categories = get_the_category($post->ID);
            $post_categories_slugs = [];
            foreach($post_categories as $cat_term) {
                $post_categories_slugs[$cat_term->term_id] = $cat_term->slug;
            }

            foreach($sorted_categories as $key => $element) {
                echo "<h4>" . $key . "</h4>";
                echo '<div id="' . $key . '" class="aliceblogs-categories-checkbox" >';
                foreach($element as $slug => $category) {
                    // autoselected post category
                    $checked = '';
                    if(in_array($slug, $post_categories_slugs)){
                        $checked = 'checked';
                    }
                    ?>
                    <div>
                        <input id="<?= $slug ?>" type="checkbox" value="<?= $slug ?>" name="aliceblogs-categories[]" <?= $checked ?>>
                        <label class="aliceblogs-metabox-item" for="<?= $slug ?>"><?= $category ?></label>
                    </div>
                    <?php
                }
                echo "</div>";
            }
            ?>
        </div>
        <?php
    }

    /**
     * Adds post tags below post content
     */
    public function single_post_metadata($content) {

        if (get_post_type() != 'post') {
            return $content;
        }

        $author = get_the_author();
        $user = get_userdata( get_the_author_meta('ID') );
        $date = get_the_date('j/m/Y');
        $categories = get_the_category();
        $posttags = get_the_tags();
        $participants_meta  = unserialize(get_post_meta(get_the_ID(), '_aliceblogs_participants')[0]);
        $participants_badges = '';
        if(!empty($participants_meta)){
            foreach($participants_meta as $participant) {
                $participants_badges .= ', <a href="/?q=' . $participant['display_name'] . '" >' . $participant['display_name'] . '</a>';
            }
        }else {
            $participants = '';
        }

        // getting studios (role of user) by author ID
        global $wp_roles;                  // getting role name by role slug
        $role_name = $wp_roles->role_names[get_role(get_userdata(get_the_author_meta('ID'))->roles[0])->name];

        // Show post tags
        if ($posttags) {
            $tags_badges = [];
            foreach($posttags as $tag) {
                array_push($tags_badges,'<a class="aliceblogs-post-tag" href="/?q=' . $tag->name . '">#' . $tag->name . '</a>');
            }
            $tags = '<div id="aliceblogs-post-tags-container">' . implode(' ', $tags_badges) . '</div>';
        }

        // Show post categories
        if ($categories) {
            $cats_badges = '';
            foreach($categories as $key => $category) {
                $cats_badges .= '<a class="aliceblogs-post-metadata-categories" href="/?q=' . $category->name . '" >' . $category->name . '</a>';
                if (array_key_last($categories) != $key) {
                    $cats_badges .= ', ';
                }
            }
        }
        
   
        /* Bulding post's meta data */
        $content = 'par ' 
        . '<a href="/?q=' . $author . '" >' . $author . '</a>' 
        . $participants_badges 
        . ' | ' . $date 
        . ' | ' . $cats_badges 
        . ' | ' . '<a href="/?q=' . $role_name . '" >' . $role_name . '</a>' 
        . ' '. $tags . '<br><br>' . $content;

        return $content;
    }
    
    /**
     * get_posts
     *
     * @return void
     */
    public function get_posts(){
        $categories = $_POST['categories'];
        $medias = $_POST['medias'];
        $users = $_POST['students'];

        // Merge all categories & medias in a single string
        if (!empty($medias)) {
            $categories = array_merge($categories, $medias);
        }

        // Get all users from selected studios
        if(is_array($_POST['roles']) && empty($users)){
            $users = [];
            foreach($_POST['roles'] as $role) {
                $users_belongs_to_role = get_users(['role' => $role]);
                foreach($users_belongs_to_role as $user) {
                    array_push($users, $user->ID);
                }
            }
        }
        $users = implode(",", $users);

        //get ID of STICKY POSTS
        $sticky = get_option( 'sticky_posts' );
        rsort($sticky);

        // Create WP Query to filter posts authors & categories
        $args = [
            'posts_per_page' => 200,
            'cat' => $categories,
            'post_type' => 'post',
            'post_status' => 'publish',
            'author' => $users,
            'post__not_in' => $sticky,
            'ignore_sticky_posts' => 1
        ];

        //QUERY to get all STICKY POSTS
        $args_sticky = [
            'posts_per_page' => 200,
            'cat' => $categories,
            'post_type' => 'post',
            'post_status' => 'publish',
            'author' => $users,
            'post__in' => $sticky,
            'ignore_sticky_posts' => 1

        ];

        $sticky_posts = new WP_Query($args_sticky);

        // QUERY to gel all posts but not the sickies ones
        $query = new WP_Query($args);

        $posts_not_formatted = array_merge($sticky_posts->posts, $query->posts);

        // create second query if users are selected in filter
        // this query will be used for meta values (participants)
        if (!empty($_POST['students'])) {
            $args2 = [
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'cat' => $categories,
            ];

            $args2_meta_query = [];
            foreach($_POST['students'] as $student) {
                $student_obj = get_user_by('ID', $student);
                $meta_search = [
                    'key'     => '_aliceblogs_participants',
                    'value'   => $student_obj->display_name,
                    'compare' => 'LIKE'
                ];
                array_push($args2_meta_query, $meta_search);
            }
            $args2['meta_query'] = $args2_meta_query;
            $args2['meta_query']['relation'] = 'OR';
            $query2 = new WP_Query($args2);
            $query_results = array_merge($query->posts, $query2->posts);
        } else {
            $query_results = $posts_not_formatted;
        }

        $posts = [];
        foreach($query_results as $post){

            $data = [
                'id'        => $post->ID,
                'title'     => $post->post_title,
                'url'       => $post->guid,
                'thumbnail' => get_the_post_thumbnail_url((int)$post->ID) ? get_the_post_thumbnail_url((int)$post->ID) : plugin_dir_url( __FILE__ ) . 'images/missing_img.svg',
                'date'      => get_the_date('d M Y', $post->ID),
                'author'    => get_the_author_meta('display_name', $post->post_author),
                'content'   => $post->post_content 
            ];
            if (empty($medias)) {
                array_push($posts, $data);
            } else {
                $post_cats = get_the_category((int)$post->ID);
                foreach ($post_cats as $cat) {
                    if (in_array(strval($cat->term_id), $medias)) {
                        array_push($posts, $data);
                    }
                }
            }
        }

        // remove magically duplicates in multi D arrays
        $posts = array_unique($posts, SORT_REGULAR);
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
            'exclude'    => [1, self::default_medias_category_id]
        ];

        $terms = get_terms($taxonomies, $args);
        echo json_encode($terms);
        die();
    }

    
    /**
     * get_medias
     *
     * @return void
     */
    public function get_medias() {
        $elements = $_POST['elements'];
        $terms = [];
        $taxonomies = [ 
            'taxonomy' => 'category'
        ];
        $args = [
            'hide_empty' => false
        ];
        foreach ($elements as $element) {
            $args['parent'] = $element;
            foreach(get_terms($taxonomies, $args) as $term) {
                $terms[$term->name][$term->term_id] = $term->slug;
            }
        }
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
                'hide_empty' => false,
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
                'exclude'    => 1,
                'orderby'    => 'order'
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
        if(isset($_POST['elements_ids']) && isset($_POST['medias_ids'])){
            if (is_array($_POST['elements_ids'])) {
                $elements_ids = implode(",", $_POST['elements_ids']);
            }
            
            $args = [
                'numberposts' => -1,
                'category'    => $elements_ids
            ];
            
            $roles = [];
            foreach(get_posts($args) as $post){

                $post_cats = get_the_category((int)$post->ID);
                foreach ($post_cats as $cat) {
                    if (in_array(strval($cat->term_id), $_POST['medias_ids'])) {
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
                }
            }

            ksort($roles);
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
                    //$users[$user->ID] = $user->display_name;
                    array_push($users, [
                        'id' => $user->ID,
                        'name' => $user->last_name . ' ' . $user->first_name
                    ]);
                }
            }
            // from : https://www.tutorialspoint.com/sort-multidimensional-array-by-multiple-keys-in-php
            // sort multidimensional arrays by ASC username
            array_multisort(array_column($users, 'name'), SORT_ASC, $users);
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
            $search = $_POST['search_text'];
            
            $wp_posts = $wpdb->prefix . "posts";
            $wp_postmeta = $wpdb->prefix . 'postmeta';
            $wp_term_relationships = $wpdb->prefix . "term_relationships";
            $wp_terms = $wpdb->prefix . "terms";
            $wp_users = $wpdb->prefix . "users";
            $wp_usermeta = $wpdb->prefix . "usermeta";
            
            $sql = 
            "SELECT DISTINCT wp_posts.ID, wp_posts.post_title, wp_posts.post_content, wp_posts.guid, wp_users.display_name, wp_posts.post_date
            FROM " . $wp_posts . " as wp_posts 
            INNER JOIN " . $wp_term_relationships . " as wp_terms_rel ON (wp_posts.ID = wp_terms_rel.object_id)
            INNER JOIN " . $wp_postmeta . " as wp_postmeta ON (wp_posts.ID = wp_postmeta.post_id) 
            INNER JOIN " . $wp_terms . " as wp_taxonmy ON (wp_terms_rel.term_taxonomy_id = wp_taxonmy.term_id)
            INNER JOIN " . $wp_users . " as wp_users ON (wp_posts.post_author = wp_users.ID)
            INNER JOIN " . $wp_usermeta . " as wp_usermeta ON wp_users.ID = wp_usermeta.user_id
            WHERE (((wp_taxonmy.name LIKE '%%%s%%' ) 
            OR (wp_users.display_name LIKE '%%%s%%') 
            OR (wp_usermeta.meta_value LIKE '%%%s%%')
            OR (wp_posts.post_title LIKE '%%%s%%') 
            OR (wp_usermeta.meta_value LIKE '%%%s%%')
            ) OR (wp_postmeta.meta_key = '_aliceblogs_participants' AND wp_postmeta.meta_value LIKE '%%%s%%')) AND wp_posts.post_type = 'post' AND wp_posts.post_status = 'publish' ORDER BY wp_posts.post_date DESC";

            $query_results = $wpdb->get_results($wpdb->prepare($sql, [$search, $search, preg_replace('/\s+/', '_', $search), $search, $search, $search]));

            //var_dump($wpdb->prepare($sql, [$search, $search, $search, $search, $search, $search]));

            $results = [];
            foreach($query_results as $result) {
                $post = [
                    'id'        => $result->ID,
                    'title'     => $result->post_title,
                    'url'       => $result->guid,
                    'thumbnail' => get_the_post_thumbnail_url((int)$result->ID) ? get_the_post_thumbnail_url((int)$result->ID) : plugin_dir_url( __FILE__ ) . 'images/missing_img.svg',
                    'date'      => date('d M Y', strtotime($result->post_date)),
                    'author'    => $result->display_name,
                    'content'   => get_post_field('post_content', $result->ID)
                ];
                array_push($results, $post);
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
        /*
        GET MOST USED TAGS
        $args = [
            'taxonomy' => 'post_tag',
            'orderby' => 'count',
            'order' => 'DESC',
            'number' => 2
        ];
        foreach(get_terms($args) as $tag) {
            if ($tag->slug == 'care' || $tag->slug == 'wordreview') {
                $tags[$tag->slug] = '#' . $tag->name;
            }
        }*/

        $tags = [
            'care' => '#care',
            'wordreview' => '#wordreview'
        ];

        echo json_encode($tags);
        die();
    }
}

new Aliceblogs();