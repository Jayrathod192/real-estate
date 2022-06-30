<?php
/**
 * Render job list for shortcode
 *
 * @since 1.0.0
 */

?>

<?php if ( ! empty( $jobs ) && ! empty( $job_with_page_id ) ) : ?>
	<div class="easyjobs-shortcode-wrapper">
		<div class="ej-job-list">
			<?php foreach ( $jobs as $job ) : ?>
				<?php
				if ( $job->is_expired ) {
					continue;
				}
				?>
				<div class="ej-job-list-item">
					<div class="ej-job-list-item-inner">
						<div class="ej-job-list-item-col">
							<h2 class="ej-job-title">
								<a href="<?php echo esc_url( get_the_permalink( $job_with_page_id[ $job->id ] ) ); ?>"><?php echo esc_html( $job->title ); ?></a>
							</h2>
							<?php if ( ! get_theme_mod( 'easyjobs_landing_hide_job_metas', false ) ) : ?>
								<div class="ej-job-list-info">
									<div class="ej-job-list-info-block">
										<i class="easyjobs-icon easyjobs-briefcase-2"></i>
										<a href="<?php echo esc_url( $job->company_easyjob_url ); ?>" target="_blank">
											<?php echo esc_html( $job->company_name ); ?>
										</a>
									</div>
									<div class="ej-job-list-info-block">
										<i class="easyjobs-icon easyjobs-map-maker"></i>
										<?php if ( $job->is_remote ) : ?>
											<span><?php esc_html_e( 'Anywhere (Remote)', 'easyjobs' ); ?></span>
										<?php else : ?>
											<span>
												<?php if ( ! empty( $job->job_address->city ) ) : ?>
													<?php echo esc_html( $job->job_address->city->name ); ?>
												<?php endif; ?>
												<?php if ( ! empty( $job->job_address->country ) ) : ?>
													, <?php echo esc_html( $job->job_address->country->name ); ?>
												<?php endif; ?>
											</span>
										<?php endif ?>
									</div>
								</div>
							<?php endif; ?>
						</div>
						<div class="ej-job-list-item-col">
							<?php
							if ( ! $job->is_expired ) :
								?>
								<p class="ej-deadline">
									<?php echo esc_html( $job->expire_at ); ?>
								</p>
								<p class="ej-list-sub">
									<?php esc_html_e( 'No of vacancies: ', 'easyjobs' ); ?> <?php echo $job->vacancies ? esc_html( $job->vacancies ) : '1'; ?>
								</p>
							<?php else : ?>
								<p class="ej-list-title ej-expired">
									<?php esc_html_e( 'Expired', 'easyjobs' ); ?>
								</p>
							<?php endif; ?>
						</div>
						<div class="ej-job-list-item-col">
							<a href="<?php echo ! empty( $job->apply_url ) ? esc_url( $job->apply_url ) : '#'; ?>" class="ej-btn ej-info-btn-light" target="_blank">
								<?php esc_html_e( 'Apply', 'easyjobs' ); ?>
							</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php else : ?>
	<h3>
		<?php esc_html_e( 'No open jobs right now', 'easyjobs' ); ?>
	</h3>
<?php endif; ?>
