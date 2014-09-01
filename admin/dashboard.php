<?php require ('header.php'); ?>

	<div id="wpsite_plugin_content">

		<div id="wpsite_plugin_settings">

			<form method="post">

				<div id="tabs">
						<ul>
							<li><a href="#wpsite_div_general"><span class="wpsite_admin_panel_content_tabs"><?php _e('General', self::$text_domain); ?></span></a></li>
							<li><a href="#wpsite_div_email"><span class="wpsite_admin_panel_content_tabs"><?php _e('Email',self::$text_domain); ?></span></a></li>
						</ul>

						<div id="wpsite_div_general">

							<h3><?php _e('Post Types', self::$text_domain); ?></h3>

							<table class="form-table">
								<tbody>

									<!-- Include these post types -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Include these post types', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">

												<?php foreach ($post_types as $post_type) { ?>
													<input type="checkbox" id="wpsite_post_status_notifications_settings_post_types_<?php echo $post_type; ?>" name="wpsite_post_status_notifications_settings_post_types_<?php echo $post_type; ?>" <?php echo (isset($settings['post_types']) && in_array($post_type, $settings['post_types']) ? 'checked="checked"' : '');?>/><label><?php printf(__('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%s', self::$text_domain), $post_type); ?></label><br />
												<?php } ?>

											</td>
										</th>
									</tr>

								</tbody>
							</table>

							<h3><?php _e('Share Links', self::$text_domain); ?></h3>

							<table class="form-table">
								<tbody>

									<!-- Twitter -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Twitter', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_share_links_twitter" name="wpsite_post_status_notifications_settings_message_share_links_twitter" type="checkbox" value="users" <?php echo isset($settings['message']['share_links']['twitter']) && $settings['message']['share_links']['twitter'] ? 'checked="checked"' : ''; ?>>
											</td>
										</th>
									</tr>

									<!-- Facebook -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Facebook', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_share_links_facebook" name="wpsite_post_status_notifications_settings_message_share_links_facebook" type="checkbox" value="users" <?php echo isset($settings['message']['share_links']['facebook']) && $settings['message']['share_links']['facebook'] ? 'checked="checked"' : ''; ?>>
											</td>
										</th>
									</tr>

									<!-- Google -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Google', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_share_links_google" name="wpsite_post_status_notifications_settings_message_share_links_google" type="checkbox" value="users" <?php echo isset($settings['message']['share_links']['google']) && $settings['message']['share_links']['google'] ? 'checked="checked"' : ''; ?>>
											</td>
										</th>
									</tr>

									<!-- LinkedIn -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('LinkedIn', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_share_links_linkedin" name="wpsite_post_status_notifications_settings_message_share_links_linkedin" type="checkbox" value="users" <?php echo isset($settings['message']['share_links']['linkedin']) && $settings['message']['share_links']['linkedin'] ? 'checked="checked"' : ''; ?>>
											</td>
										</th>
									</tr>

								</tbody>
							</table>

							<h3><?php _e('When a contributor submits a post for review notify: ', self::$text_domain); ?></h3>

							<table class="form-table">
								<tbody>

									<!-- Admins-->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Admins', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input name="wpsite_post_status_notifications_settings_pending_notify" type="radio" value="administrator" <?php echo isset($settings['pending_notify']) && $settings['pending_notify'] == 'administrator' ? 'checked' : ''; ?>>
											</td>
										</th>
									</tr>

									<!-- Editors -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Editors', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input name="wpsite_post_status_notifications_settings_pending_notify" type="radio" value="editor" <?php echo isset($settings['pending_notify']) && $settings['pending_notify'] == 'editor' ? 'checked' : ''; ?>>
											</td>
										</th>
									</tr>

								</tbody>
							</table>

							<h3><?php _e("When contributor's post is published notify: ", self::$text_domain); ?></h3>

							<table class="form-table">
								<tbody>

									<!-- Contributor -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Contributor only', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<input name="wpsite_post_status_notifications_settings_publish_notify" type="radio" value="author" <?php echo isset($settings['publish_notify']) && $settings['publish_notify'] == 'author' ? 'checked' : ''; ?>>
											</td>
										</th>
									</tr>

									<!-- All Users -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('All Users', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input name="wpsite_post_status_notifications_settings_publish_notify" type="radio" value="users" <?php echo isset($settings['publish_notify']) && $settings['publish_notify'] == 'users' ? 'checked' : ''; ?>>
											</td>
										</th>
									</tr>

									<!-- Admins -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Admins only', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input name="wpsite_post_status_notifications_settings_publish_notify" type="radio" value="admins" <?php echo isset($settings['publish_notify']) && $settings['publish_notify'] == 'admins' ? 'checked' : ''; ?>>
											</td>
										</th>
									</tr>

									<!-- Editors -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Editors only', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input name="wpsite_post_status_notifications_settings_publish_notify" type="radio" value="editors" <?php echo isset($settings['publish_notify']) && $settings['publish_notify'] == 'editors' ? 'checked' : ''; ?>>
											</td>
										</th>
									</tr>

								</tbody>
							</table>

						</div>


						<div id="wpsite_div_email">

							<h3><?php _e('Headers', self::$text_domain); ?></h3>

							<em><label><?php _e('Leave blank for defaults.', self::$text_domain); ?></label></em>

							<table class="form-table">
								<tbody>

									<!-- From -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('From', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_from_email" name="wpsite_post_status_notifications_settings_message_from_email" type="text" size="50" value="<?php echo esc_attr($settings['message']['from_email']); ?>"><br/>
												<em><label><?php _e('email (e.g. example@gmail.com or example@gmail.com, example1@gmail.com)', self::$text_domain); ?></label></em><br/>
												<em><label><?php _e('default (wordpress@yoursite.com)', self::$text_domain); ?></label></em>
											</td>
										</th>
									</tr>

									<!-- Cc -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Cc', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_cc_email" name="wpsite_post_status_notifications_settings_message_cc_email" type="text" size="50" value="<?php echo esc_attr($settings['message']['cc_email']); ?>"><br/>
												<em><label><?php _e('email (e.g. example@gmail.com or example@gmail.com, example1@gmail.com)', self::$text_domain); ?></label></em><br/>
												<em><label><?php _e('default (none)', self::$text_domain); ?></label></em>
											</td>
										</th>
									</tr>

									<!-- Bcc -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Bcc', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_bcc_email" name="wpsite_post_status_notifications_settings_message_bcc_email" type="text" size="50" value="<?php echo esc_attr($settings['message']['bcc_email']); ?>"><br/>
												<em><label><?php _e('email (e.g. example@gmail.com or example@gmail.com, example1@gmail.com)', self::$text_domain); ?></label></em><br/>
												<em><label><?php _e('default (none)', self::$text_domain); ?></label></em>
											</td>
										</th>
									</tr>

								</tbody>
							</table>

							<h3><?php _e('Custom email subjects and messages', self::$text_domain); ?></h3>

							<em><label><?php _e('Leave blank for defaults.', self::$text_domain); ?></label></em>

							<table class="form-table">
								<tbody>

									<!-- Email when post is published -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Sent when post is published', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<label><?php _e('Subject', self::$text_domain); ?></label><br/>
												<input id="wpsite_post_status_notifications_settings_message_subject_published" name="wpsite_post_status_notifications_settings_message_subject_published" type="text" size="50" value="<?php echo esc_attr($settings['message']['subject_published']); ?>"/><br/>

												<label><?php _e('Content', self::$text_domain); ?></label><br/>
												<textarea rows="10" cols="50" id="wpsite_post_status_notifications_settings_message_content_published" name="wpsite_post_status_notifications_settings_message_content_published"><?php echo esc_attr($settings['message']['content_published']); ?></textarea>
											</td>
										</th>
									</tr>


									<!-- Email sent to contributor when their post is published -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Sent to contributor when their post is published', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<label><?php _e('Subject', self::$text_domain); ?></label><br/>
												<input id="wpsite_post_status_notifications_settings_message_subject_published_contributor" name="wpsite_post_status_notifications_settings_message_subject_published_contributor" type="text" size="50" value="<?php echo esc_attr($settings['message']['subject_published_contributor']); ?>"/><br/>

												<label><?php _e('Content', self::$text_domain); ?></label><br/>
												<textarea rows="10" cols="50" id="wpsite_post_status_notifications_settings_message_content_published_contributor" name="wpsite_post_status_notifications_settings_message_content_published_contributor"><?php echo esc_attr($settings['message']['content_published_contributor']); ?></textarea>
											</td>
										</th>
									</tr>


									<!-- Email sent to admin when contributor submits post for review -->

									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Sent when post is submitted for review from a contributor', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<label><?php _e('Subject', self::$text_domain); ?></label><br/>
												<input id="wpsite_post_status_notifications_settings_message_subject_pending" name="wpsite_post_status_notifications_settings_message_subject_pending" type="text" size="50" value="<?php echo esc_attr($settings['message']['subject_pending']); ?>"/><br/>

												<label><?php _e('Content', self::$text_domain); ?></label><br/>
												<textarea rows="10" cols="50" id="wpsite_post_status_notifications_settings_message_content_pending" name="wpsite_post_status_notifications_settings_message_content_pending"><?php echo esc_attr($settings['message']['content_pending']); ?></textarea>
											</td>
										</th>
									</tr>

								</tbody>
							</table>

							<em><label><?php _e('*Please note that this does not affect the Share Links.', self::$text_domain); ?></label></em>

						</div>
					</div>

					<?php wp_nonce_field('wpsite_post_status_notifications_admin_settings'); ?>

					<?php submit_button(); ?>

				</form>

		</div> <!-- wpsite_plugin_settings -->

	<?php require ('sidebar.php'); ?>

	</div> <!-- /wpsite_plugin_content -->

<?php require ('footer.php'); ?>

<script type="text/javascript">
jQuery(document).ready(function($) {
	$( "#tabs" ).tabs();
});
</script>