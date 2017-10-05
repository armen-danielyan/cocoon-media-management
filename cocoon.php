<?php
/**
 * @package COCOON
 * @version 0.0.1
 */
/*
Plugin Name: Cocoon
Plugin URI: http://www.use-cocoon.nl/
Description: Load images from Cocoon.
Version: 0.0.2
Author: Danielyan
Author URI: http://www.use-cocoon.nl/
License: GPLv2 or later
Text Domain: use-cocoon.nl
 */
?>
<?php

/**
 * Prevent Direct Access
 */

defined( 'ABSPATH' ) or die( 'Direct Access to This File is Not Allowed.' );

require_once( 'lib/CocoonController.php' );

$cocoonController = new Cocoon();

add_action( 'admin_enqueue_scripts', 'addOTUScripts' );

function addOTUScripts() {
	wp_enqueue_script( 'cn_main_js', plugin_dir_url( __FILE__ ) . 'js/main.js', '', VERSION );
	wp_localize_script( 'cn_main_js', 'wp_vars', array(
		'ajax_url' => admin_url( 'admin-ajax.php' )
	) );

	wp_enqueue_style( 'cn_main_css', plugin_dir_url( __FILE__ ) . 'css/main.css', '', VERSION );
}

add_action( 'admin_menu', 'cocoonSettingsPage' );

function cocoonSettingsPage() {
	add_menu_page( 'Cocoon Plugin Settings', 'Cocoon Settings', 'administrator', __FILE__, 'cocoon_settings_page', plugins_url( '/images/icon.png', __FILE__ ) );
	add_action( 'admin_init', 'register_cocoon_settings' );
}


function register_cocoon_settings() {
	register_setting( 'cocoon-main-group', 'cn_domain' );
	register_setting( 'cocoon-main-group', 'cn_secret' );
	register_setting( 'cocoon-main-group', 'cn_username' );
}

function cocoon_settings_page() { ?>
    <div class="wrap">
        <h1>Cocoon Settings</h1>

        <form method="post" action="options.php">
			<?php settings_fields( 'cocoon-main-group' ); ?>
			<?php do_settings_sections( 'cocoon-main-group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Domain</th>
                    <td><input type="text" class="regular-text" name="cn_domain"
                               value="<?php echo esc_attr( get_option( 'cn_domain' ) ); ?>"/>
                        <p><i>Subdomain</i></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Username</th>
                    <td><input type="text" class="regular-text" name="cn_username"
                               value="<?php echo esc_attr( get_option( 'cn_username' ) ); ?>"/>
                        <p><i>Your Username</i></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Secret Key</th>
                    <td><input type="password" class="regular-text" name="cn_secret"
                               value="<?php echo esc_attr( get_option( 'cn_secret' ) ); ?>"/>
                        <p><i>Your Secret Key</i></p></td>
                </tr>
            </table>

            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings">
        </form>
    </div>
<?php }


/**
 * Make Media Upload Tab
 */

add_filter( 'media_upload_tabs', 'cnUploadTab' );

function cnUploadTab( $tabs ) {
	$tabs['mytabname'] = "Cocoon MediaBank";

	return $tabs;
}

add_action( 'media_upload_mytabname', 'cnAddNewForm' );

function cnAddNewForm() {
	wp_iframe( 'cnNewForm' );
}

function cnNewForm() {
	global $cocoonController;

	echo media_upload_header();

	$sets = $cocoonController->getSets(); ?>

    <div id="cn-wrap">
        <div id="cn-header">

        </div>

        <div id="cn-sidebar">
            <ul>
				<?php foreach ( $sets as $set ) { ?>
                    <li>
                        <input type="radio"
                               id="<?php echo 'cn' . $set['id']; ?>"
                               class="cn-sets"
                               name="sets"
                               value="<?php echo $set['id']; ?>">
                        <label for="<?php echo 'cn' . $set['id']; ?>"><?php echo $set['title'] . ' (' . $set['file_count'] . ')'; ?></label>
                    </li>
				<?php } ?>
            </ul>
        </div>

        <div id="cn-content">

        </div>
    </div>
<?php }

add_action( 'wp_ajax_get_files_by_set', 'getFilesBySet' );
add_action( 'wp_ajax_nopriv_get_files_by_set', 'getFilesBySet' );

function getFilesBySet() {
	global $cocoonController;

	if ( ! $_POST['setId'] ) {
		return json_encode( array( 'status' => 'error', 'statusMsg' => 'Nothing has found!' ) );
	}

	$setId    = $_POST['setId'];
	$setFiles = $cocoonController->getFilesBySet( $setId );

	foreach ( $setFiles as $setFile ) {
		$thumbUrl = $cocoonController->getThumbUrl( $setFile['id'] ); ?>
        <div class="cn-thumb" data-cnid="<?php echo $setFile['id']; ?>">
            <div class="cn-image" style="background-image: url('<?php echo $thumbUrl; ?>')"></div>
            <div class="cn-title"><?php echo $setFile['title']; ?></div>
        </div>
	<?php }

	exit;
}

;

/**
 * Upload Image
 */

function uploadImage( $thumbURL, $fileNameExt, $photoName ) {
	global $post;
	$req = new WP_Http();
	$res = $req->request( $thumbURL );

	$attachment = wp_upload_bits( $fileNameExt, null, $res['body'], date( "Y-m", strtotime( $res['headers']['last-modified'] ) ) );

	$fileType = wp_check_filetype( basename( $attachment['file'] ), null );

	$postinfo    = array(
		'post_mime_type' => $fileType['type'],
		'post_title'     => $photoName,
		'post_content'   => '',
		'post_status'    => 'inherit',
	);
	$filename    = $attachment['file'];
	$attach_id   = wp_insert_attachment( $postinfo, $filename, $post->id );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	wp_update_attachment_metadata( $attach_id, $attach_data );
}