<?php
/**
 * Legacy Functions and Classes
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// phpcs:disable

/**
 * Return the instance of the function
 */
function WU_Signup() {

	return \WP_Ultimo\Checkout\Legacy_Checkout::get_instance();

} // end WU_Signup;

/**
 *
 * We need to load our functions in case people access this from wp-signup without the .php extension
 */

if (!function_exists('validate_blog_form')) {

	function validate_blog_form() {
		$user = '';
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
		} // end if;

		return wpmu_validate_blog_signup($_POST['blogname'], $_POST['blog_title'], $user);
	} // end validate_blog_form;


} // end if;

if (!function_exists('validate_user_form')) {

	function validate_user_form() {
		return wpmu_validate_user_signup($_POST['user_name'], $_POST['user_email']);
	} // end validate_user_form;


} // end if;

/**
 * Builds HTML attributes from a PHP array
 *
 * @param array $attributes
 * @return void
 */
function wu_create_html_attributes_from_array($attributes = array()) {

	$output = '';

	foreach ($attributes as $name => $value) {
		if (is_bool($value)) {
			if ($value) {
				$output .= $name . ' ';
			} // end if;
		} else {
			$output .= sprintf('%s="%s"', $name, $value);
		} // end if;
	} // end foreach;

	return $output;

} // end wu_create_html_attributes_from_array;

/**
 * Display one single option
 *
 * @since 1.7.3
 * @param string $option_value
 * @param string $option_label
 * @return void
 */
function wu_print_signup_field_option($option_value, $option_label, $field = array()) { ?>

  <option <?php selected(isset($field['default']) && $field['default'] == $option_value); ?> value="<?php echo $option_value; ?>"><?php echo $option_label; ?></option>

	<?php
} // end wu_print_signup_field_option;

/**
 * Displays the option tags of an select field
 *
 * @since 1.7.3
 * @param array $options
 * @return void
 */
function wu_print_signup_field_options($options, $field = array()) {

	foreach ($options as $option_value => $option_label) {

		if (is_array($option_label)) {

			echo sprintf('<optgroup label="%s">', $option_value);

			foreach ($option_label as $option_value => $option_label) {

				wu_print_signup_field_option($option_value, $option_label, $field);

			} // end foreach;

			echo '</optgroup>';

		} else {

			wu_print_signup_field_option($option_value, $option_label, $field);

		} // end if;

	} // end foreach;

} // end wu_print_signup_field_options;

/**
 * Print sing-up fields
 *
 * @param string $field_slug
 * @param array  $field
 * @param array  $results
 * @return void
 */
function wu_print_signup_field($field_slug, $field, $results) {

	$display = true;

	// Requires Logic
	if (isset($field['requires']) && is_array($field['requires'])) {

		$display = false;

		/**
		 * Builds required elements list
		 */

		$elements = array_keys($field['requires']);
		array_walk($elements, function(&$value, $key) {
			$value = '#' . $value;
		});
		$elements = implode(', ', $elements);

		wp_enqueue_script('jquery');
		?>

    <script type="text/javascript">
    (function($) {
      $(document).ready(function() {

        var requires = <?php echo json_encode($field['requires']); ?>,
            target_field = $('#<?php echo $field_slug; ?>-field');

        var display_field = function(target_field, requires, velocity) {

          var conditions_count = Object.keys(requires).length,
              conditions_met   = 0;

          $.each(requires, function(element, value) {

            var element = $("#" + element),
                element_value = element.val();

            if (element.is(":checkbox")) {

              var is_checked = !!element.is(':checked');

              if (is_checked === value) {
                conditions_met++;
              }

              return true;

            } // end if;

            value = Array.isArray(value) ? value : [value];

            if (value.indexOf(element_value) > -1) {

              conditions_met++;

            } // end if;

          });

          if (conditions_met == conditions_count) {

            target_field.slideDown(velocity);

          } else {

            target_field.slideUp(velocity);

          } // end

        } // end display_field;

        display_field(target_field, requires, 0);

        $('<?php echo $elements; ?>').on('change', function() {
          display_field(target_field, requires, 300);
        });

      });
    })(jQuery);

    </script>

		<?php

	} // end if;

	$wrapper_attributes = '';
	$attributes         = '';

	/**
	 * Builds Attributes display
	 */
	if (isset($field['wrapper_attributes']) && $field['wrapper_attributes']) {

		$wrapper_attributes = wu_create_html_attributes_from_array($field['wrapper_attributes']);

	} // end if;

	if (isset($field['attributes']) && $field['attributes']) {

		$attributes = wu_create_html_attributes_from_array($field['attributes']);

	} // end if;

	/**
	 * Switch type for display
	 */
	switch ($field['type']) {

		/**
		 * Normal Text Inputs
		 */
		case 'text':
		case 'number':
		case 'email':
		case 'url':
			?>

    <p <?php echo $wrapper_attributes; ?> id="<?php echo $field_slug; ?>-field" <?php echo $wrapper_attributes; ?> style="<?php echo $display ? '' : 'display: none'; ?>" >

      <label for="<?php echo $field_slug; ?>"><?php echo $field['name']; ?> <?php echo wu_tooltip($field['tooltip']); ?><br>
      <input <?php echo $attributes; ?> <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?> type="<?php echo $field['type']; ?>" name="<?php echo $field_slug; ?>" id="<?php echo $field_slug; ?>" class="input" value="<?php echo isset($results[$field_slug]) ? $results[$field_slug] : ''; ?>" size="20"></label>


			<?php
			if ($error_message = $results['errors']->get_error_message($field_slug)) {
				echo '<p class="error">' . $error_message . '</p>';
			} // end if;

			?>

    </p>

			<?php
	        break;

		case 'password':
			wp_enqueue_script('utils');
			wp_enqueue_script('user-profile');
			?>

    <p <?php echo $wrapper_attributes; ?> id="<?php echo $field_slug; ?>-field" <?php echo $wrapper_attributes; ?> style="<?php echo $display ? '' : 'display: none'; ?>" >

			<?php
			if (isset($field['display_force']) && $field['display_force']) :

				$suffix = WP_Ultimo()->min;

				wp_enqueue_script('wu-password-verify', WP_Ultimo()->get_asset("wu-password-verify$suffix.js", 'js'), array('jquery'), true);

				?>

      <span class="password-input-wrapper" style="display: block;">
        <label for="<?php echo $field_slug; ?>"><?php echo $field['name']; ?> <?php echo wu_tooltip($field['tooltip']); ?><br>
        <input <?php echo $attributes; ?> <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?> type="<?php echo $field['type']; ?>" name="<?php echo $field_slug; ?>" id="<?php echo $field_slug; ?>" class="input" value="<?php echo isset($results[$field_slug]) ? $results[$field_slug] : ''; ?>"  data-reveal="1" data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" class="input" size="20" autocomplete="off" aria-describedby="pass-strength-result" />
      </span>

      <span style="display: block; margin-top: -16px; opacity: 1; height: 36px;" id="pass-strength-result" class="hide-if-no-js" aria-live="polite"><?php _e( 'Strength indicator' ); ?></span>

      <script>
        (function($) {
          $(function() {
            //wu_check_pass_strength('#<?php echo $field_slug; ?>', '#<?php echo $field_slug; ?>');
            $('#<?php echo $field_slug; ?>').keyup(function() {
              wu_check_pass_strength('#<?php echo $field_slug; ?>', '#<?php echo $field_slug; ?>');
            });
          });
        })(jQuery);
      </script>

			<?php else : ?>

      <label for="<?php echo $field_slug; ?>"><?php echo $field['name']; ?> <?php echo wu_tooltip($field['tooltip']); ?><br>
      <input <?php echo $attributes; ?> <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?> type="<?php echo $field['type']; ?>" name="<?php echo $field_slug; ?>" id="<?php echo $field_slug; ?>" class="input" value="<?php echo isset($results[$field_slug]) ? $results[$field_slug] : ''; ?>" size="20"></label>

    <?php endif; ?>

			<?php
			if ($error_message = $results['errors']->get_error_message($field_slug)) {
				echo '<p class="error">' . $error_message . '</p>';
			} // end if;

			?>

    </p>

			<?php
	        break;

		/**
		 * Case HTML
		 */
		case 'html':
			?>

      <div <?php echo $wrapper_attributes; ?> id="<?php echo $field_slug; ?>-field">
			<?php echo $field['content']; ?>
      </div>

			<?php
		    break;

		/**
		 * Case Submit Button
		 */
		case 'submit':
			?>

    <p class="submit">

      <input name="signup_form_id" type="hidden" value="1">

      <button id="wp-submit" <?php echo $attributes; ?> type="submit" class="button button-primary button-large button-next" value="1" name="save_step">
			<?php esc_attr_e($field['name'], 'wp-ultimo'); ?>
      </button>

			<?php wp_nonce_field('signup_form_1', '_signup_form'); ?>

    </p>

			<?php
	        break;

		/**
		 * Case Select
		 */
		case 'select':
			?>

    <p <?php echo $wrapper_attributes; ?> id="<?php echo $field_slug; ?>-field" style="<?php echo $display ? '' : 'display: none'; ?>">

      <label for="<?php echo $field_slug; ?>"><?php echo $field['name']; ?> <?php echo wu_tooltip($field['tooltip']); ?><br>

      <select <?php echo $attributes; ?> <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?> name="<?php echo $field_slug; ?>" id="<?php echo $field_slug; ?>" class="input" value="<?php echo isset($results[$field_slug]) ? $results[$field_slug] : ''; ?>">

			<?php wu_print_signup_field_options($field['options'], $field); ?>

      </select>

      </label>

			<?php
			if ($error_message = $results['errors']->get_error_message($field_slug)) {
				echo '<p class="error">' . $error_message . '</p>';
			} // end if;

			?>

    </p>

			<?php
	        break;

		/**
		 * Case Checkbox
		 */
		case 'checkbox':
			$checked = isset($field['check_if']) && isset($result[$field['check_if']])
                  || (isset($field['check_if']) && isset($_POST[$field['check_if']]) && $_POST[$field['check_if']])
                  || (isset($field['checked']) && $field['checked'])
                  ? true : false;
			?>

    <p>

      <label for="<?php echo $field_slug; ?>">
        <input type="checkbox" name="<?php echo $field_slug; ?>" value="1" id="<?php echo $field_slug; ?>" <?php echo checked($checked, true); ?>>
			<?php echo $field['name']; ?>
      </label>

      <br>

			<?php
			if ($error_message = $results['errors']->get_error_message($field_slug)) {
				echo '<p class="error">' . $error_message . '</p>';
			} // end if;

			?>

      <br>

    </p>

			<?php
	        break;

	} // end switch;

}  // end wu_print_signup_field;

/**
 * Alias function to allow creation of users for WP Ultimo.
 *
 * User Data should contain: user_login, user_email, user_pass;
 * Plan Data should contain: plan_id, plan_freq;
 * User Meta is an associative array containing key => value pairs to be saved as meta fields on that user.
 *
 * @param array $user_data
 * @param array $plan_data
 * @param array $user_meta
 * @return integer|boolean
 */
function wu_create_user(array $user_data, array $plan_data, array $user_meta = array()) {

	return WU_Signup()->create_user($user_data, $plan_data, $user_meta);

} // end wu_create_user;

/**
 * Alias function to allow creation of sites for WP Ultimo.
 *
 * Site Data should contain: blog_title, blogname, and role;
 * Site Meta is an associative array containing key => value pairs to be saved as meta fields on that site.
 *
 * @param integer $user_id
 * @param array   $site_data
 * @param boolean $template_id
 * @param array   $site_meta
 * @return void
 */
function wu_create_site_legacy($user_id, array $site_data, $template_id = false, $site_meta = array()) {

	return WU_Signup()->create_site($user_id, $site_data, $template_id, $site_meta);

} // end wu_create_site_legacy;

/**
 * Alias function that adds a new Step to the sign-up flow
 *
 * @since 1.4.0
 * @param string  $id
 * @param integer $order
 * @param array   $step
 * @return void
 */
function wu_add_signup_step($id, $order, array $step) {

	return WU_Signup()->add_signup_step($id, $order, $step);

} // end wu_add_signup_step;

/**
 * Alias function that adds a new field to a step the sign-up flow
 *
 * @since 1.4.0
 * @param string  $step
 * @param string  $id
 * @param integer $order
 * @param array   $step
 * @return void
 */
function wu_add_signup_field($step, $id, $order, $field) {

	return WU_Signup()->add_signup_field($step, $id, $order, $field);

} // end wu_add_signup_field;
