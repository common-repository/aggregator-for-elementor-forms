<?php
/**
 * Functions for form editor.
 * @version 1.0
 * @since 1.0
 */
/**
 * This form recursively plucks from elementor form adds elements to the forms array.
 * @param array $elements. The elements to iterate.
 * @param array $forms     The forms data. They are passed by reference.
 *
 * @return
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
function cfse_list_recursive_iterate_elements($elements, &$forms) {
    /**
     * Start our loop.
     */
    foreach ($elements as $element) {
        /**
         * Check if our form.
         */
        if (isset($element->widgetType) && $element->widgetType == 'form') {
            $forms[] = $element;
        }
        /**
         * Check if we have elements.
         */
        if (empty($element->elements) === false) {
            $recursive = cfse_list_recursive_iterate_elements($element->elements, $forms);
        }
    }
}
/**
 * Get all elementor forms.
 * This function retrieves data from wp_meta_table.
 * @param string $offset. The offset for pagination.
 *
 * @return array. Returns array of all form with relevant data or null if no forms.
 */
function cfse_list_get_forms($offset = 0) {
    global $wpdb;
    /**
     * Get the forms now.
     */
    $results = $wpdb->get_results("SELECT a.ID, b.meta_value FROM $wpdb->posts a, $wpdb->postmeta b WHERE a.post_type NOT IN ('draft', 'revision') AND a.post_status = 'publish' AND a.ID = b.post_id AND b.meta_key = '_elementor_data' LIMIT 100000");
    /**
     * Check if empty.
     */
    if (empty($results)) {
        return null;
    }
    /**
     * Set vars.
     */
    $all_forms = [];
    /**
     * Now loop over results, extract form data.
     */
    foreach ($results as $result) {
        /**
         * Decode the data first. They are stored in json format.
         */
        $data = json_decode($result->meta_value);
        /**
         * Only proceed if object.
         */

        if (is_array($data) === false || empty($data) === true) {
            continue;
        }
        /**
         * Set vars.
         */
        $forms = [];
        /**
         * Start recursive iteration.
         */
        $iteration = cfse_list_recursive_iterate_elements($data, $forms);
        /**
         * Set data to all forms.
         */
        $all_forms[$result->ID] = $forms;
    }
    /**
     * Now filter the form data.
     */
    return $all_forms;
}
/**
 * This function changes the value of email in recursive formation.
 * Since value is passed by reference, we don't need to return anything.
 * @param string $form_id.  The form id to update email of.
 * @param array  $elements. The elementor elements data.
 * @param string $new_email The new email id to update.
 *
 * @return void.
 */
function cfse_list_change_email_value($form_id, &$elements, $new_email) {
    /**
     * Start looping.
     */
    foreach ($elements as $element) {
        /**
         * Check if our form.
         */
        if (isset($element->widgetType) && ($element->widgetType == 'form' && $element->id == $form_id)) {
            /**
             * We got our match.
             */
            $element->settings->email_to = $new_email;
            return $elements;
        }
        /**
         * Check if we have elements.
         */
        if (empty($element->elements) === false) {
            $element->elements = cfse_list_change_email_value($form_id, $element->elements, $new_email);
        }
    }
    /**
     * Return the elements.
     */
    return $elements;
}
/**
 * Get entries count.
 * @param string $form_id. The ID of the form.
 *
 * @return int. Total entries of the form.
 */
function cfse_get_entries_count($form_id) {
    global $wpdb;
    /**
     * Set vars.
     */
    $table_name = $wpdb->prefix."e_submissions";
    /**
     * Get the count and
     */
    return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE type='submission' AND element_id=%s", $form_id));
}
/**
 * Generates tr-row content for form list.
 * @param object $form.     The form object from elementor_data.
 * @param int    $ele_id    The ID of the element where form is.It could be in post, page or any other custom post type.
 *
 * @return OUTPUTS. Directly outputs data in buffer.
 */
function cfse_list_get_form_list_content($form, $ele_id) {
    ?>
        <tr class='cfse-forms-table__tr cfse-forms-table__tr--tbody' data-id='<?php echo esc_attr($form->id); ?>' data-post_id='<?php echo esc_attr($ele_id); ?>' data-nonce='<?php echo esc_attr(wp_create_nonce("single-form-".$form->id."_".$ele_id)); ?>'>
            <td class='cfse-forms-table__td cfse-forms-table__hd'></td>
            <td class='cfse-forms-table__td cfse-forms-table__hd'><?php echo esc_html($form->settings->form_name); ?></td>
            <td class='cfse-forms-table__td cfse-forms-table__hd'><?php echo esc_html(get_the_title($ele_id)); ?></td>
            <td class='cfse-forms-table__td cfse-forms-table__hd'><?php echo esc_html(cfse_get_entries_count($form->id)); ?></td>
            <td class='cfse-forms-table__td cfse-forms-table__hd'><input type='email' value='<?php echo esc_attr($form->settings->email_to); ?>' data-old-value='<?php echo esc_attr($form->settings->email_to); ?>' class='cfse-forms-table__notf-email' placeholder="Enter notification Email"></td>
            <td class='cfse-forms-table__td cfse-forms-table__hd'>
                <div class='cfse-table-action-buttons'>
                    <div class='cfse-table-action-buttons__normal-mode  cfse-table-action-buttons__all-modes'>
                        <div class='cfse-table-action-button cfse-table-action-button__edit'></div>
                        <a href='<?php echo esc_url(admin_url("post.php?post=$ele_id&action=elementor")); ?>' target='_blank' class='cfse-table-action-button cfse-table-action-button__full-edit'></a>
                    </div>
                    <div class='cfse-table-action-buttons__edit-mode cfse-table-action-buttons__all-modes'>
                        <div class='cfse-table-action-button cfse-table-action-button__save'></div>
                        <div class='cfse-table-action-button cfse-table-action-button__cancel'></div>
                    </div>
                </div>
            </td>
        </tr>
    <?php
}
/**
 * Ajax handler to save changes.
 * @param object|string $_POST {
 *      @type string $form_id The ID of the form
 *      @type int    $post_id The ID of the post. Could be anything.
 *      @type string nonce    The nonce to verify request.
 * }
 *
 * @return object. Returns wp_json_object.
 */
add_action('wp_ajax_cfse_save_form_settings', 'cfse_save_form_settings', 10, 2);
function cfse_save_form_settings() {
    if (current_user_can('manage_options') == false) {
        wp_send_json_error();
    }
    /**
     * Decode and validate data.
     */
    $data = json_decode(stripslashes($_POST['data']));
    /**
     * Validate nonce.
     */
    if (wp_verify_nonce($data->nonce, 'single-form-'.$data->form_id."_".$data->post_id) == false) {
        wp_send_json_error();
    }

    /**
     * Sanitize data.
     */
    $data->form_id = sanitize_text_field($data->form_id);
    $data->post_id = filter_var($data->post_id, FILTER_VALIDATE_INT);

    if (empty($data->form_id) || empty($data->post_id)) {
        wp_send_json_error();
    }
    
    /**
     * Validate email.
     */
    $data->value = filter_var($data->value, FILTER_SANITIZE_STRING);
    if (empty($data->value)) {
        wp_send_json_error();
    }
    /**
     * Now since all the data is validated, let's update data.
     */
    $elementor_data = get_post_meta($data->post_id, '_elementor_data', true);
	$old_ele_data   = $elementor_data;
    $elementor_data = json_decode($elementor_data);
    /**
     * Change email value.
     */
    $new_elementor_data = cfse_list_change_email_value($data->form_id, $elementor_data, $data->value);
    /**
     * Decode data.
     */
    $new_elementor_data = json_encode($new_elementor_data);
	global $wpdb;
	$sd = $wpdb->update(
		$wpdb->postmeta,
		[
			'meta_value' => $new_elementor_data
		],
		[
			'post_id'  => $data->post_id,
			'meta_key' => '_elementor_data'
		]
	);
    /**
     * Send rep/
     */
    wp_send_json_success();
}
?>
