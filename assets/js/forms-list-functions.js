/**
 * Functions for Forms list page. 
 * @version 1.0 
 * @since 1.0
 */
jQuery(document).ready(function($){
    /**
     * Our module based var. 
     * We will have module based var.
     */
    var cfseList = {
        /**
         * Set vars. 
         */
        notfID: 1,
        /**
         * This function validates email address. 
         * @param string email. The email to validate.
         * 
         * @return bool. Returns true on success, false on failure
         */
        validateEmail: function (email) {
            return Boolean(email.match(
                /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            ));
        },
        /**
         * This function runs a notifcation for success, error ,warning
         * @param {string} text The message to show in notification
         * @param {string} id   Unique notification id
         * @param {string} type Whether a success|error|warning notification
         * @param {string} posn Position of the notification box on screen
         *
         * @return void.
         */
        sendNotf: function (text, options) {
            options = $.extend({}, {
                id: 'yamc-notification',
                type: 'error',
                posn: 'bottomRight'
            }, options);
            if (!text) {
                text = 'We had an error processing your request. Please try again';
            }
            /**
             * Set vars.
             */
            var notfID = 'cfse-toast-' + this.notfID;
            this.notfID++;
            /**
             * Send a custom notifcaiton. 
             */
            $('body').append('<div id="' + notfID+'" class="cfse-toast toast-type--'+options.type+'">'+text+'</div>');
            /**
             * Append data. 
             */
            $('body').find('#' + notfID).fadeIn();
            /**
             * Remove the toast after 5 seconds. 
             */
            setTimeout(function(){
                $('body').find('#'+notfID).fadeOut();
            }, 5000);
        },
        /**
         * Opens edit more of the following form. 
         * @param ojbect $ele.      The button which was clicked or parent element.
         * @param bool   editMode   True if need to switch to edit mode. False otherwise.
         * 
         * @return void.
         */
        switchMode: function ($ele, editMode) {
            var $parent = $ele.is('tr') ? $ele : $ele.closest('tr');
            /**
             * Check what we want to do. 
             */
            if (editMode === true) {
                /**
                 * This turns on edit mode.
                 */
                $parent.addClass('cfse-editing')
                    .find('.cfse-forms-table__notf-email').focus();
            } else {
                /**
                 * This removes edit mode.
                 */
                $parent.removeClass('cfse-editing');
            }
        },
        /**
         * Sends request to process it on server. 
         * @param object $btn. The button to play with.
         * 
         * @return void.
         */
        saveSettings: function ($btn) {
            var _this   = this,
                $parent = $btn.closest('tr'),
                $email  = $parent.find('.cfse-forms-table__notf-email'),
                data    = {
                    value   : $email.val(),
                    form_id : $parent.attr('data-id'),
                    post_id : $parent.attr('data-post_id'),
                    nonce   : $parent.attr('data-nonce')
                };
            /**
             * Check if email is valid.
             */
            if (data.value.length < 5) {
                /**
                 * Throw an error.
                 */
                $email.addClass('cfse-error');
                this.sendNotf('Please enter a valid email address to continue');
                return;
            }
            /**
             * Now start aesthetics and ux.
             */
            $btn.addClass('loading');
            /**
             * Sends ajax request to the server.
             */
            this.sendAjaxRequest('cfse_save_form_settings', data).always(function(resp){
                if (resp.success === true) {
                    /**
                     * Change the value.
                     */
                    $email.attr('data-old-value', data.value);
                    /**
                     * Close the box.
                     */
                    _this.switchMode($parent, false);
                    /**
                     * Send notf.
                     */
                    _this.sendNotf('Your changes were successfully updated', { type: 'success' });
                } else {
                    alert('We had an error while processing your request. Please try again in some time');
                }
                $btn.removeClass('loading');
            })
        },
        /**
         * Cancel changes and appends old value back to email. 
         * @param object $btn. The button to play with. 
         * 
         * @return void.
         */
        cancelChanges: function ($btn) {
            var $parent     = $btn.closest('tr'),
                $email      = $parent.find('.cfse-forms-table__notf-email'),
                oldValue    = $email.attr('data-old-value');
            /**
             * Append old value.
             */
            $email.val(oldValue);
            /**
             * Switch back. 
             */
            this.switchMode($parent, false);
        },
        /**
         * Send ajax request over server
         * @param string action_name The name of ajax action
         * @param object data.       The data to send
         *
         * @return void
         */
        sendAjaxRequest: function (action_name, data) {
            return $.ajax({
                type: 'POST',
                dataType: 'json',
                url: cfseVars.ajaxURL,
                data: {
                    'action': action_name,
                    'data': JSON.stringify(data)
                }
            });
        },
    };
    /*************
     * ***
     *  jQuery Event Listeners.
     * ***
     */
    /**
     * Even listener to start edit mode.
     */
    $('body').on('click', '.cfse-table-action-button__edit', function() {
        cfseList.switchMode($(this), true);
    });
    /**
     * Event listener to save changes after edit. 
     */
    $('body').on('click', '.cfse-table-action-button__save', function(){
        cfseList.saveSettings($(this));
    });
    /**
     * Event listener to cancel changes and exit edit mode.
     */
    $('body').on('click', '.cfse-table-action-button__cancel', function () {
        cfseList.cancelChanges($(this));
    });
});