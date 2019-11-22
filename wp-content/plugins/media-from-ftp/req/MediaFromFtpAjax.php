<?php
/**
 * Media from FTP
 * 
 * @package    Media from FTP
 * @subpackage MediafromFtpAjax
/*  Copyright (c) 2013- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

$mediafromftpajax = new MediaFromFtpAjax();

class MediaFromFtpAjax {

	private $upload_dir;
	private $upload_url;
	private $is_add_on_activate;

	/* ==================================================
	 * Construct
	 * @since	9.81
	 */
	public function __construct() {

		$plugin_base_dir = untrailingslashit(plugin_dir_path( __DIR__ ));
		$slugs = explode('/', $plugin_base_dir);
		$slug = end($slugs);
		$plugin_dir = untrailingslashit(rtrim($plugin_base_dir, $slug));

		if(!class_exists('MediaFromFtp')){
			include_once $plugin_base_dir.'/inc/MediaFromFtp.php';
		}
		$mediafromftp = new MediaFromFtp();
		list($this->upload_dir, $this->upload_url, $upload_path) = $mediafromftp->upload_dir_url_path();

		$category_active = FALSE;
		if( function_exists('media_from_ftp_add_on_category_load_textdomain') ){
			include_once $plugin_dir.'/media-from-ftp-add-on-category/inc/MediaFromFtpAddOnCategory.php';
			$category_active = TRUE;
		}

		$this->is_add_on_activate = array(
			'category'	=>	$category_active
			);

		$action1 = 'mediafromftp-update-ajax-action';
		$action2 = 'mediafromftp-import-ajax-action';
		add_action( 'wp_ajax_'.$action1, array($this, 'mediafromftp_update_callback') );
		add_action( 'wp_ajax_mediafromftp_message', array($this, 'mediafromftp_message_callback') );
		add_action( 'wp_ajax_'.$action2, array($this, 'mediafromftp_medialibraryimport_update_callback') );
		add_action( 'wp_ajax_mediafromftp_medialibraryimport_message', array($this, 'mediafromftp_medialibraryimport_message_callback') );

	}

	/* ==================================================
	 * Update Files Callback
	 * 
	 * @since	9.30
	 */
	public function mediafromftp_update_callback(){

		$action1 = 'mediafromftp-update-ajax-action';
	    if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], $action1 ) ) {
	        if ( current_user_can( 'upload_files' ) ) {
				$maxcount = intval($_POST["maxcount"]);
				$new_url_attach = sanitize_text_field($_POST["new_url"]);
				$new_url_datetime = sanitize_text_field($_POST["new_datetime"]);
				$new_url_mlccategory = NULL;
				$new_url_emlcategory = NULL;
				$new_url_mlacategory = NULL;
				$new_url_mlatags = NULL;
				if ( $this->is_add_on_activate['category'] ) {
					$new_url_mlccategory = sanitize_text_field($_POST["new_mlccategory"]);
					$new_url_emlcategory = sanitize_text_field($_POST["new_emlcategory"]);
					$new_url_mlacategory = sanitize_text_field($_POST["new_mlacategory"]);
					$new_url_mlatags = sanitize_text_field($_POST["new_mlatags"]);
				}

				$mediafromftp_settings = get_option($this->wp_options_name());

				if (!empty($new_url_attach)) {

					$mediafromftp = new MediaFromFtp();

					$dateset = $mediafromftp_settings['dateset'];
					$datefixed = $mediafromftp_settings['datefixed'];
					$yearmonth_folders = get_option('uploads_use_yearmonth_folders');
					$exif_text_tag = NULL;
					if ( $mediafromftp_settings['caption']['apply'] ) {
						$exif_text_tag = $mediafromftp_settings['caption']['exif_text'];
					}

					$exts = explode('.', wp_basename($new_url_attach));
					$ext = end($exts);

					// Delete Cash
					$mediafromftp->delete_cash($ext, $new_url_attach);

					// Regist
					list($attach_id, $new_attach_title, $new_url_attach, $metadata) = $mediafromftp->regist($ext, $new_url_attach, $new_url_datetime, $dateset, $datefixed, $yearmonth_folders, $mediafromftp_settings['character_code'], $mediafromftp_settings['cron']['user']);

					$cat_html = NULL;
					$mlccategory = NULL;
					$emlcategory = NULL;
					$mlacategory = NULL;
					$mlatag = NULL;
					if ( $this->is_add_on_activate['category'] ) {
						$mediafromftpaddoncategory = new MediaFromFtpAddOnCategory();
						list($cat_html, $cat_text, $mlccategory, $emlcategory, $mlacategory, $mlatag) = $mediafromftpaddoncategory->regist_term($attach_id, $new_url_mlccategory, $new_url_emlcategory, $new_url_mlacategory, $new_url_mlatags);
						unset($mediafromftpaddoncategory);
					}

					if ( $attach_id == -1 || $attach_id == -2 ) { // error
						$error_title = $mediafromftp->mb_utf8($new_attach_title, $mediafromftp_settings['character_code']);
						$error_url = $mediafromftp->mb_utf8($new_url_attach, $mediafromftp_settings['character_code']);
						if ( $attach_id == -1 ) {
							echo '<div class="notice notice-error is-dismissible"><ul><li>'.'<div>'.__('File name:').$error_title.'</div>'.'<div>'.__('Directory name:', 'media-from-ftp').$error_url.'</div>'.sprintf(__('<div>You need to make this directory writable before you can register this file. See <a href="%1$s" target="_blank">the Codex</a> for more information.</div><div>Or, filename or directoryname must be changed of illegal. Please change Character Encodings for Server of <a href="%2$s">Settings</a>.</div>', 'media-from-ftp'), 'https://codex.wordpress.org/Changing_File_Permissions', admin_url('admin.php?page=mediafromftp-settings')).'</li></div>';
						} else if ( $attach_id == -2 ) {
							echo '<div class="notice notice-error is-dismissible"><ul><li><div>'.__('Title').': '.$error_title.'</div>'.'<div>URL: '.$error_url.'</div><div>'.__('This file could not be registered in the database.', 'media-from-ftp').'</div></li></ul></div>';
						}
					} else {
						// Outputdata
						list($imagethumburls, $mimetype, $length, $stamptime, $file_size, $exif_text) = $mediafromftp->output_metadata($ext, $attach_id, $metadata, $mediafromftp_settings['character_code'], $exif_text_tag);

						$image_attr_thumbnail = wp_get_attachment_image_src($attach_id, 'thumbnail', true);

						$output_html = $mediafromftp->output_html_and_log($ext, $attach_id, $new_attach_title, $new_url_attach, $imagethumburls, $mimetype, $length, $stamptime, $file_size, $exif_text, $image_attr_thumbnail, $mediafromftp_settings, $cat_html, $mlccategory, $emlcategory, $mlacategory, $mlatag);

						header('Content-type: text/html; charset=UTF-8');
						echo $output_html;

					}
					unset($mediafromftp);

				}
			}
		} else {
			status_header( '403' );
			echo 'Forbidden';
		}

		wp_die();

	}

	/* ==================================================
	 * Update Messages Callback
	 * 
	 * @since	9.30
	 */
	public function mediafromftp_message_callback(){

		$error_count = intval($_POST["error_count"]);
		$error_update = sanitize_text_field($_POST["error_update"]);
		$success_count = intval($_POST["success_count"]);

		$output_html = NULL;
		if ( $error_count > 0 ) {
			$error_message = sprintf(__('Errored to the registration of %1$d files.', 'media-from-ftp'), $error_count);
			$output_html .= '<div class="notice notice-error is-dismissible"><ul><li><div>'.$error_message.'</div>'.$error_update.'</li></ul></div>';
		}
		$success_message = sprintf(__('Succeeded to the registration of %1$d files.', 'media-from-ftp'), $success_count);
		$output_html .= '<div class="notice notice-success is-dismissible"><ul><li><div>'.$success_message.'</li></ul></div>';

		header('Content-type: text/html; charset=UTF-8');
		echo $output_html;

		wp_die();

	}

	/* ==================================================
	 * Import Files Callback
	 * 
	 * @since	9.40
	 */
	public function mediafromftp_medialibraryimport_update_callback(){

		$action2 = 'mediafromftp-import-ajax-action';
	    if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], $action2 ) ) {
	        if ( current_user_can( 'upload_files' ) ) {
				$file = sanitize_text_field($_POST["file"]);
				$filepath = str_replace($this->upload_dir.'/' , '', $file);
				if ( is_file($file) ) {
					if ( !empty($_POST["db_array"]) ) {
						$db_array = $_POST["db_array"];
						global $wpdb;
						$table_name = $wpdb->prefix.'posts';
						$wpdb->insert( $table_name, $db_array );
						update_attached_file( intval($db_array['ID']), $filepath ) ;
						if ( !empty($_POST["db_wp_attachment_metadata"]) ) {
							$metadata_json = stripslashes($_POST["db_wp_attachment_metadata"]);
							$metadata = json_decode($metadata_json);
							$table_meta_name = $wpdb->prefix.'postmeta';
							$db_meta_array = array(
											"post_id"	=>	intval($db_array['ID']),
											"meta_key"	=>	'_wp_attachment_metadata',
											"meta_value"	=>	$metadata
											);
							$wpdb->insert( $table_meta_name, $db_meta_array );
						}
						if ( !empty($_POST["db_thumbnail_id"]) ) {
							update_post_meta( intval($db_array['ID']), '_thumbnail_id', intval($_POST["db_thumbnail_id"]) );
						}
						if ( !empty($_POST["db_cover_hash"]) ) {
							update_post_meta( intval($db_array['ID']), '_cover_hash', sanitize_text_field($_POST["db_cover_hash"]) );
						}
						if ( !empty($_POST["db_wp_attachment_image_alt"]) ) {
							update_post_meta( intval($db_array['ID']), '_wp_attachment_image_alt', sanitize_text_field($_POST["db_wp_attachment_image_alt"]) );
						}
						$msg = 'success_db';
						$output_html = $msg.','.'<div>'.__('Media').': <a href="'.get_permalink($db_array['ID']).'" target="_blank" style="text-decoration: none; color: green;">'.$this->esc_title($db_array['post_title']).'</a>: '.'<a href="'.$this->upload_url.'/'.$filepath.'" target="_blank" style="text-decoration: none;">'.$filepath.'</a></div>';
					} else {
						$msg = 'success';
						$output_html = $msg.','.'<div>'.__('Thumbnail').': '.'<a href="'.$this->upload_url.'/'.$filepath.'" target="_blank" style="text-decoration: none;">'.$filepath.'</a></div>';
					}
				} else {
					$error_string = __('No file!', 'media-from-ftp');
					$msg = '<div>'.$filepath.': '.$error_string.'</div>';
					$output_html = $msg.','.'<div>'.$filepath.'<span style="color: red;"> &#8811; '.$error_string.'</span></div>';
				}

				header('Content-type: text/html; charset=UTF-8');
				echo $output_html;
			}
		} else {
			status_header( '403' );
			echo 'Forbidden';
		}

		wp_die();

	}

	/* ==================================================
	 * Import Messages Callback
	 * 
	 * @since	9.40
	 */
	public function mediafromftp_medialibraryimport_message_callback(){

		$error_count = intval($_POST["error_count"]);
		$error_update = sanitize_text_field($_POST["error_update"]);
		$success_count = intval($_POST["success_count"]);
		$db_success_count = intval($_POST["db_success_count"]);

		$output_html = NULL;
		if ( $error_count > 0 ) {
			$error_message = sprintf(__('Errored to the registration of %1$d files.', 'media-from-ftp'), $error_count);
			$output_html .= '<div class="notice notice-error is-dismissible"><ul><li><div>'.$error_message.'</div>'.$error_update.'</li></ul></div>';
		}
		$success_message = sprintf(__('Succeeded to the registration of %1$d files and %2$d items for MediaLibrary.', 'media-from-ftp'), $success_count, $db_success_count);
		$output_html .= '<div class="notice notice-success is-dismissible"><ul><li><div>'.$success_message.'</li></ul></div>';

		header('Content-type: text/html; charset=UTF-8');
		echo $output_html;

		wp_die();

	}

	/* ==================================================
	 * Escape Title
	 * @param	string	$str
	 * @return	string	$str
	 * @since	9.41
	 */
	private function esc_title($str){

		$str = esc_html($str);
		$str = str_replace(',', '&#44;', $str);

		return $str;
	}

	/* ==================================================
	 * @param	none
	 * @return	string	$this->wp_options_name()
	 * @since	10.09
	 */
	private function wp_options_name() {
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include_once(ABSPATH . 'wp-includes/pluggable.php');
		}
		return 'mediafromftp_settings'.'_'.get_current_user_id();
	}

}

?>