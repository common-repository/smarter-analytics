<?php

/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function smarter_analytics_add_custom_box() {

    $screens = get_post_types('', 'names');

    foreach ( $screens as $screen ) {
        add_meta_box(
			 'smarter_analytics_metabox',
             __( 'Smarter Analytics', 'smarter_analytics_textdomain' ),
            'smarter_analytics_render_meta_box_content',
            $screen
        );
    }
}
add_action( 'add_meta_boxes', 'smarter_analytics_add_custom_box' );

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function smarter_analytics_render_meta_box_content( $post ) {

    // Add an nonce field so we can check for it later.
    wp_nonce_field( 'smarter_analytics_render_meta_box_content', 'smarter_analytics_custom_box_nonce' );


	// Use get_post_meta to retrieve an existing value from the database.
	$value = get_post_meta( $post->ID, 'smarter_analytics_code', true );

	// Display the form, using the current value.
	echo '<label for="smarter_analytics_code_label">';
	_e( 'Choose the Google Analytics code for this page:', 'smarter_analytics_textdomain' );
	echo '</label> ';
    
    $options = get_option( 'smarter_analytics_option', '' );
    $codes = explode_codes($options["codes"]);
    
    array_unshift($codes, "no-show");
    array_unshift($codes, "");
    
    echo '<select id="smarter_analytics_code" name="smarter_analytics_code">';
    
    $first_blank_rendered = false;
    foreach ($codes as $code) {
        switch ($code) {
            case "": $option_display = "Use post type default code"; break;
            case "no-show": $option_display  = "Do not include an analytics code on this page"; break;
            default: $option_display  = $code; break;
        }
        
        if ($first_blank_rendered == false || $code != "") {
            $selected = ($code == $value) ? " selected" : "";
            echo '  <option value="' . $code . '"' . $selected . '>' . $option_display . '</option>';
            $first_blank_rendered = true;
        }
    }
    
    echo '</select>';
    
    echo '<p><em>This will override the page/post default and the global default.</em></p>';
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function smarter_analytics_save_postdata( $post_id ) {
    if ( ! isset( $_POST['smarter_analytics_custom_box_nonce'] ) ) return $post_id;
    $nonce = $_POST['smarter_analytics_custom_box_nonce'];
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  return $post_id;

    if ( 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) return $post_id;        
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;
    }

    update_post_meta( $post_id, 'smarter_analytics_code', sanitize_text_field( $_POST['smarter_analytics_code'] ) );
}
add_action( 'save_post', 'smarter_analytics_save_postdata' );