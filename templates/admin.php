<div class="wrap">

	<script>(function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/ja_JP/all.js#xfbml=1&appId=983379265125123";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));</script>

	<h1 class="wp-heading-inline">
		<?php echo esc_html__( 'Hamazon Affiliate Setting', 'hamazon' ) ?>
	</h1>

	<div class="hamazon-wrapper">

		<div class="hamazon-row">

			<div class="hamazon-main">
				<form method="post" action="<?php echo admin_url( 'options.php' ) ?>">
					<?php
					settings_fields( 'wp-hamazon' );
					do_settings_sections( 'wp-hamazon' );
					submit_button();
					?>
				</form>
			</div>

			<div class="hamazon-sidebar">
				<img src="<?= hamazon_asset_url( '/img/admin-banner.jpg' ) ?>" alt="Hamazon" class="hamazon-sidebar-banner">



				<h3 class="hamazon-sidebar-title">
					<?php esc_html_e( 'Related Links', 'hamazon' ) ?>
				</h3>

				<ul class="hamazon-sidebar-list">
					<li>
						<?php echo wp_kses_post( sprintf( __( 'This plugin is hosted on <a href="%s" target="_blank">github</a>. Any pull requests are welcomed!', 'hamazon' ), 'https://github.com/fumikito/wp-hamazon' ) )?>
					</li>
					<li>
						<?php echo wp_kses_post( sprintf( __( '<a href="%s" target="_blank">Gisnism.info</a> has lots of tips. Please touch it and join us.', 'hamazon' ), 'https://gianism.info' ) )?>
					</li>
					<li>
						<?php echo wp_kses_post( sprintf( __( 'Please review our plugin at <a href="%s" target="_blank">WordPress.org</a>. Feedback will grow us.', 'hamazon' ), 'https://wordpress.org/support/plugin/wp-hamazon/reviews/#new-post' ) )?>
					</li>
				</ul>

				<hr />

				<h3 class="hamazon-sidebar-title">
					<?php esc_html_e( 'Public Resources', 'hamazon' ) ?>
				</h3>

				<ul class="hamazon-sidebar-list">

				</ul>

				<hr />

				<?php
				$locale = get_locale();
				$user = get_userdata( get_current_user_id() );
				?>

				<!-- Begin MailChimp Signup Form -->
				<div id="mc_embed_signup">
					<!-- Begin MailChimp Signup Form -->
					<div id="mc_embed_signup">
						<form
								action="//gianism.us14.list-manage.com/subscribe/post?u=9b5777bb4451fb83373411d34&amp;id=1e82da4148&amp;SINGUP=Hamazon-Admin"
								method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate"
								target="_blank" novalidate>
							<div id="mc_embed_signup_scroll">
								<h4><?php esc_html_e( 'Join our News Letter!', 'hamazon' ) ?></h4>
								<p class="description">
									<?php esc_html_e( 'We provide pragmatic information and tips. Not often, No span.', 'hamazon' ) ?>
								</p>
								<div class="mc-field-group">
									<label for="mce-EMAIL">
										<?php esc_html_e( 'Email' , 'hamazon' ) ?>
										<span class="asterisk">*</span>
									</label>
									<input type="email" name="EMAIL" class="required email" id="mce-EMAIL" value="<?php echo esc_attr( $user->user_email ) ?>">
								</div>
								<div class="mc-field-2col mc-field-name-<?php echo esc_attr( get_locale() ) ?>">
									<div>
										<div class="mc-field-group first-name">
											<label for="mce-FNAME"><?php esc_html_e( 'First Name' , 'hamazon' ) ?></label>
											<input type="text" value="<?php echo esc_attr( get_user_meta( $user->ID, 'first_name', true ) ) ?>" name="FNAME" class="" id="mce-FNAME">
										</div>
									</div>
									<div>
										<div class="mc-field-group last-name">
											<label for="mce-LNAME"><?php esc_html_e( 'Last Name' , 'hamazon' ) ?></label>
											<input type="text" value="<?php echo esc_attr( get_user_meta( $user->ID, 'last_name', true ) ) ?>" name="LNAME" class="" id="mce-LNAME">
										</div>
									</div>
									<div style="clear:both;"></div>
								</div>
								<p>
									<label class="inline" for="mce-group[1111]-1111-0">
										<input type="checkbox" value="1" name="group[1111][1]"
											   id="mce-group[1111]-1111-0" <?php checked( 'ja' != $locale ) ?>>
										<?php esc_html_e( 'English' , 'hamazon' ) ?>
									</label>
									<label class="inline" for="mce-group[1111]-1111-1">
										<input type="checkbox" value="2" name="group[1111][2]"
											   id="mce-group[1111]-1111-1" <?php checked( 'ja' == $locale ) ?>>
										<?php esc_html_e( 'Japanese' , 'hamazon' ) ?>
									</label>
								</p>
								<input type="hidden" name="group[1115]" value="16"/>
								<input type="hidden" name="b_9b5777bb4451fb83373411d34_1e82da4148" value="">
							</div>
							<p class="submit">
								<input type="submit" value="<?php esc_html_e( 'Subscribe' , 'hamazon' ) ?>" name="subscribe"
									   id="mc-embedded-subscribe" class="button-primary">
							</p>
						</form>
					</div>
				</div>

				<hr />

				<h3 class="hamazon-sidebar-title">
					<?php esc_html_e( 'Social Links', 'hamazon' ) ?>
				</h3>

				<div class="fb-page" data-href="https://www.facebook.com/gianism.info" data-small-header="true"
					 data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"
					 data-show-posts="false">
					<div class="fb-xfbml-parse-ignore">
						<blockquote cite="https://www.facebook.com/gianism.info"><a
									href="https://www.facebook.com/gianism.info">Gianism</a></blockquote>
					</div>
				</div>
				<p class="social-link">
					<a href="https://twitter.com/intent/tweet?screen_name=wpGianism" class="twitter-mention-button"
					   data-lang="ja" data-related="takahashifumiki">Tweet to @wpGianism</a>
					<script>!function (d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (!d.getElementById(id)) {
                          js = d.createElement(s);
                          js.id = id;
                          js.src = "//platform.twitter.com/widgets.js";
                          fjs.parentNode.insertBefore(js, fjs);
                        }
                      }(document, "script", "twitter-wjs");</script>
				</p>

				<p class="hamazon-sidebar-desc description">
					<?php esc_html_e( 'Please visit our social media to make us more powerful!', 'hamazon' ) ?>
				</p>

			</div>
		</div>

	</div>


</div>