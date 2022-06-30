<?php
/**
 * Class Easyjobs_Shortcode
 * Handles all public shortcodes for Easyjobs
 *
 * @since 1.0.0
 * @package easyjobs
 */

class Easyjobs_Shortcode {

	/**
	 * Easyjobs_Shortcode constructor.
	 */
	public function __construct() {
		add_shortcode( 'easyjobs', array( $this, 'render_easyjobs_shortcode' ) );
		add_shortcode( 'easyjobs_list', array( $this, 'render_easyjobs_list_shortcode' ) );
		add_shortcode( 'easyjobs_details', array( $this, 'render_easyjobs_details_shortcode' ) );
	}

	/**
	 * Render content for shortcode 'easyjobs'
	 *
	 * @since 1.0.0
	 * @return false|string
	 */
	public function render_easyjobs_shortcode() {
		$company = $this->get_company_info();

		ob_start();
		include EASYJOBS_PUBLIC_PATH . 'partials/easyjobs-jobs-landing.php';
		return ob_get_clean();
	}
	/**
	 * Render content for shortcode 'easyjobs_details'
	 *
	 * @since 1.0.0
	 * @return false|string
	 */
	public function render_easyjobs_details_shortcode( $atts ) {
		if ( empty( $atts['id'] ) ) {
			return '';
		}
		$company = $this->get_company_info();
		if ( ! empty( $company->company_analytics ) && ! empty( $company->company_analytics->id ) ) {
			$this->insert_analytics_script( $company->company_analytics );
		}
		$job = Easyjobs_Helper::get_job( $atts['id'] );
		ob_start();
		include EASYJOBS_PUBLIC_PATH . 'partials/easyjobs-job-details.php';
		return ob_get_clean();
	}

	public function render_easyjobs_list_shortcode() {
		if ( ! Easyjobs_Helper::is_api_connected() ) {
			return esc_html__( 'Api is not connected', 'easyjobs' );
		}
		$company = Easyjobs_Helper::get_company_info();
		if ( ! empty( $company->company_analytics ) && ! empty( $company->company_analytics->id ) ) {
			$this->insert_analytics_script( $company->company_analytics );
		}
		$jobs                 = $this->get_published_jobs();
		$job_with_page_id     = Easyjobs_Helper::get_job_with_page( $jobs );
		$new_job_with_page_id = EasyJobs_Helper::create_pages_if_required( $jobs, $job_with_page_id );

		// if there is new job and page, we need to add it
		$job_with_page_id = $job_with_page_id + $new_job_with_page_id;

		ob_start();
		include EASYJOBS_PUBLIC_PATH . 'partials/easyjobs-job-list.php';
		return ob_get_clean();
	}

	/**
	 * Get published job from api
	 *
	 * @since 1.0.0
	 * @return object|false
	 */
	private function get_published_jobs() {
		$jobs = Easyjobs_Api::get( 'published_jobs' );
		if ( $jobs->status === 'success' ) {
			return $jobs->data;
		}
		return false;
	}

	/**
	 * Get company info from api
	 *
	 * @since 1.0.0
	 * @return object|bool
	 */
	private function get_company_info() {
		$company_info = Easyjobs_Api::get( 'company' );
		if ( ! empty( $company_info ) && $company_info->status == 'success' ) {
			return $company_info->data;
		}
		return false;
	}

	private function insert_analytics_script( $analytics ) {
		add_action(
			'wp_footer',
			function() use ( $analytics ) {
				?>
			<!-- Matomo -->
			<script type="text/javascript">
				var _paq = window._paq = window._paq || [];
				/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
				_paq.push(["setDomains", <?php echo wp_json_encode( wp_unslash( $analytics->urls ) ); ?>]);
				_paq.push(['trackPageView']);
				_paq.push(['enableLinkTracking']);
				(function() {
					var u="<?php echo esc_url( EASYJOBS_ANALYTICS_URL ); ?>";
					_paq.push(['setTrackerUrl', u+'matomo.php']);
					_paq.push(['setSiteId', <?php echo esc_attr( $analytics->id ); ?>]);
					var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
					g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
				})();
			</script>
			<noscript><p><img src="//matomo.easyjobs.dev/matomo.php?idsite=<?php echo esc_attr( $analytics->id ); ?>&amp;rec=1" style="border:0;" alt="" /></p></noscript>
			<!-- End Matomo Code -->
				<?php
			}
		);
	}
}
