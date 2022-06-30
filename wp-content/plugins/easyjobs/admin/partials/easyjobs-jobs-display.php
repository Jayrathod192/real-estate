<?php
/**
 * Provide job list for plugin
 *
 * @link       https://easy.jobs
 * @since      1.0.0
 *
 * @package    Easyjobs
 * @subpackage Easyjobs/admin/partials
 */

$active_tab = 'published';
if ( isset( $_GET['tab'] ) && empty( $_GET['tab'] ) ) {
    $active_tab = trim( strtolower( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) );
}
$toggle_title = __( 'Published jobs', 'easyjobs' );
if ( $active_tab === 'draft' ) {
    $toggle_title = __( 'Draft jobs', 'easyjobs' );
}
if ( $active_tab === 'archive' ) {
    $toggle_title = __( 'Archived jobs', 'easyjobs' );
}
$duplicate_nonce = wp_create_nonce('ej_duplicate_nonce');
?>

<div class="wrap">
    <hr class="wp-header-end">
    <div class="easy-page-body">
        <main class="content-area">
            <?php require EASYJOBS_ADMIN_DIR_PATH . '/partials/easyjobs-admin-header.php'; ?>
            <?php $delete_nonce = wp_create_nonce( 'easyjobs_delete_job' ); ?>
            <!-- content body -->
            <div class="content-area__body">
                <!-- user list -->
                <section class="published-jobs section-gap">
                    <div class="form-filter align-items-center">
                        <div class="dropdown pipeline-action ej-job-type-select">
                            <button class="button white-button dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo esc_attr( $toggle_title ); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left nav-tab" role="tablist">
                                <a class="dropdown-item" href="#published-jobs" data-toggle="tab" role="tab" aria-controls="published-jobs" aria-selected="true">Published Jobs</a>
                                <a class="dropdown-item" href="#archive-jobs" data-toggle="tab" role="tab" aria-controls="published-jobs" aria-selected="false">Archived Jobs</a>
                                <a class="dropdown-item" href="#draft-jobs" data-toggle="tab" role="tab" aria-controls="draft-jobs" aria-selected="false">Draft Jobs</a>
                            </ul>
                        </div>
                        <div class="search-bar"><input type="text" class="easyjobs-job-search" placeholder="Search Job Name . . ." /></div>
                        <div class="ej-active-filter <?php echo $active_tab === 'published' ? esc_attr( 'show-filter' ) : ''; ?>">
                            <label class="checkbox">
                                <input  type="checkbox" value="active" name="ej-active-jobs" checked>
                                <span>Active</span>
                            </label>
                            <label class="checkbox">
                                <input  type="checkbox" value="expired" name="ej-expired-jobs" checked>
                                <span>Expired</span>
                            </label>
                        </div>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=easyjobs' ) ); ?>" class="button primary-button text-uppercase ml-auto">Create A new Job</a>
                    </div>
                    <?php if ( ! empty( $jobs ) ) : ?>
                    <div class="tab-content ej-tab-content">
                        <div class="tab-pane fade <?php echo $active_tab === 'published' ? esc_attr( 'active show' ) : ''; ?>" id="published-jobs" role="tabpanel" aria-labelledby="published-jobs">
                            <div class="section-title-wrap">
                                <h2 class="section-title">
                                    <?php esc_html_e( 'Published Jobs', 'easyjobs' ); ?>
                                </h2>
                            </div>
                            <?php if ( ! empty( $jobs->published ) && ! empty( $published_job_page_ids ) ) : ?>
                                <div class="row row-cols-1 row-cols-lg-2 ej-jobs">
                                    <?php foreach ( $jobs->published as $job ) : ?>
                                    <div class="col <?php echo $job->is_expired ? esc_attr( 'has-expired-job' ) : esc_attr( 'has-active-job' ); ?>">
                                        <div class="job-card">
                                            <div class="card-thumbnail" style="background-image: url('<?php echo esc_url( $job->banner_image ); ?>')">
                                                <?php if ( $job->is_expired ) : ?>
                                                    <div class="thumbnail__status thumbnail__status--danger">
                                                        <?php esc_html_e( 'Expired', 'easyjobs' ); ?>
                                                    </div>
                                                <?php else : ?>
                                                    <?php Easyjobs_Helper::get_job_status_badge( $job->status ); ?>
                                                <?php endif; ?>
                                                <div class="card-control">
                                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=easyjobs-admin&job-id=' . $job->id . '&view=pipeline' ) ); ?>" class="control-button control-button--primary">
                                                        <div class="control-button__icon">
                                                            <i class="easyjobs-icon easyjobs-pipe"></i>
                                                        </div>
                                                        <span>
                                                            <?php esc_html_e( 'Pipeline', 'easyjobs' ); ?>
                                                        </span>
                                                    </a>
                                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=easyjobs-admin&job-id=' . $job->id . '&view=candidates' ) ); ?>" class="control-button control-button--primary">
                                                        <div class="control-button__icon">
                                                            <i class="easyjobs-icon easyjobs-users"></i>
                                                        </div>
                                                        <span>
                                                            <?php esc_html_e( 'Candidates', 'easyjobs' ); ?>
                                                        </span>
                                                    </a>
                                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=easyjobs#/edit/' . $job->id ) ); ?>" class="control-button control-button--primary">
                                                        <div class="control-button__icon">
                                                            <i class="easyjobs-icon easyjobs-document"></i>
                                                        </div>
                                                        <span>
                                                            <?php esc_html_e( 'Edit', 'easyjobs' ); ?>
                                                        </span>
                                                    </a>
                                                    <div class="dropdown job-control-more">
                                                        <button class="control-button control-button--primary" data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                            <div class="control-button__icon">
                                                                <i class="easyjobs-icon easyjobs-plus"></i>
                                                            </div>
                                                            <span>
                                                                <?php esc_html_e( 'More', 'easyjobs' ); ?>
                                                            </span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-center">
                                                            <a class="dropdown-item" href="<?php echo esc_url( get_the_permalink( $published_job_page_ids[ $job->id ] ) ); ?>" target="_blank">
                                                                <div class="dropdown-icon"><i class="easyjobs-icon easyjobs-eye-1"></i></div><?php esc_html_e( 'View', 'easyjobs' ); ?>
                                                            </a>
                                                            <a class="dropdown-item ej-duplicate-job" href="#" data-job-id="<?php echo esc_html( $job->id ); ?>" data-nonce="<?php echo esc_html($duplicate_nonce); ?>">
                                                                <div class="dropdown-icon"><i class="easyjobs-icon easyjobs-duplicate"></i></div><?php esc_html_e( 'Duplicate', 'easyjobs' ); ?>
                                                            </a>
                                                            <a class="dropdown-item delete delete-job" href="#" data-job-id="<?php echo esc_html( $job->id ); ?>" target="_blank" data-nonce="<?php echo esc_html( $delete_nonce ); ?>">
                                                                <div class="dropdown-icon"><i class="easyjobs-icon easyjobs-delete"></i></div><?php esc_html_e( 'Delete', 'easyjobs' ); ?>
                                                            </a>
                                                            <?php if ( ! empty( $job->social_links ) ) : ?>
                                                            <div class="share-button">
                                                                <a class="dropdown-item" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <div class="dropdown-icon"><i class="easyjobs-icon easyjobs-share"></i></div>
                                                                    <?php esc_html_e( 'Share', 'easyjobs' ); ?>
                                                                </a>
                                                                <div class="share-button-menu">
                                                                    <a class="dropdown-item" href="<?php echo esc_url( $job->social_links->facebook ); ?>">
                                                                        <div class="dropdown-icon"><i class="easyjobs-icon easyjobs-facebook"></i></div><?php esc_html_e( 'facebook', 'easyjobs' ); ?>
                                                                    </a>
                                                                    <a class="dropdown-item" href="<?php echo esc_url( $job->social_links->linkedIn ); ?>">
                                                                        <div class="dropdown-icon"><i class="easyjobs-icon easyjobs-linkedin"></i></div><?php esc_html_e( 'linkedin', 'easyjobs' ); ?>
                                                                    </a>
                                                                    <a class="dropdown-item" href="<?php echo esc_url( $job->social_links->twitter ); ?>">
                                                                        <div class="dropdown-icon"><i class="easyjobs-icon easyjobs-twitter"></i></div><?php esc_html_e( 'twitter', 'easyjobs' ); ?>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-content">
                                                <div class="card-content__left">
                                                    <h3 class="card-title">
                                                        <?php echo esc_html( $job->title ); ?>
                                                    </h3>
                                                    <h4 class="card-sub-title">
                                                        <?php echo esc_html( $job->category->name ); ?>
                                                    </h4>
                                                    <div class="card-info-group">
                                                        <p class="card-info">
                                                            <?php esc_html_e( 'Post Date: ', 'easyjobs' ); ?>  <?php echo esc_html( $job->created_at ); ?>
                                                        </p>
                                                        <p class="card-info">
                                                          <?php esc_html_e( 'Expiry Date: ', 'easyjobs' ); ?>  <?php echo esc_html( $job->expire_at ); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="card-content__right">
                                                    <p><?php echo esc_html( $job->applicant_count ); ?> <?php esc_html_e( 'Applied', 'easyjobs' ); ?></p>
                                                    <?php if ( ! empty( $job->applicants ) ) : ?>
                                                    <div class="applicants__img">
                                                        <?php foreach ( $job->applicants as $applicant ) : ?>
                                                        <img src="<?php echo esc_url( $applicant->image ); ?>" alt="" />
                                                        <?php endforeach; ?>
                                                        <p class="Applicants"><?php echo esc_html( $job->applicant_count ); ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <h4 class="empty-message">
                                    <?php esc_html_e( 'No jobs found.', 'easyjobs' ); ?>
                                </h4>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane fade <?php echo $active_tab === 'archive' ? esc_html( 'active show' ) : ''; ?>" id="archive-jobs" role="tabpanel" aria-labelledby="archive-jobs">
                            <div class="section-title-wrap">
                                <h2 class="section-title">
                                    <?php esc_html_e( 'Archived Jobs', 'easyjobs' ); ?>
                                </h2>
                            </div>
                            <?php if ( ! empty( $jobs->archived ) ) : ?>
                                <div class="row row-cols-1 row-cols-lg-2 ej-jobs">
                                    <?php foreach ( $jobs->archived as $job ) : ?>
                                        <div class="col">
                                            <div class="job-card">
                                                <div class="card-thumbnail" style="background-image: url('<?php echo esc_url( $job->banner_image ); ?>')">
                                                    <?php if ( $job->is_expired ) : ?>
                                                        <div class="thumbnail__status thumbnail__status--danger">
                                                            <?php esc_html_e( 'Expired', 'easyjobs' ); ?>
                                                        </div>
                                                    <?php else : ?>
                                                        <?php Easyjobs_Helper::get_job_status_badge( $job->status ); ?>
                                                    <?php endif; ?>
                                                    <div class="card-control">
                                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=easyjobs-admin&job-id=' . $job->id . '&view=pipeline' ) ); ?>" class="control-button control-button--primary">
                                                            <div class="control-button__icon">
                                                                <i class="easyjobs-icon easyjobs-pipe"></i>
                                                            </div>
                                                            <span>
                                                            <?php esc_html_e( 'Pipeline', 'easyjobs' ); ?>
                                                        </span>
                                                        </a>
                                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=easyjobs#/edit/' . $job->id ) ); ?>"  class="control-button control-button--primary">
                                                            <div class="control-button__icon">
                                                                <i class="easyjobs-icon easyjobs-document"></i>
                                                            </div>
                                                            <span>
                                                            <?php esc_html_e( 'Edit', 'easyjobs' ); ?>
                                                        </span>
                                                        </a>
                                                        <a href="#" class="control-button control-button--primary ej-duplicate-job" data-job-id="<?php echo esc_html( $job->id); ?>" data-nonce="<?php echo esc_html($duplicate_nonce);?>">
                                                            <div class="control-button__icon">
                                                                <i class="easyjobs-icon easyjobs-duplicate"></i>
                                                            </div>
                                                            <span>
                                                                <?php esc_html_e( 'Duplicate', 'easyjobs' ); ?>
                                                            </span>
                                                        </a>
                                                        <a href="#" class="control-button control-button--danger delete-job" data-job-id="<?php echo esc_html( $job->id ); ?>" data-nonce="<?php echo esc_html( $delete_nonce ); ?>">
                                                            <div class="control-button__icon">
                                                                <i class="easyjobs-icon easyjobs-delete"></i>
                                                            </div>
                                                            <span>
                                                                <?php esc_html_e( 'Delete', 'easyjobs' ); ?>
                                                            </span>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="card-content">
                                                    <div class="card-content__left">
                                                        <h3 class="card-title">
                                                            <?php echo esc_html( $job->title ); ?>
                                                        </h3>
                                                        <h4 class="card-sub-title">
                                                            <?php echo esc_html( $job->category->name ); ?>
                                                        </h4>
                                                        <div class="card-info-group">
                                                            <p class="card-info">
                                                                <?php esc_html_e( 'Post Date: ', 'easyjobs' ); ?>  <?php echo esc_html( $job->created_at ); ?>
                                                            </p>
                                                            <p class="card-info">
                                                                <?php esc_html_e( 'Expiry Date: ', 'easyjobs' ); ?>  <?php echo esc_html( $job->expire_at ); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="card-content__right">
                                                        <p><?php echo esc_html( $job->applicant_count ); ?> <?php esc_html_e( 'Applied', 'easyjobs' ); ?></p>
                                                        <?php if ( ! empty( $job->applicants ) ) : ?>
                                                        <div class="applicants__img">
                                                            <?php foreach ( $job->applicants as $applicant ) : ?>
                                                                <img src="<?php echo esc_url( $applicant->image ); ?>" alt="" />
                                                            <?php endforeach; ?>
                                                            <p class="Applicants"><?php echo esc_html( $job->applicant_count ); ?></p>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <h4 class="empty-message">
                                    <?php esc_html_e( 'No jobs found.', 'easyjobs' ); ?>
                                </h4>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane fade <?php echo $active_tab === 'draft' ? esc_html( 'active show' ) : ''; ?>" id="draft-jobs" role="tabpanel" aria-labelledby="draft-jobs">
                            <div class="section-title-wrap">
                                <h2 class="section-title">
                                    <?php esc_html_e( 'Draft Jobs', 'easyjobs' ); ?>
                                </h2>
                            </div>
                            <?php if ( ! empty( $jobs->draft ) ) : ?>
                                <div class="row row-cols-1 row-cols-lg-2 ej-jobs">
                                    <?php foreach ( $jobs->draft as $job ) : ?>
                                        <div class="col">
                                            <div class="job-card">
                                                <div class="card-thumbnail" style="background-image: url('<?php echo esc_url( $job->banner_image ); ?>')">
                                                    <?php if ( $job->is_expired ) : ?>
                                                        <div class="thumbnail__status thumbnail__status--danger">
                                                            <?php esc_html_e( 'Expired', 'easyjobs' ); ?>
                                                        </div>
                                                    <?php else : ?>
                                                        <?php Easyjobs_Helper::get_job_status_badge( $job->status ); ?>
                                                    <?php endif; ?>
                                                    <div class="card-control">
                                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=easyjobs#/edit/' . $job->id ) ); ?>" class="control-button control-button--primary">
                                                            <div class="control-button__icon">
                                                                <i class="easyjobs-icon easyjobs-document"></i>
                                                            </div>
                                                            <span>
                                                            <?php esc_html_e( 'Edit', 'easyjobs' ); ?>
                                                        </span>
                                                        </a>
                                                        <a href="#" class="control-button control-button--primary ej-duplicate-job" data-job-id="<?php echo esc_html( $job->id); ?>" data-nonce="<?php echo esc_html($duplicate_nonce);?>">
                                                            <div class="control-button__icon">
                                                                <i class="easyjobs-icon easyjobs-duplicate"></i>
                                                            </div>
                                                            <span>
                                                                <?php esc_html_e( 'Duplicate', 'easyjobs' ); ?>
                                                            </span>
                                                        </a>
                                                        <a href="#" class="control-button control-button--danger delete-job" data-job-id="<?php echo esc_html( $job->id ); ?>" data-nonce="<?php echo esc_html( $delete_nonce ); ?>">
                                                            <div class="control-button__icon">
                                                                <i class="easyjobs-icon easyjobs-delete"></i>
                                                            </div>
                                                            <span>
                                                                <?php esc_html_e( 'Delete', 'easyjobs' ); ?>
                                                            </span>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="card-content">
                                                    <div class="card-content__left">
                                                        <h3 class="card-title">
                                                            <?php echo esc_html( $job->title ); ?>
                                                        </h3>
                                                        <h4 class="card-sub-title">
                                                            <?php echo esc_html( $job->category->name ); ?>
                                                        </h4>
                                                        <div class="card-info-group">
                                                            <p class="card-info">
                                                                <?php esc_html_e( 'Post Date: ', 'easyjobs' ); ?>  <?php echo esc_html( $job->created_at ); ?>
                                                            </p>
                                                            <p class="card-info">
                                                                <?php esc_html_e( 'Expiry Date: ', 'easyjobs' ); ?>  <?php echo esc_html( $job->expire_at ); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="card-content__right">
                                                        <p><?php echo esc_html( $job->applicant_count ); ?> <?php esc_html_e( 'Applied', 'easyjobs' ); ?></p>
                                                        <?php if ( ! empty( $job->applicants ) ) : ?>
                                                        <div class="applicants__img">
                                                            <?php foreach ( $job->applicants as $applicant ) : ?>
                                                                <img src="<?php echo esc_url( $applicant->image ); ?>" alt="" />
                                                            <?php endforeach; ?>
                                                            <p class="Applicants"><?php echo esc_html( $job->applicant_count ); ?></p>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <h4 class="empty-message">
                                    <?php esc_html_e( 'No jobs found.', 'easyjobs' ); ?>
                                </h4>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else : ?>
                        <h4 class="empty-message">
                            <?php esc_html_e( 'No jobs found.', 'easyjobs' ); ?>
                        </h4>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>
</div>
