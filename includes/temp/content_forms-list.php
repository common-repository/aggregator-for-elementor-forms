<?php
/**
 * Content template for all forms list page.
 * @version 1.0
 * @since 1.0
 */
/**
 * Check if current user can access this page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if (current_user_can('manage_options') === false) {
    /**
     * This form page should only be restricted to admins.
     */
    echo 'Sorry, you are not allowed to access this page';
    return;
}
?>
<div id='cfse-all-forms'>
    <div id='cfse-all-forms__header'>All Elementor forms</div>
    <div id='cfse-all-forms__table-wrapper'>
        <table id='cfse-forms-table'>
            <thead id='cfse-forms-table__thead'>
                <tr class='cfse-forms-table__tr cfse-forms-table__tr--thead'>
                    <th class='cfse-forms-table__th cfse-forms-table__hd'>#</th>
                    <th class='cfse-forms-table__th cfse-forms-table__hd'>Form Name</th>
                    <th class='cfse-forms-table__th cfse-forms-table__hd'>Parent Page</th>
                    <th class='cfse-forms-table__th cfse-forms-table__hd'>Total Entries</th>
                    <th class='cfse-forms-table__th cfse-forms-table__hd'>Notification Email</th>
                    <th class='cfse-forms-table__th cfse-forms-table__hd'>Actions</th>
                </tr>
            </thead>
            <tbody id='cfse-forms-table__tbody'>
                <?php
                    global $wpdb;
                    /**
                     * Get all the forms.
                     */
                    $all_forms = cfse_list_get_forms();
                    /**
                     * Now check if we have content.
                     */
                    if (empty($all_forms) === false) {
                        /**
                         * Let's loop over all form.
                         */
                        foreach ($all_forms as $page_id => $forms) {
                            /**
                             * Now loop over sub-forms for page-id.
                             */
                            foreach ($forms as $form) {
                                /**
                                 * Directly outputs the data.
                                 */
                                cfse_list_get_form_list_content($form, $page_id);
                            }
                        }
                    } else {
                        ?>
                        <tr class='crfe-no-forms-row'>
                            <td>
                                <div class='crfe-no-forms-row'>
                                    <div class='crfe-no-forms-row__icon'></div>
                                    <div class='crfe-no-forms-row__texts'>
                                        <div class='crfe-no-forms-row__btext'>You have not yet created a form</div>
                                        <div class='crfe-no-forms-row__stext'>You can create a form by editing a page with Elementor. If you believe this is an error, please feel free to contact us for further assistance</div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                ?>
            </tbody>
        </table>
    </div>
</div>
