<?php

/**
 * Code inspired from https://github.com/jaredatch/Disable-Users/blob/master/init.php
 * 
 */

defined('ABSPATH' ) or die( 'No script kiddies please!' );

class Aliceblogs_Disable_User {

    public function __construct(){
        add_action('show_user_profile', [$this, 'disable_user_profile']);
        add_action('edit_user_profile', [$this, 'disable_user_profile']);
        add_action('personal_options_update', [$this, 'user_profile_field_save']);
        add_action('edit_user_profile_update', [$this, 'user_profile_field_save']);
        add_action('wp_login', [$this, 'user_login'], 10, 2 );
        add_action('manage_users_custom_column', [$this, 'manage_users_column_content'], 10, 3 );
        add_action('admin_footer-users.php', [$this, 'manage_users_css']);
        add_filter( 'login_message', [$this, 'user_login_message']);
        add_filter( 'manage_users_columns', [$this, 'manage_users_columns']);
    }

    /**
     * Add user profile page disable field
     */
    public function disable_user_profile($user) {
        $allowed_roles = ['administrator', 'aliceblogs_super_teacher'];
        if (array_intersect($user->roles, $allowed_roles)) {
            $user_status_meta = get_user_meta($user->ID, 'aliceblogs_disable_user');
            $user_status_meta = $user_status_meta[0] ?? 0;
            ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th>
                            <label for="disable_user">Désactiver le compte</label>
                        </th>
                        <td>
                            <input type="checkbox" id="aliceblogs_disable_user" name="aliceblogs_disable_user" value="1" <?php checked(1, $user_status_meta); ?> />
                            <span class="description">Si la case est cochée, l'utilisateur ne pourra plus se connecter</span>
                        </td>
                    </tr>
                <tbody>
            </table>
            <?php
        }
    }

    /**
     * Add user disable attribute
     */
    public function user_profile_field_save($user_id) {
        if (!isset($_POST['aliceblogs_disable_user'])) {
            $disabled = 0;
        } else {
            $disabled = $_POST['aliceblogs_disable_user'];
        }
        update_user_meta($user_id, 'aliceblogs_disable_user', $disabled);
    }

    /**
     * After login check to see if user account is disabled
     *
     */
    public function user_login($user_login, $user = null) {
        if (!$user) {
            $user = get_user_by('login', $user_login);
        }
        if (!$user) {
            // not logged in - definitely not disabled
            return;
        }
        // Get user meta
        $disabled = get_user_meta( $user->ID, 'aliceblogs_disable_user', true );
        
        // Is the user logging in disabled?
        if ($disabled == '1') {
            // Clear cookies, a.k.a log user out
            wp_clear_auth_cookie();

            // Build login URL and then redirect
            $login_url = site_url('wp-login.php', 'login');
            $login_url = add_query_arg('disabled', '1', $login_url);
            wp_redirect($login_url);
            exit;
        }
    }
    
    /**
     * Show a notice to users who try to login and are disabled
     *
     */
    public function user_login_message($message) {

        // Show the error message if it seems to be a disabled user
        if (isset( $_GET['disabled']) && $_GET['disabled'] == 1) {
            $message =  '<div id="login_error">' . apply_filters('aliceblogs_disable_users_notice', 'Compte désactivé') . '</div>';
        }

        return $message;
    }

    /**
     * Add custom disabled column to users list
     *
     */
    public function manage_users_columns($defaults) {

        $defaults['aliceblogs_disable_user'] = 'Désactivé';
        return $defaults;
    }
    
    /**
     * Set content of disabled users column
     *
     */
    public function manage_users_column_content($empty, $column_name, $user_ID) {

        if ($column_name == 'aliceblogs_disable_user') {
            if ( get_the_author_meta( 'aliceblogs_disable_user', $user_ID )	== 1 ) {
                return '✔';
            }
        }
    }

    /**
     * Specifiy the width of our custom column
     *
      */
    public function manage_users_css() {
        echo '<style type="text/css">.column-aliceblogs_disable_user { width: 80px; }</style>';
    }
    
}

new Aliceblogs_Disable_User();