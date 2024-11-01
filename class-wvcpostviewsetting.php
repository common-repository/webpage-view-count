<?php
/**
 * PostViewSetting File
 *
 * @file
 * @package Webpage
 * PostViewSetting File
 */

/**
 * PostViewSetting Class
 *
 * @category PostViewSetting
 * @package   Webpage
 * @author   Techforce
 * @link     https://techforceglobal.com/
 */
class WvcPostViewSetting {
	/**
	 * The deposit owner.
	 *
	 * @var string
	 */
	private $post_view_setting_options;
	/**
	 * PostViewSetting constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'post_view_setting_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'post_view_setting_page_init' ) );
	}
	/**
	 * Post View Setting Add Plugin page.
	 */
	public function post_view_setting_add_plugin_page() {
		add_options_page(
			'Post View Setting', // page_title.
			'Post View Setting', // menu_title.
			'manage_options', // capability.
			'post-view-setting', // menu_slug.
			array( $this, 'post_view_setting_create_admin_page' ) // function.
		);
	}
	/**
	 * Post View Setting Admin Page.
	 */
	public function post_view_setting_create_admin_page() {
		wp_enqueue_style( 'post.views.css' );
		wp_enqueue_style( 'boot.css' );
		wp_enqueue_style( 'summernote.css' );
		wp_enqueue_script( 'boot.js' );
		wp_enqueue_script( 'summernote.js' );
		wp_enqueue_script( 'custom.js' );

		$this->post_view_setting_options = get_option( 'post_view_setting_option_name' ); ?>

		<div class="wrap">
			<form method="post" action="options.php">
				<?php
					settings_fields( 'post_view_setting_option_group' );
					do_settings_sections( 'post-view-setting-admin' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Post View Setting Page Init.
	 */
	public function post_view_setting_page_init() {
		register_setting(
			'post_view_setting_option_group', // option_group.
			'post_view_setting_option_name', // option_name.
			array( $this, 'post_view_setting_sanitize' ) // sanitize_callback.
		);

		add_settings_section(
			'post_view_setting_setting_section', // id.
			'Post View Settings', // title.
			array( $this, 'post_view_setting_section_info' ), // callback.
			'post-view-setting-admin' // page.
		);

		add_settings_field(
			'post_values', // id.
			'Post Values', // title.
			array( $this, 'post_values_callback' ), // callback.
			'post-view-setting-admin', // page.
			'post_view_setting_setting_section' // section.
		);

		add_settings_field(
			'positions', // id.
			'Positions', // title.
			array( $this, 'positions_callback' ), // callback.
			'post-view-setting-admin', // page.
			'post_view_setting_setting_section' // section.
		);

		add_settings_field(
			'use_exclude', // id.
			'Use Exclude?', // title.
			array( $this, 'use_exclude_callback' ), // callback.
			'post-view-setting-admin', // page.
			'post_view_setting_setting_section' // section.
		);

		add_settings_field(
			'html_template', // id.
			'HTML Template', // title.
			array( $this, 'html_template_callback' ), // callback.
			'post-view-setting-admin', // page.
			'post_view_setting_setting_section' // section.
		);

		add_settings_field(
			'custom_css', // id.
			'Custom CSS', // title.
			array( $this, 'custom_css_callback' ), // callback.
			'post-view-setting-admin', // page.
			'post_view_setting_setting_section' // section.
		);

		add_settings_field(
			'custom_js', // id.
			'Custom JS', // title.
			array( $this, 'custom_js_callback' ), // callback.
			'post-view-setting-admin', // page.
			'post_view_setting_setting_section' // section.
		);
	}
	/**
	 * Post View Setting Sanitize.
	 *
	 * @param String $input string.
	 */
	public function post_view_setting_sanitize( $input ) {
		$sanitary_values = array();
		if ( isset( $input['post_values'] ) ) {
			$sanitary_values['post_values'] = $input['post_values'];
		}

		if ( isset( $input['positions'] ) ) {
			$sanitary_values['positions'] = $input['positions'];
		}

		if ( isset( $input['use_exclude'] ) ) {
			$sanitary_values['use_exclude'] = $input['use_exclude'];
		}

		if ( isset( $input['html_template'] ) ) {
			$sanitary_values['html_template'] = $input['html_template'];
		}

		if ( isset( $input['custom_css'] ) ) {
			$sanitary_values['custom_css'] = $input['custom_css'];
		}

		if ( isset( $input['custom_js'] ) ) {
			$sanitary_values['custom_js'] = $input['custom_js'];
		}

		return $sanitary_values;
	}
	/**
	 * Post View Setting Section Info.
	 */
	public function post_view_setting_section_info() {
	}
	/**
	 * Post Values call back.
	 */
	public function post_values_callback() {
		?>
		<select name="post_view_setting_option_name[post_values][]" id="post_values" multiple="multiple">
			<?php
			$types = get_post_types( array(), 'objects' );
			$html  = '';
			foreach ( $types as $type ) {
				if ( isset( $type->public ) && 1 == intval( $type->public ) ) {
					$selected = ( isset( $this->post_view_setting_options['post_values'] ) && in_array( $type->name, $this->post_view_setting_options['post_values'], true ) ) ? 'selected' : '';
					?>
				<option value='<?php echo esc_html( $type->name ); ?>'<?php echo esc_html( $selected ); ?>><?php echo esc_html( $type->label ); ?></option>
					<?php
				}
			}

			?>
		</select> 
		<?php
	}
	/**
	 * Position callback.
	 */
	public function positions_callback() {
		?>
		<select name="post_view_setting_option_name[positions]" id="positions">
			<?php
			$positions = array(
				'before' => __( 'before the content', 'post-views-counter' ),
				'after'  => __( 'after the content', 'post-views-counter' ),
				'manual' => __( 'manual', 'post-views-counter' ),
			);
			$html      = '';
			foreach ( $positions as $position => $position_name ) {
				$selected = ( isset( $this->post_view_setting_options['positions'] ) && $this->post_view_setting_options['positions'] == $position ) ? 'selected' : '';
				?>
			<option value='<?php echo esc_html( $position ); ?>'<?php echo esc_html( $selected ); ?>><?php echo esc_html( $position_name ); ?></option>
				<?php
			}

			?>
		</select> 
		<?php
	}
	/**
	 * Exclue call back.
	 */
	public function use_exclude_callback() {
		printf(
			'<input type="checkbox" name="post_view_setting_option_name[use_exclude]" id="use_exclude" value="use_exclude" %s> <label for="use_exclude">Check Exclude in Post </label>',
			( isset( $this->post_view_setting_options['use_exclude'] ) && 'use_exclude' == $this->post_view_setting_options['use_exclude'] ) ? 'checked' : ''
		);
	}
	/**
	 * HTMl call back.
	 */
	public function html_template_callback() {
		printf(
			'<textarea class="large-text" rows="5" name="post_view_setting_option_name[html_template]" id="html_template">%s</textarea>',
			! empty( wp_strip_all_tags( html_entity_decode( $this->post_view_setting_options['html_template'] ) ) ) ? esc_attr( $this->post_view_setting_options['html_template'] ) : '<div class="post-views entry-meta"><span class="post-views-label"> Post View:</span><span class="post-views-count">{{counter}}</span></div>'
		);
	}
	/**
	 * Custom Css call back.
	 */
	public function custom_css_callback() {
		printf(
			'<textarea class="large-text" rows="5" name="post_view_setting_option_name[custom_css]" id="custom_css">%s</textarea>',
			isset( $this->post_view_setting_options['custom_css'] ) ? esc_attr( $this->post_view_setting_options['custom_css'] ) : ''
		);
	}
	/**
	 * Custom Js call back.
	 */
	public function custom_js_callback() {
		printf(
			'<textarea class="large-text" rows="5" name="post_view_setting_option_name[custom_js]" id="custom_js">%s</textarea>',
			isset( $this->post_view_setting_options['custom_js'] ) ? esc_attr( $this->post_view_setting_options['custom_js'] ) : ''
		);
	}
}
if ( is_admin() ) {
	$post_view_setting = new WvcPostViewSetting();
}
