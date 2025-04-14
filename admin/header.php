<?php
/**
 * Admin Header Page
 *
 * @package WPSite_Post_Status_Notifications
 */

?>

<!-- Header -->
<div class="nnr-header">

	<div class="nnr-logo"></div>

	<div class="nnr-product-details">
		<span class="nnr-product-name"><?php esc_html_e( 'Post Status Notifications', 'wpsite-post-status-notification' ); ?></span>
		<span class="nnr-product-version"><?php echo esc_html( WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM ); ?></span>
	</div>

	<a href="http://draftpress.com/products" target="_blank">
		<button class="nnr-header-button pull-right"><?php esc_html_e( 'More Products', 'wpsite-post-status-notification' ); ?></button>
	</a>

</div>
