<?php
/**
 * Render jobs landing page for shortcode
 *
 * @since 1.0.0
 * @package easyjobs
 */

?>
<div class="easyjobs-shortcode-wrapper">
	<?php if ( ! empty( $company ) ) : ?>
		<div class="easyjobs-details">
			<?php if ( ! empty( $company->cover_photo ) ) : ?>
				<div class="ej-job-cover">
					<img src="<?php echo esc_url( $company->cover_photo[0] ); ?>" alt="<?php echo esc_attr( $company->name ); ?>">
				</div>
			<?php else : ?>
				<div class="ej-no-cover-photo"></div>
			<?php endif; ?>
			<div class="ej-header">
				<div class="ej-company-highlights">
					<?php if ( ! get_theme_mod( 'easyjobs_landing_hide_company_info', false ) ) : ?>
						<div class="ej-company-info">
							<?php if ( ! get_theme_mod( 'easyjobs_landing_hide_company_logo', false ) ) : ?>
								<div class="logo">
									<img src="<?php echo esc_url( $company->logo ); ?>" alt="">
								</div>
							<?php endif; ?>
							<div class="info">
								<h2 class="name"><?php echo esc_html( $company->name ); ?></h2>
								<?php if ( ! empty( $company->address ) ) : ?>
									<p class="location">
										<i class="easyjobs-icon easyjobs-map-maker"></i>
										<span>
											<?php
											echo ! empty( $company->address->city->name ) ? esc_html( $company->address->city->name )
												. ', ' : ''
											?>
											<?php echo ! empty( $company->address->country->name ) ? esc_html( $company->address->country->name ) : ''; ?>
										</span>
									</p>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( ! get_theme_mod( 'easyjobs_landing_hide_company_website_button' ) ) : ?>
						<div class="ej-header-tools">
							<a href="<?php echo esc_url( $company->website ); ?>" class="ej-btn ej-info-btn">
								<?php esc_html_e( 'Explore company website', 'easyjobs' ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $company->description ) ) : ?>
					<?php if ( ! get_theme_mod( 'easyjobs_landing_hide_company_description', false ) ) : ?>
						<div class="ej-company-description">
							<?php
							echo wp_kses(
								$company->description,
								array(
									'div'    => array(
										'class' => array(),
										'style' => array(),
									),
									'p'      => array(
										'class' => array(),
										'style' => array(),
									),
									'h1'     => array(
										'class' => array(),
										'style' => array(),
									),
									'h2'     => array(
										'class' => array(),
										'style' => array(),
									),
									'h3'     => array(
										'class' => array(),
										'style' => array(),
									),
									'h4'     => array(
										'class' => array(),
										'style' => array(),
									),
									'span'   => array(
										'class' => array(),
										'style' => array(),
									),
									'strong' => array(
										'class' => array(),
										'style' => array(),
									),
									'em'     => array(
										'class' => array(),
										'style' => array(),
									),
									'b'      => array(
										'class' => array(),
										'style' => array(),
									),
									'a'      => array(
										'class' => array(),
										'style' => array(),
										'href'  => array(),
										'title' => array(),
									),
									'ul'  => array(
										'class' => array(),
										'style' => array(),
									),
									'li'  => array(
										'class' => array(),
										'style' => array(),
									),
								)
							)
							?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<div class="ej-job-body">
				<div class="ej-section">
					<div class="ej-section-title">
						<span class="ej-section-title-icon"><i class="easyjobs-icon easyjobs-briefcase"></i></span>
						<span class="ej-section-title-text">
							<?php
							echo esc_html(
								get_theme_mod(
									'easyjobs_landing_job_list_heading',
									esc_html__( 'Open Job Positions', 'easyjobs' )
								)
							);
							?>
						</span>
					</div>
					<div class="ej-section-content">
						<?php echo do_shortcode( '[easyjobs_list]' ); ?>
					</div>
				</div>
				<?php if ( ! empty( $company->showcase_photo ) ) : ?>
					<div class="ej-section">
						<div class="ej-section-title">
							<span class="ej-section-title-icon"><i class="easyjobs-icon easyjobs-briefcase"></i></span>
							<span class="ej-section-title-text">
								<?php if ( ! empty( $showcase_heading = get_theme_mod( 'easyjobs_landing_showcase_heading' ) ) ) : ?>
									<?php echo esc_html( $showcase_heading ); ?>
								<?php else : ?>
									<?php echo esc_html__( 'Life at ', 'easyjobs' ) . esc_html( $company->name ); ?>
								<?php endif; ?>
							</span>
						</div>
						<div class="ej-section-content">
							<div class="ej-company-showcase">
								<div class="ej-showcase-inner">
									<div class="ej-showcase-left">
										<div class="ej-showcase-image">
											<div class="ej-image">
												<img src="<?php echo esc_url( $company->showcase_photo[0] ); ?>" alt="<?php echo esc_attr( $company->name ); ?>">
											</div>
										</div>
									</div>
									<?php if ( count( $company->showcase_photo ) > 1 ) : ?>
										<div class="ej-showcase-right">
											<?php foreach ( $company->showcase_photo as $key => $photo ) : ?>
												<?php
												if ( $key === 0 ) {
													continue;
												}
												?>
												<div class="ej-showcase-image">
													<div class="ej-image">
														<img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $company->name ); ?>">
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php else : ?>
		<h3>
			<?php esc_html_e( 'No open jobs right now', 'easyjobs' ); ?>
		</h3>
	<?php endif; ?>
</div>
