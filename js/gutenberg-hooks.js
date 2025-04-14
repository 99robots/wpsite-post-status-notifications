jQuery(document).ready(function($) {

    let previousPostStatus = null;  // Variable to hold the previous post status

    wp.data.subscribe(() => {
        const isSavingPost = wp.data.select('core/editor').isSavingPost();
        const isAutosavingPost = wp.data.select('core/editor').isAutosavingPost();
        const currentPostStatus = wp.data.select('core/editor').getCurrentPost().status;
        //const previousPostStatus = wp.data.select('core/editor').getEditedPostAttribute('status');

        // On initial load, set the previous status
        if (previousPostStatus === null) {
            previousPostStatus = currentPostStatus;
        }

        // Only proceed if the post is being saved and it's not an autosave
        if (isSavingPost && !isAutosavingPost) {
    
            // Send AJAX request
            wpsiteSendAjaxRequest(currentPostStatus, previousPostStatus);

            // Update the previous status after saving
            previousPostStatus = currentPostStatus;

        }

    });

    function wpsiteSendAjaxRequest(currentPostStatus, previousStatus) {
        $.ajax({
            url: wpsite_gutenberg_hooks.ajaxurl, // This is how you can pass the AJAX URL from PHP to JavaScript.
            type: 'POST',
            data: {
                action: 'wpsite_handle_post_save',
                post_status: currentPostStatus,
                previous_status: previousStatus,
                post_id: wp.data.select('core/editor').getCurrentPostId(),
                wp_nonce: wpsite_gutenberg_hooks.wp_nonce
            },
            success: function(response) {
                console.log('AJAX response:', response);
            },
            error: function(error) {
                console.error('AJAX error:', error);
            }
        });
    }
});