<?php
/**
 * PostViewSetting File
 *
 * @file
 * @package Webpage
 * Plugin Name: Webpage View Count
 * Plugin URI: https://techforceglobal.com
 * Description: A simple plugin to add visit count for your WordPress Posts
 * Author: Techforce
 * Author URI: https://techforceglobal.com/
 * Version: 1.2
 * Text Domain: views
 * Domain Path: /languages
 */

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PostViews Class
 *
 * @category PostViews
 * @package   Webpage
 * @author   Techforce
 * @link     https://techforceglobal.com/
 */
class WvcPostViews {

	/**
	 * The deposit owner.
	 *
	 * @var string
	 */
	private $plugin_version = '1.0.0';
	/**
	 * The deposit owner.
	 *
	 * @var string
	 */
	private $plugin_url;
	/**
	 * The deposit owner.
	 *
	 * @var string
	 */
	private $plugin_path;

	/**
	 * PostViews constructor.
	 */
	public function __construct() {
		define( 'WVC_POST_VIEWS_VERSION', $this->plugin_version );
		define( 'WVC_POST_VIEWS_SITE_URL', site_url() );
		define( 'WVC_POST_VIEWS_HOME_URL', home_url() );
		define( 'WVC_POST_VIEWS_URL', $this->plugin_url() );
		define( 'WVC_POST_VIEWS_PATH', $this->plugin_path() );
		register_activation_hook( __FILE__, array( $this, 'activate_handler' ) );
		add_action( 'add_meta_boxes', array( $this, 'post_views_meta_box' ) );
		add_action( 'save_post', array( $this, 'post_views_save_post' ) );

		$post_view_setting_options = get_option( 'post_view_setting_option_name' );
		// post content.
		add_filter( 'the_content', array( $this, 'add_post_views_count' ) );

		$filter = $post_view_setting_options['positions'];
		// bbpress support.
		add_action( 'bbp_template_' . $filter . '_single_topic', array( $this, 'display_bbpress_post_views' ) );
		add_action( 'bbp_template_' . $filter . '_single_forum', array( $this, 'display_bbpress_post_views' ) );

		// Add Shortcode.
		add_shortcode( 'post_vsk', array( $this, 'load_shortcode' ) );
		$this->setup();
		$this->load_assets();
		$this->plugin_includes();
		register_deactivation_hook( __FILE__, array( $this, 'deactivation_handler' ) );
	}
	/**
	 * PostViews constructor.
	 */
	public function plugin_includes() {
		include_once 'class-wvcpostviewsetting.php';
	}
	/**
	 * PostViews constructor.
	 */
	public function activate_handler() {
		add_option( 'post_view_count_plugin_version', $this->plugin_version );
	}
	/**
	 * PostViews constructor.
	 */
	public function deactivation_handler() {
	}
	/**
	 * PostViews constructor.
	 */
	public function plugin_url() {
		if ( $this->plugin_url ) {
			return $this->plugin_url;
		}
		$this->plugin_url = plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) );
		return $this->plugin_url;
	}
	/**
	 * PostViews constructor.
	 */
	public function plugin_path() {
		if ( $this->plugin_path ) {
			return $this->plugin_path;
		}
		$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		return $this->plugin_path;
	}
	/**
	 * PostViews constructor.
	 */
	public function setup() {
		global $table_prefix, $wpdb;

		$version         = get_option( 'post_view_count_plugin_version', '1.0' );
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'post_views';

		$sql = "CREATE TABLE $table_name (
				id int(11) NOT NULL AUTO_INCREMENT,
				pst_id int(11) NOT NULL,
				pcount int(11) NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'post_views_by_user';

		$sql = "CREATE TABLE $table_name (
				id int(11) NOT NULL AUTO_INCREMENT,
				pst_id int(11) NOT NULL,
				user_ip varchar(200) NULL,
				user_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Upgrade the DB Schema.
		if ( version_compare( $version, '2.0' ) < 0 ) {
			update_option( 'post_view_count_plugin_version', '2.0' );
		}
	}
	/**
	 * PostViews constructor.
	 */
	public function load_assets() {
		wp_register_style( 'post.views.css', plugins_url( 'css/style.css', __FILE__ ), array(), '1.0' );
		wp_register_style( 'boot.css', plugins_url( 'css/bootstrap.min.css', __FILE__ ), array(), '1.0' );
		wp_register_style( 'summernote.css', plugins_url( 'editor/summernote.min.css', __FILE__ ), array(), '1.0' );
		wp_register_script( 'boot.js', plugins_url( 'js/bootstrap.min.js', __FILE__ ), array( 'jquery' ), '1.0', true );
		wp_register_script( 'summernote.js', plugins_url( 'editor/summernote.min.js', __FILE__ ), array( 'jquery', 'boot.js' ), '1.0', true );
		wp_register_script( 'custom.js', plugins_url( 'js/custom.js', __FILE__ ), array( 'jquery', 'boot.js', 'summernote.js' ), '1.0', true );
	}
	/**
	 * PostViews constructor.
	 */
	public function load_shortcode() {
		ob_start();
		$post_id = get_the_ID();
		$user_ip = $this->get_the_user_ip();
		// Defualt value.
		$finalcount    = 0;
		$post_restrict = get_option( $post_id . '-post_option' );
		if ( ! is_single() || ( isset( $post_restrict ) && 'on' == $post_restrict ) ) {
			$finalcount = $this->get_total_post_count( $post_id );
		} else {
			$finalcount = $this->set_total_post_count( $post_id, $user_ip );
		}

		$post_view_setting_options = get_option( 'post_view_setting_option_name' );
		$html                      = $post_view_setting_options['html_template'];
		$script                    = $post_view_setting_options['custom_js'];
		$css                       = $post_view_setting_options['custom_css'];
		$html                     .= '<style>' . $css . '</style>';
		$html                     .= '<script>' . $script . '</script>';
		$html                      = str_replace( '{{counter}}', $finalcount, $html );
		if ( 'on' !== $post_restrict ) {
			return $html;
		}
		return ob_get_clean();
	}
	/**
	 * PostViews constructor.
	 */
	public function get_the_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} else {
			if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			}
		}

		return apply_filters( 'wpb_get_ip', $ip );
	}
	/**
	 * PostViews constructor.
	 */
	public function post_views_meta_box() {
		$use_exclude = get_option( 'post_view_setting_option_name' )['use_exclude'];
		if ( 'use_exclude' == $use_exclude ) {
			add_meta_box( 'post_count_restrict', 'Exclude Post?', array( $this, 'display_post_views_meta_box_callback' ), '', 'side', 'default' );
		}
	}
	/**
	 * Post View Setting Sanitize.
	 *
	 * @param String $post_id string.
	 */
	public function display_post_views_meta_box_callback( $post_id ) {
			$title_field = get_option( $post_id->ID . '-post_option' );
			$nonce       = wp_create_nonce( 'custom-action' ); ?>
			<input type="hidden" name="message-send" value='<?php echo esc_html( $nonce ); ?>'>
			<?php
			if ( isset( $title_field ) && 'on' == $title_field ) {
				?>
				<input id="post_exclude_check" name="post_exclude_check" checked="checked" type="checkbox">
			<?php } else { ?>
				<input id="post_exclude_check" name="post_exclude_check" type="checkbox">
			<?php } ?>
			<label>Restrict Post count for this post ? By default, it will be included.</label>
		<?php
	}
	/**
	 * Post View Setting Sanitize.
	 *
	 * @param String $post_id string.
	 */
	public function post_views_save_post( $post_id ) {
		if ( isset( $_POST['message-send'] ) ) {
			$retrieved_nonce = sanitize_text_field( wp_unslash( $_POST['message-send'] ) );
		}
		if ( wp_verify_nonce( $retrieved_nonce, 'custom-action' ) ) {
			if ( isset( $_POST['post_exclude_check'] ) ) {
				update_option( $post_id . '-post_option', sanitize_text_field( wp_unslash( $_POST['post_exclude_check'] ) ) );
			} else {
				update_option( $post_id . '-post_option', '' );
			}
		}
	}
	/**
	 * Post View Setting Sanitize.
	 *
	 * @param String $post_id string.
	 * @param String $user_ip string.
	 */
	public function set_total_post_count( $post_id, $user_ip ) {
		global $wpdb;
		$post_status = 'publish';
		$cache_key   = 'prefix_post_count_' . $post_status;
		$count       = wp_cache_get( $cache_key );
		if ( false == $count ) {
			// @codingStandardsIgnoreLine
			$vist  = $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM {$wpdb->prefix}post_views_by_user WHERE (pst_id = %d AND user_ip = %s)", $post_id, $user_ip ) );
			if($count != 0 && 0 != $vist){ 
				$count = $this->get_total_post_count( $post_id ); 
			}else{
			 	$count = $this->post_new_update_entry_count( $post_id, $user_ip );
			}
			wp_cache_set( $cache_key, $count );
		}
		return $count;
	}
	/**
	 * Post View Setting Sanitize.
	 *
	 * @param String $post_id string.
	 * @param String $user_ip string.
	 */
	public function post_new_update_entry_count( $post_id, $user_ip ) {
		$postentry = $this->get_total_post_count( $post_id );
		if ( 0 == $postentry ) {
			// New Entry.
			$this->insert_data( $postentry, $post_id, $user_ip );
		} else {
			// Update Entry.
			$count = $postentry;
			$ci    = $count + 1;
			$this->insert_data( $ci, $post_id, $user_ip, 1 );
		}

		$countentry = $this->get_total_post_count( $post_id );
		return $countentry;
	}
	/**
	 * Post View Setting Sanitize.
	 *
	 * @param String $postentry string.
	 * @param String $post_id string.
	 * @param String $user_ip string.
	 * @param String $isupdate string.
	 */
	public function insert_data( $postentry, $post_id, $user_ip, $isupdate = 0 ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'post_views';
		if ( 0 == $isupdate ) {
			// @codingStandardsIgnoreLine
			$newid = $wpdb->insert(
				$table_name,
				array(
					'pst_id' => $post_id,
					'pcount' => 1,
				)
			);
		} else {
			$post_status = 'publish';
			$cache_key   = 'prefix_post_count_' . $post_status;
			//$newid       = wp_cache_get( $cache_key );
			//if ( false == $newid ) {
				// @codingStandardsIgnoreLine
				$newid = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}post_views SET pcount = %d WHERE pst_id= %d", $postentry, $post_id ) );
				//wp_cache_set( $cache_key, $newid );
			//}
		}

		$tb_name = $wpdb->prefix . 'post_views_by_user';
		$utime   = gmdate( 'd-m-y h:i:s' );

		if ( ! empty( $newid ) ) {
			// @codingStandardsIgnoreLine
			$wpdb->insert(
				$tb_name,
				array(
					'pst_id'    => $post_id,
					'user_ip'   => $user_ip,
					'user_time' => $utime,
				)
			);
		}
	}
	/**
	 * Post View Setting Sanitize.
	 *
	 * @param String $post_id string.
	 */
	public function get_total_post_count( $post_id ) {
		global $wpdb;
		$post_status = 'publish';
		$cache_key   = 'prefix_post_count_' . $post_status;
		$count       = wp_cache_get( $cache_key );
		if ( false == $count ) {
			// @codingStandardsIgnoreLine
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT pcount FROM {$wpdb->prefix}post_views WHERE pst_id = %d", $post_id ) );
			$count = '' !== $count ? $count : 0;
			wp_cache_set( $cache_key, $count );
		}
		return $count;
	}

	/**
	 * Add post views counter to content.
	 *
	 * @param string $content string.
	 * @return mixed
	 */
	public function add_post_views_count( $content = '' ) {
		$display = false;

		$post_view_setting_options = get_option( 'post_view_setting_option_name' );

		// get post types.
		$post_values = $post_view_setting_options['post_values'];
		$post_type   = get_post_type();
		if ( in_array( $post_type, $post_values, true ) ) {
			$display = true;
		}

		// we don't want to mess custom loops.
		if ( ! in_the_loop() && ! class_exists( 'bbPress' ) ) {
			$display = false;
		}

		if ( true == $display ) {
			$filter = $post_view_setting_options['positions'];

			switch ( $filter ) {
				case 'after':
					$content = $content . do_shortcode( '[post_vsk]' );
					break;

				case 'before':
					$content = do_shortcode( '[post_vsk]' ) . $content;
					break;

				case 'manual':
				default:
					break;
			}
		}

		return $content;
	}

	/**
	 * Add post views counter to forum/topic of bbPress.
	 */
	public function display_bbpress_post_views() {
		$post_id = get_the_ID();

		// check only for forums and topics.
		if ( bbp_is_forum( $post_id ) || bbp_is_topic( $post_id ) ) {
			echo esc_html( $this->add_post_views_count( '' ) );
		}
	}
}
/**
 * Class Init.
 */
function wvc_init_class() {
	new WvcPostViews();
}

add_action( 'init', 'wvc_init_class' );
