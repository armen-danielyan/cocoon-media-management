<?php
/**
 * @package Cocoon Media Management
 * @version 0.0.1
 */
/*
Plugin Name: Cocoon Media Management
Plugin URI: http://www.use-cocoon.nl/
Description: Load images from Cocoon.
Version: 0.0.3
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

add_action( 'admin_enqueue_scripts', 'addCocoonScripts' );

function addCocoonScripts() {
	wp_enqueue_script( 'cn_main_js', plugin_dir_url( __FILE__ ) . 'js/main.js', '', VERSION );
	wp_localize_script( 'cn_main_js', 'wp_vars', array(
		'ajax_url' => admin_url( 'admin-ajax.php' )
	) );

	wp_enqueue_style( 'cn_main_css', plugin_dir_url( __FILE__ ) . 'css/main.css', '', VERSION );
}

add_action( 'admin_menu', 'cocoonSettingsPage' );

function cocoonSettingsPage() {
	add_menu_page( 'Cocoon Plugin Settings', 'Cocoon Settings', 'administrator', __FILE__, 'cocoon_settings_page', plugins_url( '/img/icon.png', __FILE__ ) );
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

                <tr valign="top">
                    <th scope="row"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings"></th>
                    <td>
                        <p style="display: none" id="cn-thumb-up"><img width="32" height="32" src="<?php echo plugin_dir_url( __FILE__ ) . 'img/thumb_up.png' ?>"> Cocoon account successfully connected.</p>
                        <p style="display: none" id="cn-thumb-down"><img width="32" height="32" src="<?php echo plugin_dir_url( __FILE__ ) . 'img/thumb_down.png' ?>"> You have entered wrong Cocoon API credentials, please try again.</p>
                        <?php if (!get_option( 'cn_domain' ) || !get_option( 'cn_username' ) || !get_option( 'cn_secret' )) {
                            echo '<p id="cn-error-msg">Please enter your Cocoon API credentials.</p>';
                        } ?>
                    </td>
                </tr>
            </table>
        </form>
    </div>
<?php }


/**
 * Make Media Upload Tab
 */

add_filter( 'media_upload_tabs', 'cnUploadTab' );

function cnUploadTab( $tabs ) {
	$tabs['cocoonmediabank'] = "Cocoon Media Management";

	return $tabs;
}

add_action( 'media_upload_cocoonmediabank', 'cnAddNewForm' );

function cnAddNewForm() {
	wp_iframe( 'cnNewForm');
}

function cnNewForm() {
	global $cocoonController;

	media_upload_header(); ?>

    <div id="cn-wrap">
        <div id="cn-loader">
            <span></span>
        </div>

        <div id="cn-sidebar">
            <script>
                jQuery(function($) {
                    $("#cn-loader").show();
                    $.ajax({
                        url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                        data: {
                            action: "cn_get_sets"
                        },
                        type: "POST",
                        success: function (res) {
                            $("#cn-loader").hide();
                            $("#cn-sets-list").html(res);
                        }
                    });
                });
            </script>
            <h2>Available sets</h2>
            <ul id="cn-sets-list"></ul>
        </div>

        <div id="cn-content"></div>

        <div id="cn-sidebar-right">
            <div id="cn-sidebar-right-wrap" style="display: none">
                <table>
                    <tr>
                        <th colspan="2"><h2>Attachment Details</h2></th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div style="width: 38%; display: inline-block">
                                <img id="cn-form-img" src="" width="100%">
                            </div>
                            <div style="width: 60%; display: inline-block">
                                <p id="cn-form-name"></p>
                                <p id="cn-form-size"></p>
                                <p id="cn-form-dim"></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>URL</td>
                        <td><input type="text" id="cn-form-url" value="http://www.ebtb.com" disabled="disabled"></td>
                    </tr>
                    <tr>
                        <td>Title</td>
                        <td><input type="text" id="cn-form-title" value=""></td>
                    </tr>
                    <tr>
                        <td>Caption</td>
                        <td><textarea id="cn-form-caption" cols="30" rows="4"></textarea></td>
                    </tr>
                    <tr>
                        <td>Alt Text</td>
                        <td><input type="text" id="cn-form-alt" value=""></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div id="cn-footer">
        <div style="display: inline-block">
            <input type="text" id="cn-form-search" value="">
            <button type="button" class="button media-button button-primary button-large media-button-select" id="cn-form-search-submit">Search</button>
        </div>
        <button type="button" class="button media-button button-primary button-large media-button-select" disabled="disabled" id="cn-form-insert">Insert into post</button>
    </div>
<?php
}


add_action( 'wp_ajax_get_files_by_set', 'getFilesBySet' );
add_action( 'wp_ajax_nopriv_get_files_by_set', 'getFilesBySet' );

function getFilesBySet() {
	global $cocoonController;

	if ( !$_POST['setId'] ) {
		return json_encode( array( 'status' => 'error', 'statusMsg' => 'Nothing has found!' ) );
	}

	$setId    = $_POST['setId'];
	$setFiles = $cocoonController->getFilesBySet( $setId );

	foreach ( $setFiles as $setFile ) {
		$thumbInfo = $cocoonController->getThumbInfo( $setFile['id'] ); ?>
        <div class="cn-thumb" data-cnid="<?php echo $setFile['id']; ?>"
             data-cnpath="<?php echo $thumbInfo["path"]; ?>"
             data-cnext="<?php echo $thumbInfo["ext"]; ?>"
             data-cnname="<?php echo $thumbInfo["name"]; ?>"
             data-web="<?php echo $thumbInfo["web"]; ?>">
            <div class="cn-image" style="background-image: url('<?php echo $thumbInfo["web"]; ?>')"></div>
            <div class="cn-title"><?php echo $setFile['title']; ?></div>
        </div>
	<?php }

	exit;
};


add_action( 'wp_ajax_cn_upload_image', 'uploadImage' );
add_action( 'wp_ajax_nopriv_cn_upload_image', 'uploadImage' );

function uploadImage() {
	global $post;

	if ( !isset($_POST['path']) || !isset($_POST['ext']) || !isset($_POST['name']) ) {
		return json_encode( array( 'status' => 'error', 'statusMsg' => 'Something went wrong!' ) );
	}

	$thumbURL = $_POST['path'];
	$fileNameExt = $_POST['ext'];
	$photoName = $_POST['name'];

	$res = wp_remote_get( $thumbURL );

	$attachment = wp_upload_bits( $photoName . '.' .$fileNameExt, null, $res['body'], date( 'Y-m') );

	$fileType = wp_check_filetype( basename( $attachment['file'] ), null );

	$postinfo    = array(
		'post_mime_type' => $fileType['type'],
		'post_title'     => $photoName,
		'post_content'   => '',
		'post_status'    => 'inherit',
	);
	$filename    = $attachment['file'];
	$attach_id   = wp_insert_attachment( $postinfo, $filename, $post->id );

	$my_image_meta = array('ID' => $attach_id);
	$attr = [];

	if($_POST['alt']) update_post_meta($attach_id, '_wp_attachment_image_alt', $_POST['alt']);
	if($_POST['title']) {
	    $my_image_meta['post_title'] = $_POST['title'];
		$attr['title'] = $_POST['title'];
	}
	if($_POST['caption']) $my_image_meta['post_excerpt'] = $_POST['caption'];
	if(sizeof($my_image_meta) > 1) wp_update_post( $my_image_meta );

	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	wp_update_attachment_metadata( $attach_id, $attach_data );

    $html = wp_get_attachment_image( $attach_id, 'full', '', $attr );

	echo json_encode( array( 'status' => 'OK', 'data' => $html ) );

	exit;
}


add_action( 'wp_ajax_check_creds', 'checkCreds' );
add_action( 'wp_ajax_nopriv_check_creds', 'checkCreds' );

function checkCreds() {
	global $cocoonController;

	$result = $cocoonController->getVersion();
	if($result)
		echo json_encode( array( 'status' => 'OK' ) );
	else
		echo json_encode( array( 'status' => 'error' ) );

	exit;
}


add_action( 'wp_ajax_cn_search', 'cnSearch' );
add_action( 'wp_ajax_nopriv_cn_search', 'cnSearch' );

function cnSearch() {
	global $cocoonController;
	$keyword = $_POST['keyword'] ? $_POST['keyword'] : '';

	$sets = $cocoonController->getSets();

	foreach ($sets as $set) {
		$setFiles = $cocoonController->getFilesBySet( $set['id'] );
		foreach ( $setFiles as $setFile ) {
			if (strpos($setFile['title'], $keyword) !== false || !$keyword) { ?>
                <?php $thumbInfo = $cocoonController->getThumbInfo( $setFile['id'] ); ?>
                <div class="cn-thumb" data-cnid="<?php echo $setFile['id']; ?>"
                     data-cnpath="<?php echo $thumbInfo["path"]; ?>"
                     data-cnext="<?php echo $thumbInfo["ext"]; ?>"
                     data-cnname="<?php echo $thumbInfo["name"]; ?>"
                     data-web="<?php echo $thumbInfo["web"]; ?>">
                    <div class="cn-image" style="background-image: url('<?php echo $thumbInfo["web"]; ?>')"></div>
                    <div class="cn-title"><?php echo $setFile['title']; ?></div>
                </div>
			<?php } else {
			    echo 'Result not found.';
            }
        }
    }

	exit;
}


add_action( 'wp_ajax_cn_get_sets', 'cnGetSets' );
add_action( 'wp_ajax_nopriv_cn_get_sets', 'cnGetSets' );

function cnGetSets() {
	global $cocoonController;

	$sets = $cocoonController->getSets();
    ob_start();
    foreach ( $sets as $set ) { ?>
        <li>
            <input type="radio"
                   id="<?php echo 'cn' . $set['id']; ?>"
                   class="cn-sets"
                   name="sets"
                   value="<?php echo $set['id']; ?>">
            <label for="<?php echo 'cn' . $set['id']; ?>"><?php echo $set['title'] . ' (' . $set['file_count'] . ')'; ?></label>
        </li>
    <?php }
    $html = ob_get_contents();
	ob_end_clean();
    echo $html;
	exit;
}