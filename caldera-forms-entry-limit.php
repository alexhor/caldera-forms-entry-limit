<?php defined( 'ABSPATH' ) or die( 'Please return to the main page!' );
/**
 * Plugin Name: Caldera Forms Entry Limit
 * Description: Limit the number of entries for the caldera forms plugin
 * Version: 1.0
 * Author: Alexander Hornig
 * Author URI: https://h-software.de
 * License: APGL-3.0
 */

class CalderaFormsEntryLimit {
	function __construct() {
		add_action( 'caldera_forms_general_settings_panel', [ $this, 'settings' ] );
		add_action( 'caldera_forms_submit_start', [ $this, 'submit_check' ], 10, 2 );
		add_filter( 'caldera_forms_render_get_form', [ $this, 'render_check' ], 10, 2 );
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
	}
	
	public function settings( array $element ) {
		?>
		<div class="caldera-config-group">
			<label for="entry_limit"><?php _e( 'Entry Limit', 'caldera_forms_entry_limit' ); ?></label>

			<div class="caldera-config-field">
				<input type="number" name="config[entry_limit]" id="entry_limit" value="<?php echo esc_attr( @$element['entry_limit'] ); ?>">
			</div>
		</div>
		
		<div class="caldera-config-group" style="width:500px;">
			<label for="entry_limit"l><?php _e( 'Entry Limit Reached Message', 'caldera_forms_entry_limit' ); ?></label>

			<div class="caldera-config-field">
				<span style="position:relative;display:inline-block; width:100%;">
					<textarea class="field-config block-input" name="config[entry_limit_message]" id="entry_limit_message"><?php echo esc_attr( @$element['entry_limit_message'] ); ?></textarea>
				</span>
			</div>
		</div>
		<?php
	}
	
	public function submit_check( array $form, $process_id ) {
		if( ( $message = $this->check_limit( $form ) ) === false ) return;
		
		echo $message;
		// stop further output
		wp_die();
	}
	
	public function render_check( array $form ) {
		if( ( $message = $this->check_limit( $form ) ) === false ) return $form;
		
		echo '<div class="caldera-grid"><div class="alert alert-error">' . $message . '</div></div>';
		return [];
	}
	
	protected function check_limit( array $form ) {
		global $referrer;
		
		// this only makes sense, if an entry limit is given
		if( !isset( $form['entry_limit'] ) || ( empty( $form['entry_limit'] ) && $form['entry_limit'] != 0 ) || !is_numeric( $form['entry_limit'] ) ) return false;
		
		// count the entries
		$entries_count = Caldera_Forms_Admin::get_entries( $form['ID'] )['active'];
		
		// check if the entry limit has been reached
		if( $form['entry_limit'] <= $entries_count ) {
			// if the message is empty, show the default one
			if( !isset( $form['entry_limit_message'] ) || empty( $form['entry_limit_message'] ) ) {
				return __( 'The maximum number of submission was reached. Submitting the form is not possible anymore.', 'caldera_forms_entry_limit' );
			}
			else {
				return esc_html( $form['entry_limit_message'] );
			}
		}
		
		return false;
	}
	
	public function load_textdomain() {
		load_plugin_textdomain( 'caldera_forms_entry_limit', false, basename( dirname( __FILE__ ) ) . '/lang' );
	}
}

new CalderaFormsEntryLimit();
