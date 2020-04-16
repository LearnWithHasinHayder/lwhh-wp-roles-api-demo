<?php
/*
Plugin Name: Roles & Permissions Demo
Plugin URI:
Description: Demonstration of Roles API
Version: 1.0.0
Author: LWHH
Author URI:
License: GPLv2 or later
Text Domain: roles-demo
 */

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( 'toplevel_page_roles-demo' == $hook ) {
        wp_enqueue_style( 'pure-grid-css', '//unpkg.com/purecss@1.0.1/build/grids-min.css' );
        wp_enqueue_style( 'roles-demo-css', plugin_dir_url( __FILE__ ) . "assets/css/style.css", null, time() );
        wp_enqueue_script( 'roles-demo-js', plugin_dir_url( __FILE__ ) . "assets/js/main.js", array( 'jquery' ), time(), true );
        $nonce = wp_create_nonce( 'roles_display_result' );
        wp_localize_script(
            'roles-demo-js',
            'plugindata',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => $nonce )
        );
    }
} );

add_action( 'wp_ajax_roles_display_result', function () {
    global $roles;
    $table_name = $roles->prefix . 'persons';
    if ( wp_verify_nonce( $_POST['nonce'], 'roles_display_result' ) ) {
        $task = $_POST['task'];
        if ( 'current-user-details' == $task ) {
            $user = wp_get_current_user();
            echo $user->user_email . "<br/>";
            if ( is_user_logged_in() ) {
                echo "Someone is logged in<br/>";
            }
            print_r( $user );

        } elseif ( 'any-user-detail' == $task ) {
            $user = new WP_User( 2 );
            echo $user->user_email . "<br/>";

            print_r( $user );

        } elseif ( 'current-role' == $task ) {
            $user = new WP_User( 2 );
            // $user = wp_get_current_user();
            /* foreach($user->roles as $role){
            echo $role."<br/>";
            } */
            echo $user->roles[0];
        } elseif ( 'all-roles' == $task ) {
            global $wp_roles;
            //print_r($wp_roles);
            foreach ( $wp_roles->roles as $role => $roledetails ) {
                echo "{$role}<br/>";
            }

            $roles = get_editable_roles();
            //print_r($roles);
            echo "<hr/>";

            foreach ( $roles as $role => $roledetails ) {
                echo "{$role}<br/>";
            }
        } elseif ( 'current-capabilities' == $task ) {
            $currentUser = wp_get_current_user();
            print_r( $currentUser->allcaps );
        } elseif ( 'check-user-cap' == $task ) {
            $cap = 'manage_sites';
            $cap = 'manage_options';
            if ( current_user_can( $cap ) ) {
                echo "Yes he/she can do {$cap}<br/>";
            } else {
                echo "No he/she can not do {$cap}<br/>";
            }

            $user = new WP_User( 2 );
            $cap = 'delete_posts';
            if ( $user->has_cap( $cap ) ) {
                echo "Yes {$user->user_nicename} can do {$cap}<br/>";
            } else {
                echo "No {$user->user_nicename} can not do {$cap}<br/>";
            }

        } elseif ( 'create-user' == $task ) {
            echo wp_create_user( 'janedoe', 'abcd1234', 'jane@doe.com' );
        } elseif ( 'set-role' == $task ) {
            $user = new WP_User( 3 );
            $user->remove_role( 'subscriber' );
            $user->add_role( 'author' );
            print_r( $user );
        } elseif ( 'login' == $task ) {
            /* $user = wp_authenticate('janedoe','abcd1234');
            if(is_wp_error($user)){
            echo "Failed";
            }else{
            wp_set_current_user($user->ID);
            echo wp_get_current_user()->user_email;
            wp_set_auth_cookie($user->ID);
            //echo "Success";
            } */

            /* $user = wp_signon( array(
            'user_login'    => 'janedoe',
            'user_password' => 'abcd1234',
            'remember'      => true,
            ) );
            if ( is_wp_error( $user ) ) {
            echo "Failed";
            } else {
            wp_set_current_user( $user->ID );
            echo wp_get_current_user()->user_email;
            //wp_set_auth_cookie( $user->ID );
            //echo "Success";
            } */

            wp_set_auth_cookie( 2 );

        } elseif ( 'users-by-role' == $task ) {
            $users = get_users( array( 'role' => 'editor', 'orderby' => 'user_email', 'order' => 'asc' ) );
            print_r( $users );
        } elseif ( 'change-role' == $task ) {
            $user = new WP_User( 3 );
            $user->remove_role( 'editor' );
            $user->add_role( 'author' );
            print_r( $user );
        } elseif ( 'create-role' == $task ) {
            /* $role = add_role('super_author',__('Super Author','roles-demo'),[
            'read'=>true,
            'delete_posts'=>true,
            'edit_posts'=>true,
            'custom_cap_one'=>true,
            'custom_cap_two'=>false
            ]);
            print_r($role);*/
            $user = new WP_User( 3 );
            $user->add_role( 'super_author' );
            if ( $user->has_cap( 'custom_cap_one' ) ) {
                echo "Jane can do custom_cap_one<br/>";
            }

            if ( !$user->has_cap( 'custom_cap_two' ) ) {
                echo "Jane can not do custom_cap_two<br/>";
            }
            print_r( $user );

        }

    }
    die( 0 );
} );

add_action( 'admin_menu', function () {
    add_menu_page( 'roles Demo', 'Roles Demo', 'manage_options', 'roles-demo', 'rolesdemo_admin_page' );
} );

function rolesdemo_admin_page() {
    ?>
        <div class="container" style="padding-top:20px;">
            <h1>Roles Demo</h1>
            <div class="pure-g">
                <div class="pure-u-1-4" style='height:100vh;'>
                    <div class="plugin-side-options">
                        <button class="action-button" data-task='current-user-details'>Get Current User Details</button>
                        <button class="action-button" data-task='any-user-detail'>Get Any User Details</button>
                        <button class="action-button" data-task='current-role'>Detect Any User Role</button>
                        <button class="action-button" data-task='all-roles'>Get All Roles List</button>
                        <button class="action-button" data-task='current-capabilities'>Current User Capability</button>
                        <button class="action-button" data-task='check-user-cap'>Check User Capability</button>
                        <button class="action-button" data-task='create-user'>Create A New User</button>
                        <button class="action-button" data-task='set-role'>Assign Role To A New User</button>
                        <button class="action-button" data-task='login'>Login As A User</button>
                        <button class="action-button" data-task='users-by-role'>Find All Users From Role</button>
                        <button class="action-button" data-task='change-role'>Change User Role</button>
                        <button class="action-button" data-task='create-role'>Create New Role</button>
                    </div>
                </div>
                <div class="pure-u-3-4">
                    <div class="plugin-demo-content">
                        <h3 class="plugin-result-title">Result</h3>
                        <div id="plugin-demo-result" class="plugin-result"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}
