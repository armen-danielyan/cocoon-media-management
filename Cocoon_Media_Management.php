<?php
/**
 * @package Cocoon Media Management
 * @version 0.0.1
 */
/*
Plugin Name: Cocoon Media Management
Plugin URI: http://www.use-cocoon.nl/
Description: Load images from Cocoon.
Version: 0.0.4
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
define('PLUGINDOMAIN', 'Cocoon-Media-Management');

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
	add_menu_page( __('Cocoon Plugin Settings', PLUGINDOMAIN), __('Cocoon Settings', PLUGINDOMAIN), 'administrator', __FILE__, 'cocoon_settings_page', plugins_url( '/img/icon.png', __FILE__ ) );
	add_action( 'admin_init', 'register_cocoon_settings' );
}

function register_cocoon_settings() {
	register_setting( 'cocoon-main-group', 'cn_domain' );
	register_setting( 'cocoon-main-group', 'cn_secret' );
	register_setting( 'cocoon-main-group', 'cn_username' );
}

function cocoon_settings_page() { ?>
    <div class="wrap">
        <h1><?php _e('Cocoon Settings', PLUGINDOMAIN); ?></h1>

        <form method="post" action="options.php">
			<?php settings_fields( 'cocoon-main-group' ); ?>
			<?php do_settings_sections( 'cocoon-main-group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Domain', PLUGINDOMAIN); ?></th>
                    <td><input type="text" class="regular-text" name="cn_domain"
                               value="<?php echo esc_attr( get_option( 'cn_domain' ) ); ?>"/>
                        <p><i><?php _e('Subdomain', PLUGINDOMAIN); ?></i></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Username', PLUGINDOMAIN); ?></th>
                    <td><input type="text" class="regular-text" name="cn_username"
                               value="<?php echo esc_attr( get_option( 'cn_username' ) ); ?>"/>
                        <p><i><?php _e('Your Username', PLUGINDOMAIN); ?></i></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Secret Key', PLUGINDOMAIN); ?></th>
                    <td><input type="password" class="regular-text" name="cn_secret"
                               value="<?php echo esc_attr( get_option( 'cn_secret' ) ); ?>"/>
                        <p><i><?php _e('Your Secret Key', PLUGINDOMAIN); ?></i></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Settings', PLUGINDOMAIN); ?>"></th>
                    <td>
                        <p style="display: none" id="cn-thumb-up"><img width="32" height="32" src="<?php echo plugin_dir_url( __FILE__ ) . 'img/thumb_up.png' ?>"> <?php _e('Cocoon account successfully connected.', PLUGINDOMAIN); ?></p>
                        <p style="display: none" id="cn-thumb-down"><img width="32" height="32" src="<?php echo plugin_dir_url( __FILE__ ) . 'img/thumb_down.png' ?>"> <?php _e('You have entered wrong Cocoon API credentials, please try again.', PLUGINDOMAIN); ?></p>
                        <?php if (!get_option( 'cn_domain' ) || !get_option( 'cn_username' ) || !get_option( 'cn_secret' )) { ?>
                            <p id="cn-error-msg"><?php _e('Please enter your Cocoon API credentials.', PLUGINDOMAIN); ?></p>;
                        <?php } ?>
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
	$tabs['cocoonmediabank'] = __('Cocoon Media Management', PLUGINDOMAIN);

	return $tabs;
}

add_action( 'media_upload_cocoonmediabank', 'cnAddNewForm' );

function cnAddNewForm() {
	wp_iframe( 'cnNewForm');
}

function cnNewForm() {
	media_upload_header(); ?>

    <div id="cn-wrap">
        <div id="cn-loader">
            <span></span>
        </div>

        <div id="cn-sidebar">
            <div class="cn-sidebar-wrap">
                <h2><?php _e('Available sets', PLUGINDOMAIN); ?></h2>
                <ul id="cn-sets-list"></ul>
            </div>
        </div>

        <div id="cn-header">
            <div id="cn-search-wrap" style="display: inline-block">
                <input type="text" id="cn-form-search" placeholder="<?php _e('Search media items...', PLUGINDOMAIN); ?>" value="">
                <button type="button" class="button" id="cn-form-search-submit"><?php _e('Search', PLUGINDOMAIN); ?></button>
            </div>
        </div>

        <div id="cn-content"></div>

        <div id="cn-sidebar-right">
            <div class="cn-sidebar-wrap" style="display: none">
                <h2><?php _e('Attachment Details', PLUGINDOMAIN); ?></h2>
                <div class="cn-thumb-details-wrap">
                    <div class="cn-thumb-preview">
                        <img id="cn-form-img" src="">
                    </div>
                    <div class="cn-thumb-details">
                        <div id="cn-form-name"></div>
                        <div id="cn-form-uploaded"></div>
                        <div id="cn-form-size"></div>
                        <div id="cn-form-dim"></div>
                    </div>
                </div>

                <label class="cn-form-label-wrap">
                    <span><?php _e('URL', PLUGINDOMAIN); ?></span>
                    <input type="text" id="cn-form-url" value="" disabled="disabled">
                </label>

                <label class="cn-form-label-wrap">
                    <span><?php _e('Title', PLUGINDOMAIN); ?></span>
                    <input type="text" id="cn-form-title" value="">
                </label>

                <label class="cn-form-label-wrap">
                    <span><?php _e('Caption', PLUGINDOMAIN); ?></span>
                    <textarea id="cn-form-caption"></textarea>
                </label>

                <label class="cn-form-label-wrap">
                    <span><?php _e('Alt Text', PLUGINDOMAIN); ?></span>
                    <input type="text" id="cn-form-alt" value="">
                </label>

                <label class="cn-form-label-wrap">
                    <span><?php _e('Size', PLUGINDOMAIN); ?></span>
                    <select id="cn-form-thumb-size">
	                    <?php $imageSizes = get_image_sizes();
	                    foreach($imageSizes as $imageSize) { ?>
                            <option value="<?php echo $imageSize['name']; ?>"><?php echo ucfirst($imageSize['name']) . ' - ' . $imageSize['width'] . ' x ' . $imageSize['height']; ?></option>
                        <?php } ?>
                        <option value="full"></option>
                    </select>
                </label>
            </div>
        </div>

        <div id="cn-footer">
            <button type="button" class="button button-primary" disabled="disabled" id="cn-form-insert"><?php _e('Insert into post', PLUGINDOMAIN); ?></button>
        </div>
    </div>
<?php
}

function get_image_sizes() {
    $sizes = array();
    $get_intermediate_image_sizes = get_intermediate_image_sizes();
    foreach( $get_intermediate_image_sizes as $_size ) {
        if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
            $tmp['name'] = $_size;
	        $tmp['width'] = get_option( $_size . '_size_w' );
	        $tmp['height'] = get_option( $_size . '_size_h' );

	        array_push($sizes, $tmp);
        }
    }
    return $sizes;
}


add_action( 'wp_ajax_get_files_by_set', 'getFilesBySet' );
add_action( 'wp_ajax_nopriv_get_files_by_set', 'getFilesBySet' );

function getFilesBySet() {
	global $cocoonController;
	ob_start();

	if ( !$_POST['setId'] ) {
		return json_encode( array( 'status' => 'error', 'statusMsg' => 'Nothing has found!' ) );
	}

	$keyword = $_POST['keyword'] ? $_POST['keyword'] : '';

	$setId = $_POST['setId'];
	$pageNo = $_POST['pageNo'];
	$pagePer = 48;
	$setFiles = [];

	if($setId === 'all') {
		$sets = $cocoonController->getSets();
		foreach ($sets as $set) {
			$setFiles = array_merge($setFiles, $cocoonController->getFilesBySet( $set['id'] ));
		}
    } else if($setId === 'search') {
		$setFilesTmp = [];
		$sets = $cocoonController->getSets();

		foreach ($sets as $set) {
			$setFilesTmp = array_merge($setFilesTmp, $cocoonController->getFilesBySet( $set['id'] ));
			foreach ( $setFilesTmp as $setFile ) {
				if (strpos($setFile['title'], $keyword) !== false || !$keyword) {
					array_push($setFiles, $setFile);
				}
			}
		}

    } else {
		$setFiles = $cocoonController->getFilesBySet( $setId );
    }

	$thumbsCount = sizeof($setFiles);
	$offset = $pageNo * $pagePer;
	$max = $offset + $pagePer > $thumbsCount ? $thumbsCount : $offset + $pagePer;

	for($i = $offset; $i < $max; $i++) {
		$thumbInfo = $cocoonController->getThumbInfo( $setFiles[$i]['id'] ); ?>
        <div class="cn-thumb" data-cnid="<?php echo $setFile['id']; ?>"
             data-cnpath="<?php echo $thumbInfo['path']; ?>"
             data-cnext="<?php echo $thumbInfo['ext']; ?>"
             data-cnname="<?php echo $thumbInfo['name']; ?>"
             data-cnsize="<?php echo $thumbInfo['size']; ?>"
             data-cndim="<?php echo $thumbInfo['dim']; ?>"
             data-cnuploaded="<?php echo $thumbInfo['uploaded']; ?>"
             data-web="<?php echo $thumbInfo['web']; ?>">
            <div class="cn-image" style="background-image: url('<?php echo $thumbInfo["web"]; ?>')"></div>
            <div class="cn-title"><?php echo $setFiles[$i]['title']; ?></div>
        </div>
	<?php }

	$html = ob_get_contents();
	ob_end_clean();
	echo $html;

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
	$photoSize = $_POST['size'];

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

    $html = wp_get_attachment_image( $attach_id, $photoSize, '', $attr );

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

add_action( 'wp_ajax_cn_get_sets', 'cnGetSets' );
add_action( 'wp_ajax_nopriv_cn_get_sets', 'cnGetSets' );

function cnGetSets() {
	global $cocoonController;

	$sets = $cocoonController->getSets();
    ob_start();
    $thumbsCount = 0;
    foreach ( $sets as $set ) { ?>
        <li>
            <input type="radio"
                   id="<?php echo 'cn' . $set['id']; ?>"
                   class="cn-sets"
                   name="sets"
                   value="<?php echo $set['id']; ?>" <?php if($set['file_count'] == 0) echo 'disabled'; ?>>
            <label for="<?php echo 'cn' . $set['id']; ?>"><?php echo $set['title'] . ' (' . $set['file_count'] . ')'; ?></label>
        </li>
    <?php
	    $thumbsCount += (int) $set['file_count'];
    } ?>
    <li>
        <input type="radio"
               id="cnall"
               class="cn-sets"
               name="sets"
               value="all" <?php if($thumbsCount == 0) echo 'disabled'; ?>>
        <label for="cnall"><?php _e('All', PLUGINDOMAIN); ?> (<?php echo $thumbsCount; ?>)</label>
    </li>
    <?php $html = ob_get_contents();
	ob_end_clean();
    echo $html;
	exit;
}