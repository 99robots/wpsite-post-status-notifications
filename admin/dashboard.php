<div class="nnr-wrap">
	<?php require_once( 'header.php' ) ?>

	<div class="nnr-container">

		<div class="nnr-content">

			<form method="post" class="form-horizontal">

				<div class="page-header" style="margin-top: 0px;">
					<h1 style="margin-top: 0px;"><?php esc_html_e( 'General Settings', 'wpsite-post-status-notification' ) ?></h1>
				</div>

				<!-- Include these post types -->

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'Post Types', 'wpsite-post-status-notification' ) ?></label>
					<div class="col-sm-9">
						<em class="help-block"><?php esc_html_e( 'Post status notification emails will only be sent for activity occurring within the following post types.', 'wpsite-post-status-notification' ) ?></em>
						<?php foreach ( $post_types as $post_type ) { ?>
							<input type="checkbox" id="wpsite_post_status_notifications_settings_post_types_<?php echo $post_type ?>" name="wpsite_post_status_notifications_settings_post_types_<?php echo $post_type ?>" <?php echo ( isset( $settings['post_types'] ) && in_array( $post_type, $settings['post_types'] ) ? 'checked="checked"' : '' ) ?>/><span><?php echo $post_type ?></span><br />
						<?php } ?>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'Share Links', 'wpsite-post-status-notification' ) ?></label>
					<div class="col-sm-9">
						<em class="help-block"><?php esc_html_e( 'Encourage sharing of published posts by inserting automatically generated share links to the bottom of the email notifications that are sent.', 'wpsite-post-status-notification' ) ?></em>

						<input id="wpsite_post_status_notifications_settings_message_share_links_twitter" name="wpsite_post_status_notifications_settings_message_share_links_twitter" type="checkbox" value="users" <?php echo isset( $settings['message']['share_links']['twitter'] ) && $settings['message']['share_links']['twitter'] ? 'checked="checked"' : ''; ?>><span><?php esc_html_e( 'Twitter', 'wpsite-post-status-notification' ) ?></span><br />

						<input id="wpsite_post_status_notifications_settings_message_share_links_facebook" name="wpsite_post_status_notifications_settings_message_share_links_facebook" type="checkbox" value="users" <?php echo isset( $settings['message']['share_links']['facebook'] ) && $settings['message']['share_links']['facebook'] ? 'checked="checked"' : ''; ?>><span><?php esc_html_e( 'Facebook', 'wpsite-post-status-notification' ) ?></span><br />

						<input id="wpsite_post_status_notifications_settings_message_share_links_google" name="wpsite_post_status_notifications_settings_message_share_links_google" type="checkbox" value="users" <?php echo isset( $settings['message']['share_links']['google'] ) && $settings['message']['share_links']['google'] ? 'checked="checked"' : ''; ?>><span><?php esc_html_e( 'Google+', 'wpsite-post-status-notification' ) ?></span><br />

						<input id="wpsite_post_status_notifications_settings_message_share_links_linkedin" name="wpsite_post_status_notifications_settings_message_share_links_linkedin" type="checkbox" value="users" <?php echo isset( $settings['message']['share_links']['linkedin'] ) && $settings['message']['share_links']['linkedin'] ? 'checked="checked"' : ''; ?>><span><?php esc_html_e( 'LinkedIn', 'wpsite-post-status-notification' ) ?></span><br />
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'Post Submitted for Review', 'wpsite-post-status-notification' ) ?></label>
					<div class="col-sm-9">
						<em class="help-block"><?php esc_html_e( 'Notify these users when a contributor submits a post for review.', 'wpsite-post-status-notification' ) ?></em>

						<input name="wpsite_post_status_notifications_settings_pending_notify" type="radio" value="administrator" <?php echo isset( $settings['pending_notify'] ) && 'administrator' === $settings['pending_notify'] ? 'checked' : ''; ?>><span><?php esc_html_e( 'Admins', 'wpsite-post-status-notification' ) ?></span><br />

						<input name="wpsite_post_status_notifications_settings_pending_notify" type="radio" value="editor" <?php echo isset( $settings['pending_notify'] ) && 'editor' === $settings['pending_notify'] ? 'checked' : ''; ?>><span><?php esc_html_e( 'Editors', 'wpsite-post-status-notification' ) ?></span><br />

					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'Post Was Published', 'wpsite-post-status-notification' ) ?></label>
					<div class="col-sm-9">
						<em class="help-block"><?php esc_html_e( 'Notify these users when a contributor\'s post is published or any other post is published.', 'wpsite-post-status-notification' ) ?></em>

						<input id="wpsite_post_status_notifications_settings_publish_notify_author" name="wpsite_post_status_notifications_settings_publish_notify" type="radio" value="author" <?php echo isset( $settings['publish_notify'] ) && 'author' === $settings['publish_notify'] ? 'checked="checked"' : ''; ?>><span><?php esc_html_e( 'Contributors', 'wpsite-post-status-notification' ) ?></span><br />

						<input id="wpsite_post_status_notifications_settings_publish_notify_users" name="wpsite_post_status_notifications_settings_publish_notify" type="radio" value="users" <?php echo isset( $settings['publish_notify'] ) && 'users' === $settings['publish_notify'] ? 'checked="checked"' : ''; ?>><span><?php esc_html_e( 'All Users', 'wpsite-post-status-notification' ) ?></span><br />

						<input id="wpsite_post_status_notifications_settings_publish_notify_admins" name="wpsite_post_status_notifications_settings_publish_notify" type="radio" value="admins" <?php echo isset( $settings['publish_notify'] ) && 'admins' === $settings['publish_notify'] ? 'checked="checked"' : ''; ?>><span><?php esc_html_e( 'Admins', 'wpsite-post-status-notification' ) ?></span><br />

						<input id="wpsite_post_status_notifications_settings_publish_notify_editors" name="wpsite_post_status_notifications_settings_publish_notify" type="radio" value="editors" <?php echo isset( $settings['publish_notify'] ) && 'editors' === $settings['publish_notify'] ? 'checked="checked"' : ''; ?>><span><?php esc_html_e( 'Editors', 'wpsite-post-status-notification' ) ?></span><br />

					</div>
				</div>

				<div class="page-header">
					<h1><?php esc_html_e( 'Email Headers', 'wpsite-post-status-notification' ) ?></h1>
				</div>

				<!-- From -->

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'From', 'wpsite-post-status-notification' ) ?></label>
					<div class="col-sm-9">
						<input id="wpsite_post_status_notifications_settings_message_from_name" name="wpsite_post_status_notifications_settings_message_from_name" type="text" class="form-control" value="<?php echo ( isset( $settings['message']['from_name'] ) ? esc_attr( $settings['message']['from_name'] ) : get_bloginfo('name')); ?>" placeholder="<?php esc_html_e( 'My Website Name or My Company Name' ) ?>">
						<em class="help-block"><?php esc_html_e( 'The From email address for all email notifications.  Please enter in emails separated by commas.', 'wpsite-post-status-notification' ) ?></em>
						<input id="wpsite_post_status_notifications_settings_message_from_email" name="wpsite_post_status_notifications_settings_message_from_email" type="text" class="form-control" value="<?php echo ( isset( $settings['message']['from_email'] ) ? esc_attr( $settings['message']['from_email'] ) : 'wordpress@' . get_site_url()); ?>" placeholder="<?php esc_html_e( 'email@example.com or email@example.com,another@example.com', 'wpsite-post-status-notification' ) ?>">
						<em class="help-block"><?php esc_html_e( 'The From email address for all email notifications.  Please enter in emails separated by commas.', 'wpsite-post-status-notification' ) ?></em>
					</div>
				</div>

				<!-- Cc -->

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'Cc', 'wpsite-post-status-notification' ) ?></label>
					<div class="col-sm-9">
						<input id="wpsite_post_status_notifications_settings_message_cc_email" name="wpsite_post_status_notifications_settings_message_cc_email" type="text" class="form-control" value="<?php echo esc_attr( $settings['message']['cc_email'] ) ?>" placeholder="<?php esc_html_e( 'email@example.com or email@example.com,another@example.com', 'wpsite-post-status-notification' ) ?>">
						<em class="help-block"><?php esc_html_e( 'The Cc email address for all email notifications.  Please enter in emails separated by commas.', 'wpsite-post-status-notification' ) ?></em>
					</div>
				</div>

				<!-- Bcc -->

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'Bcc', 'wpsite-post-status-notification' ) ?></label>
					<div class="col-sm-9">
						<input id="wpsite_post_status_notifications_settings_message_bcc_email" name="wpsite_post_status_notifications_settings_message_bcc_email" type="text" class="form-control" value="<?php echo esc_attr( $settings['message']['bcc_email'] ) ?>" placeholder="<?php esc_html_e( 'email@example.com or email@example.com,another@example.com', 'wpsite-post-status-notification' ) ?>">
						<em class="help-block"><?php esc_html_e( 'The Bcc email address for all email notifications.  Please enter in emails separated by commas.', 'wpsite-post-status-notification' ) ?></em>
					</div>
				</div>

				<div class="page-header">
					<h1><?php esc_html_e( 'Custom Emails, Subjects and Messages', 'wpsite-post-status-notification' ) ?></h1>
				</div>

				<!-- Dynamic Fields Legend -->

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'Dynamic Fields Legend', 'wpsite-post-status-notification' ) ?></label>
					<div class="col-sm-9">

						<table class="table table-striped table-responsive">
							<thead>
								<th><?php esc_html_e( 'Placeholder', 'wpsite-post-status-notification' ) ?></th>
								<th><?php esc_html_e( 'Description', 'wpsite-post-status-notification' ) ?></th>
							</thead>
							<tbody>
								<tr>
									<td><?php esc_html_e( '{post_title}', 'wpsite-post-status-notification' ) ?></td>
									<td><?php esc_html_e( 'The title of the post (i.e. Hello World!)', 'wpsite-post-status-notification' ) ?></td>
								</tr>
								<tr>
									<td><?php esc_html_e( '{post_type}', 'wpsite-post-status-notification' ) ?></td>
									<td><?php esc_html_e( 'The type of the post (i.e. post or page).', 'wpsite-post-status-notification' ) ?></td>
								</tr>
								<tr>
									<td><?php esc_html_e( '{post_url}', 'wpsite-post-status-notification' ) ?></td>
									<td><?php esc_html_e( 'The post\'s permalink (i.e. http://example.com/post-title)', 'wpsite-post-status-notification' ) ?></td>
								</tr>
								<tr>
									<td><?php esc_html_e( '{display_name}', 'wpsite-post-status-notification' ) ?></td>
									<td><?php esc_html_e( 'The post author\'s display name (i.e. Bod Smith)', 'wpsite-post-status-notification' ) ?></td>
								</tr>
								<tr>
									<td><?php esc_html_e( '{break_line}', 'wpsite-post-status-notification' ) ?></td>
									<td><?php esc_html_e( 'A new line in the email, this is great for adding spaces between sentences.', 'wpsite-post-status-notification' ) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- Email when a post is published -->

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'Post Published', 'wpsite-post-status-notification' ) ?><br /><button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#post-published"><?php esc_html_e( 'Example Email', 'wpsite-post-status-notification' ) ?></button></label>
					<div class="col-sm-9">
						<p class="form-control-static"><em><?php esc_html_e( 'Sent to users when a post has been published.  If the post being published was written by a contributor then they will receive a custom email as seen below.', 'wpsite-post-status-notification' ) ?></em></p>
						<label><?php esc_html_e( 'Subject', 'wpsite-post-status-notification' ) ?></label><br/>
						<input id="wpsite_post_status_notifications_settings_message_subject_published" name="wpsite_post_status_notifications_settings_message_subject_published" type="text" class="form-control" value="<?php echo esc_attr( $settings['message']['subject_published'] ) ?>"/><br/>

						<label><?php esc_html_e( 'Content', 'wpsite-post-status-notification' ) ?></label><br/>
						<textarea rows="10" cols="50" class="form-control" id="wpsite_post_status_notifications_settings_message_content_published" name="wpsite_post_status_notifications_settings_message_content_published"><?php echo esc_attr( $settings['message']['content_published'] ) ?></textarea>
					</div>
				</div>

				<!-- Email sent to contributor when their post is published -->

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'Contributor\'s Post Published', 'wpsite-post-status-notification' ) ?><br /><button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#post-published-contributor"><?php esc_html_e( 'Example Email', 'wpsite-post-status-notification' ) ?></button></label>
					<div class="col-sm-9">
						<p class="form-control-static"><em><?php esc_html_e( 'Sent to the contributor when their post is published.', 'wpsite-post-status-notification' ) ?></em></p>
						<label><?php esc_html_e( 'Subject', 'wpsite-post-status-notification' ) ?></label><br/>
						<input id="wpsite_post_status_notifications_settings_message_subject_published_contributor" name="wpsite_post_status_notifications_settings_message_subject_published_contributor" type="text" class="form-control" value="<?php echo esc_attr( $settings['message']['subject_published_contributor'] ) ?>"/><br/>

						<label><?php esc_html_e( 'Content', 'wpsite-post-status-notification' ) ?></label><br/>
						<textarea rows="10" cols="50" class="form-control" id="wpsite_post_status_notifications_settings_message_content_published_contributor" name="wpsite_post_status_notifications_settings_message_content_published_contributor"><?php echo esc_attr( $settings['message']['content_published_contributor'] ) ?></textarea>
					</div>
				</div>

				<!-- Email sent to admin when contributor submits post for review -->

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'Post Submitted for Review', 'wpsite-post-status-notification' ) ?><br /><button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#submit-for-review"><?php esc_html_e( 'Example Email', 'wpsite-post-status-notification' ) ?></button></label>
					<div class="col-sm-9">
						<p class="form-control-static"><em><?php esc_html_e( 'Sent to admins or editors when a contributor submits a post for review.', 'wpsite-post-status-notification' ) ?></em></p>
						<label><?php esc_html_e( 'Subject', 'wpsite-post-status-notification' ) ?></label><br/>
						<input id="wpsite_post_status_notifications_settings_message_subject_pending" name="wpsite_post_status_notifications_settings_message_subject_pending" type="text" class="form-control" value="<?php echo esc_attr( $settings['message']['subject_pending'] ) ?>"/><br/>

						<label><?php esc_html_e( 'Content', 'wpsite-post-status-notification' ) ?></label><br/>
						<textarea rows="10" cols="50" class="form-control" id="wpsite_post_status_notifications_settings_message_content_pending" name="wpsite_post_status_notifications_settings_message_content_pending"><?php echo esc_attr( $settings['message']['content_pending'] ) ?></textarea>
					</div>
				</div>

				<?php wp_nonce_field( 'wpsite_post_status_notifications_admin_settings' ) ?>

				<p class="submit">
					<input type="submit" name="submit" id="submit" class="btn btn-info" value="<?php esc_html_e( 'Save Changes', 'wpsite-post-status-notification' ) ?>">
				</p>

			</form>

			<div class="modal fade" id="post-published" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document" style="margin-top: 10vh;width: 800px;max-width: 100%;">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title"><?php esc_html_e( 'Example Email', 'wpsite-post-status-notification' ) ?></h4>
						</div>
						<div class="modal-body">
							<img style="width: 100%;" src="<?php echo wpsite_psn()->plugin_url() . 'img/post-published.png'; ?>"/>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php esc_html_e( 'Close', 'wpsite-post-status-notification' ) ?></button>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade" id="post-published-contributor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document" style="margin-top: 10vh;width: 800px;max-width: 100%;">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title"><?php esc_html_e( 'Example Email', 'wpsite-post-status-notification' ) ?></h4>
						</div>
						<div class="modal-body">
							<img style="width: 100%;" src="<?php echo wpsite_psn()->plugin_url() . 'img/post-published-contributor.png'; ?>"/>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php esc_html_e( 'Close', 'wpsite-post-status-notification' ) ?></button>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade" id="submit-for-review" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document" style="margin-top: 10vh;width: 800px;max-width: 100%;">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title"><?php esc_html_e( 'Example Email', 'wpsite-post-status-notification' ) ?></h4>
						</div>
						<div class="modal-body">
							<img style="width: 100%;" src="<?php echo wpsite_psn()->plugin_url() . 'img/please-moderate.png'; ?>"/>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php esc_html_e( 'Close', 'wpsite-post-status-notification' ) ?></button>
						</div>
					</div>
				</div>
			</div>

		</div>

		<?php require_once( 'sidebar.php' ) ?>

	</div>

	<?php require_once( 'footer.php' ) ?>

</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	$( "#tabs" ).tabs();
});
</script>
