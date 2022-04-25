<?php
// function enqueue_scripts() {

// /**
//  * This function is provided for demonstration purposes only.
//  *
//  * An instance of this class should be passed to the run() function
//  * defined in Migrate_Loader as all of the hooks are defined
//  * in that particular class.
//  *
//  * The Migrate_Loader will then create the relationship
//  * between the defined hooks and the functions defined in this
//  * class.
//  */
// wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );	  
//   wp_enqueue_script( $this->plugin_name);
  
//   // Set Nonce Values so that the ajax calls are secure
//   $add_something_nonce = wp_create_nonce( "add_something" );
//   $ajax_url = admin_url( 'admin-ajax.php' );
//   $user_id = get_current_user_id();
//   // pass ajax object to this javascript file
//   // Add nonce values to this object so that we can access them in the plugin-name-admin.js javascript file
//   wp_localize_script( $this->plugin_name, 'plugin_name_ajax_object', 
//       array( 
//         'ajax_url' => $ajax_url,
//         'add_something_nonce'=> $add_something_nonce,
//         'user_id' => $user_id
//     ) 
//   );
// }