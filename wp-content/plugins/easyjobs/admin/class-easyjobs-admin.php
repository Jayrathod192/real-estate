<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://easy.jobs
 * @since      1.0.0
 *
 * @package    Easyjobs
 * @subpackage Easyjobs/admin
 */

/**
 *
 * @package    Easyjobs
 * @subpackage Easyjobs/admin
 * @author     EasyJobs <support@easy.jobs>
 */
class Easyjobs_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * The slug of plugin main page
     *
     * @since 1.0.0
     * @var string
     */
	private $slug = 'easyjobs-admin';

    /**
     * Jobs admin class object
     *
     * @since 1.0.0
     * @var object
     */
	private $jobs;

    /**
     * Pipeline admin class object
     *
     * @since 1.0.0
     * @var object
     */
	private $pipeline;

    /**
     * Candidate admin class object
     *
     * @since 1.0.0
     * @var object
     */
	private $candidates;

    /**
     * Settings object
     *
     * @since 1.0.0
     * @var object
     */
	private $settings;

    /**
     * Dashboard admin class object
     *
     * @since 1.0.5
     * @var object
     */
    private $dashboard;

    /**
     * @var string
     */
    private $api_key_field = 'easyjobs_api_key';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->jobs        = new Easyjobs_Admin_Jobs();
		$this->pipeline    = new Easyjobs_Admin_Pipeline();
		$this->candidates  = new Easyjobs_Admin_Candidates();
		$this->settings    = (object) EasyJobs_DB::get_settings();
        $this->dashboard   = new Easyjobs_Admin_Dashboard();
        $this->update_company_data();
		if($this->isEasyJobsPage()){
			$this->set_verification_status();
		}
        $this->easyjobs_start_plugin_tracking();
        $this->admin_notice();
        add_action( 'admin_init', array( $this, 'maybe_redirect' ) );
		// Change title for inner pages
        add_filter( 'admin_title', array( $this, 'render_inner_page_title' ), 10, 2 );
        // Check api key
        add_action( 'wp_ajax_easyjobs_connect_api', array( $this, 'connect_api' ) );
        add_action( 'wp_ajax_easyjobs_disconnect_api', array( $this, 'disconnect_api' ) );
        add_action( 'wp_ajax_easyjobs_signin', array( $this, 'sign_in' ) );
        add_action( 'wp_ajax_easyjobs_save_company', array( $this, 'save_company' ) );
        add_action( 'wp_ajax_easyjobs_signup', array( $this, 'sign_up' ) );
        add_action( 'wp_ajax_easyjobs_create_company', array( $this, 'create_company' ) );
        add_action( 'wp_ajax_easyjobs_get_data', array( $this, 'get_data' ), 10 );
        add_action( 'wp_ajax_easyjobs_get_states', array( $this, 'get_states' ) );
        add_action( 'wp_ajax_easyjobs_get_cities', array( $this, 'get_cities' ) );
        add_action( 'wp_ajax_easyjobs_get_verification_status', array( $this, 'get_verification_status' ) );
        add_action( 'wp_ajax_easyjobs_post_data', array( $this, 'post_data' ) );
        add_action( 'wp_ajax_easyjobs_sync_data', array( $this, 'sync_data' ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Easyjobs_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Easyjobs_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        /********* New Styles */
        wp_enqueue_style(
            $this->plugin_name . '-eicon',
            EASYJOBS_ADMIN_URL . 'assets/vendor/css/eicon/style.css',
            array(),
            $this->version,
            'all'
        );

        wp_enqueue_style(
            $this->plugin_name . '-design',
            EASYJOBS_ADMIN_URL . 'assets/vendor/css/style.min.css',
            array(),
            $this->version,
            'all'
        );
		/****** end New Styles */

        wp_enqueue_style( $this->plugin_name, EASYJOBS_ADMIN_URL . 'assets/dist/css/easyjobs-admin.min.css', array(), $this->version, 'all' );
        wp_enqueue_style(
            $this->plugin_name . '-select2',
            EASYJOBS_ADMIN_URL . 'assets/dist/css/select2.min.css',
            array(),
            $this->version,
            'all'
        );

        $page_status = false;
        if ( $hook == 'easyjobs_page_easyjobs-settings' ) {
			$page_status = true;
		}

		if ( ! $page_status ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name . '-settings',
			EASYJOBS_ADMIN_URL . 'assets/dist/css/easyjobs-settings.css',
			array(),
            $this->version,
            'all'
		);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Easyjobs_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Easyjobs_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_script( $this->plugin_name . '-vendor', EASYJOBS_ADMIN_URL . 'assets/dist/js/easyjobs-admin-vendor-bundle.min.js', array( 'jquery' ), $this->version, true );

		/****** new scripts */

        /******* end new scripts */
		wp_enqueue_script(
			$this->plugin_name . '-select2',
			EASYJOBS_ADMIN_URL . 'assets/dist/js/select2.min.js',
			array( 'jquery' ),
            $this->version,
            true
		);
		wp_enqueue_script(
			$this->plugin_name . '-swal',
			EASYJOBS_ADMIN_URL . 'assets/dist/js/sweetalert.min.js',
			array( 'jquery' ),
            $this->version,
            true
		);

        wp_enqueue_script( 'jquery-ui-sortable' );

        if ( defined( 'EASYJOBS_DEV_SCRIPT' ) && EASYJOBS_DEV_SCRIPT ) {
            wp_register_script( 'easyjobs-react', EASYJOBS_ADMIN_URL . 'assets/dist/js/easyjobs.js', array( 'wp-i18n' ), filemtime( EASYJOBS_ADMIN_DIR_PATH . 'assets/dist/js/easyjobs.js' ), true );
			wp_enqueue_script( $this->plugin_name, EASYJOBS_ADMIN_URL . 'assets/src/js/easyjobs-admin.js', array( 'jquery' ), $this->version, true );
        } else {
            wp_register_script( 'easyjobs-react', EASYJOBS_ADMIN_URL . 'assets/dist/js/easyjobs.min.js', array( 'wp-i18n' ), filemtime( EASYJOBS_ADMIN_DIR_PATH . 'assets/dist/js/easyjobs.min.js' ), true );
			wp_enqueue_script( $this->plugin_name, EASYJOBS_ADMIN_URL . 'assets/dist/js/easyjobs-admin.min.js', array( 'jquery' ), $this->version, true );
        }



		$current_page_schema = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

        $data = array(
            'easyjobs_admin_url' => admin_url( 'admin.php?page=easyjobs-admin' ),
            'easyjobs_app_url'   => EASYJOBS_APP_URL,
            'admin_url'          => admin_url( 'admin.php' ),
            'ajax_url'           => admin_url( 'admin-ajax.php' ),
            'current_page'       => $current_page_schema . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'devtool'            => defined( 'EASYJOBS_DEVTOOL' ) ? EASYJOBS_DEVTOOL : false,
            'plugin_url'         => EASYJOBS_URL,
            'title'              => get_bloginfo( 'name`' ),
        );
        wp_localize_script( $this->plugin_name, 'easyJobsJs', $data );

    }

    /**
     * Toggle settings fields
     *
     * @since 1.0.0
     * @return array
     */
	public function toggleFields( $builder = false ) {
		$args = EasyJobs_Settings::settings_args();

		$toggleFields = $hideFields = $conditions = array();

		$tabs = $args;
		if ( ! empty( $tabs ) ) {
			foreach ( $tabs as $tab_id => $tab ) {
				$sections = isset( $tab['sections'] ) ? $tab['sections'] : array();
				if ( ! empty( $sections ) ) {
					foreach ( $sections as $section_id => $section ) {
						$fields = isset( $section['fields'] ) ? $section['fields'] : array();
						if ( ! empty( $fields ) ) {
							foreach ( $fields as $field_key => $field ) {
								if ( isset( $field['fields'] ) ) {
									foreach ( $field['fields'] as $inner_field_key => $inner_field ) {
										if ( isset( $inner_field['hide'] ) && ! empty( $inner_field['hide'] ) && is_array( $inner_field['hide'] ) ) {
											foreach ( $inner_field['hide'] as $key => $hide ) {
												$hideFields[ $inner_field_key ][ $key ] = $hide;
											}
										}
										if ( isset( $inner_field['dependency'] ) && ! empty( $inner_field['dependency'] ) && is_array( $inner_field['dependency'] ) ) {
											foreach ( $inner_field['dependency'] as $key => $dependency ) {
												$conditions[ $inner_field_key ][ $key ] = $dependency;
											}
										}
									}
								}

								if ( isset( $field['hide'] ) && ! empty( $field['hide'] ) && is_array( $field['hide'] ) ) {
									foreach ( $field['hide'] as $key => $hide ) {
										$hideFields[ $field_key ][ $key ] = $hide;
									}
								}
								if ( isset( $field['dependency'] ) && ! empty( $field['dependency'] ) && is_array( $field['dependency'] ) ) {
									foreach ( $field['dependency'] as $key => $dependency ) {
										$conditions[ $field_key ][ $key ] = $dependency;
									}
								}
							}
						}
					}
				}
			}
		}

		return array(
			'toggleFields' => $conditions, // TODO: toggling system has to be more optimized!
			'hideFields'   => $hideFields,
		);
	}
	/**
     * Add menu pages in admin
     *
     * @since 1.0.0
     * @return void
     */
	public function menu_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
            return;
		}
        // Added main menu page
        add_menu_page(
            'Easyjobs',
            'Easyjobs',
            'delete_users',
            $this->slug,
            array( $this, 'easyjobs_main_page' ),
            EASYJOBS_ADMIN_URL . 'assets/img/easyjobs-icon.svg',
            70
        );
        /**
         * Submenu pages
         * defined filter easyjobs_admin_menu for adding admin menus
         */
        $submenu_pages = apply_filters(
            'easyjobs_admin_menu',
            array(
				$this->slug                         => array(
					'title'      => __( 'Easyjobs - Dashboard', 'easyjobs' ),
					'menu_title' => __( 'Dashboard', 'easyjobs' ),
					'capability' => 'delete_users',
					'callback'   => array( $this, 'easyjobs_main_page' ),
				),
				'easyjobs-all-jobs'                 => array(
					'title'      => __( 'Easyjobs - All Jobs', 'easyjobs' ),
					'menu_title' => __( 'All Jobs', 'easyjobs' ),
					'capability' => 'delete_users',
					'callback'   => array( $this, 'easyjobs_jobs_page' ),
				),
				'easyjobs'                          => array(
					'title'      => __( 'Easyjobs', 'easyjobs' ),
					'menu_title' => __( 'Create Job', 'easyjobs' ),
					'capability' => 'delete_users',
					'callback'   => array( $this, 'easyjobs_create_job' ),
				),
				'easyjobs-candidates'               => array(
					'title'      => __( 'Easyjobs - Candidates', 'easyjobs' ),
					'menu_title' => __( 'Candidates', 'easyjobs' ),
					'capability' => 'delete_users',
					'callback'   => array( $this, 'easyjobs_candidates_page' ),
				),
				'admin.php?page=easyjobs#/settings' => array(
					'title'      => __( 'Easyjobs - Settings', 'easyjobs' ),
					'menu_title' => __( 'Settings', 'easyjobs' ),
					'capability' => 'delete_users',
					'callback'   => null,
				),
            )
        );
        // register submenu pages
        foreach ( $submenu_pages as $slug => $page ) {
            add_submenu_page(
                $this->slug,
                $page['title'],
                $page['menu_title'],
                $page['capability'],
                isset( $page['slug'] ) ? $page['slug'] : $slug,
                $page['callback']
            );
        }

    }

    /**
     * Render main admin page in easyjobs based on view
     *
     * @since 1.0.0
     * @return void
     */
    public function easyjobs_main_page() {
         // give a warning if api key is not set
        if ( empty( $this->settings->easyjobs_api_key ) ) {
            $this->render_landing();
            return;
        }
        // Render page navigation for sub pages
        if ( isset( $_GET['job-id'] ) && isset( $_GET['view'] ) ) {
            $job_id = sanitize_text_field( wp_unslash( $_GET['job-id'] ) );
            switch ( sanitize_text_field( $_GET['view'] ) ) {
                case 'pipeline':
                    $this->pipeline->show( $job_id );
                    break;
                case 'candidates':
                    $this->candidates->show_job_candidates( $job_id );
                    break;
            }
            return;
        }
        if ( isset( $_GET['easyjobs-search'] ) && isset( $_GET['result-type'] ) ) {
            $this->jobs->show_search_results();
            return;
        }
        if ( isset( $_GET['candidate-id'] ) ) {
            $this->candidates->show_details( intval( sanitize_text_field( $_GET['candidate-id'] ) ) );
            return;
        }
        if ( isset( $_GET['recent-jobs-page'] ) ) {
            $this->dashboard->show_dashboard( sanitize_text_field( $_GET['recent-jobs-page'] ) );
            return;
        }
        $this->dashboard->show_dashboard( 1 );
    }

    /**
     * Render add new job page
     *
     * @since 1.0.0
     * @return void
     */
    public function add_new_job_page() {
         include EASYJOBS_ADMIN_DIR_PATH . 'partials/easyjobs-add-new.php';
    }

    /**
     * Render inner page title
     *
     * @since 1.0.0
     * @param $admin_title
     * @param $title
     * @return string
     */
    public function render_inner_page_title( $admin_title, $title ) {
        if ( isset( $_GET['job-id'] ) && isset( $_GET['view'] ) ) {
            $suffix = str_replace( 'Easyjobs', '', $admin_title );
            switch ( sanitize_text_field( $_GET['view'] ) ) {
                case 'pipeline':
                    $title = __( 'Easyjobs Pipelines ', 'easyjobs' ) . $suffix;
                    break;
                case 'candidates':
                    $title = __( 'Easyjobs Candidates ', 'easyjobs' ) . $suffix;
                    break;
                default:
                    $title = $admin_title;
                    break;
            }
            return $title;
        }
        return $admin_title;
    }

    /**
     * Render warning for no api key
     *
     * @return void
     */
    public function render_warning() {
         echo '<h3>Connect with API </h3><p>Set your <a href="' . esc_url( admin_url( 'admin.php?page=easyjobs-settings' ) ) . '">EasyJobs API Key</a>  to connect.</p>';
    }

    /**
     * Ajax callback for 'easyjobs_connect_api'
     * Check if given api key is correct
     *
     * @return void
     */
    public function connect_api() {
        if ( ( ! isset( $_POST['nonce'] ) && ! isset( $_POST['key'] ) ) || !
            wp_verify_nonce( $_POST['nonce'], 'easyjobs_' . sanitize_text_field( $_POST['key'] ) . '_nonce' ) ) {
            return;
        }

        if ( empty( $_POST['form_data'] ) ) {
            return;
        }
        $api_key = '';

        foreach ( $_POST['form_data'] as $form_data ) {
            if ( sanitize_text_field( $form_data['name'] ) == $this->api_key_field ) {
                $api_key = sanitize_key( $form_data['value'] );
                break;
            }
        }

        $is_authenticated = Easyjobs_Api::authenticate( $api_key );

        if ( $is_authenticated ) {
            EasyJobs_Settings::save_settings( $_POST['form_data'] );
            $this->connect_analytics();
            echo 'success';
        } else {
            echo 'error';
        }
        wp_die();
    }

    /**
     *
     */
    public function render_landing() {
         $company_create_view = null;
        $company_metadata     = null;
        $login_view           = null;
        $landing_view         = isset( $_GET['landing_view'] ) ? trim( sanitize_text_field( $_GET['landing_view'] ) ) : '';
        if ( $landing_view == 'create_company' ) {
            $company_create_view = true;
            $metadata            = Easyjobs_Api::get( 'company_metadata' );
            if ( $metadata->status == 'success' ) {
                $company_metadata = $metadata->data;
            }
		}
        if ( $landing_view == 'login' ) {
            $user_meta  = get_user_meta( get_current_user_id() );
            $user_data  = array(
                'first_name' => $user_meta['first_name'][0],
                'last_name'  => $user_meta['last_name'][0],
                'email'      => wp_get_current_user()->user_email,
            );
            $login_view = true;

        }
        include EASYJOBS_ADMIN_DIR_PATH . 'partials/easyjobs-admin-landing.php';
    }

    /**
     * Ajax callback for 'easyjobs_disconnect_api'
     * Disconnect api key
     *
     * @since 1.0.5
     * @return void
     */
    public function disconnect_api() {
		if ( ( ! isset( $_POST['nonce'] ) && ! isset( $_POST['key'] ) ) || !
            wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'easyjobs_' . sanitize_text_field( $_POST['key'] ) . '_nonce' ) ) {
            return;
		}
		if ( Easyjobs_Helper::disconnect_plugin() ) {
			echo 'success';
		} else {
			echo 'error';
		}

        wp_die();
    }

    /**
     * Easyjobs Dashboard page
     *
     * @since 1.0.5
     * @return void
     */

    public function easyjobs_jobs_page() {
		if ( empty( $this->settings->easyjobs_api_key ) ) {
            $this->render_landing();
            return;
		}

        $this->jobs->show_all();
    }
    /**
     * Easyjobs All candidates page
     *
     * @since 1.1.1
     * @return void
     */

    public function easyjobs_candidates_page() {
		if ( empty( $this->settings->easyjobs_api_key ) ) {
            $this->render_landing();
            return;
		}
        $this->candidates->show_all_candidates( array( 'page' => 1 ) );
    }

    /**
     * Callback for easyjobs_signin ajax request
     * handles user signin with credentials
     *
     * @since 1.1.2
     * @return void
     */
    public function sign_in() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'easyjobs_signin_nonce' ) || ! isset( $_POST['form_data'] ) ) {
            echo wp_json_encode(
                array(
					'status'     => 'error',
					'error_type' => 'invalid_nonce',
					'message'    => 'Bad request !!',
                )
			);
			wp_die();
		}
        $credentials = array();
		foreach ( $_POST['form_data'] as $data ) {
			$credentials[ sanitize_text_field( $data['name'] ) ] = sanitize_text_field( $data['value'] );
		}

        $response = Easyjobs_Api::post( 'sign_in', null, $credentials, false );
		if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
			$response_array = array( 'status' => 'success' );
			if ( ! empty( $response->data ) ) {
				if ( ! empty( $response->data->companies ) ) {
					if ( count( $response->data->companies ) > 1 ) {
						$response_array['action'] = 'select_company';
						$companies                = array();
						foreach ( $response->data->companies as $company ) {
							$companies[] = array(
								'name'  => $company->name,
								'value' => $company->app_keys[ count( $company->app_keys ) - 1 ]->app_key,
							);
						}
						$response_array['companies'] = $companies;
					} else {
						$api_key = end( end( $response->data->companies )->app_keys )->app_key;
						EasyJobs_Settings::save_settings(
                            array(
								array(
									'name'  => $this->api_key_field,
									'value' => $api_key,
								),
                            )
						);
						$this->connect_analytics();
						$response_array['action'] = 'reload';
					}
				} else {
					$user_key = 'ej-' . sha1( time() );
					set_transient( $user_key, serialize( $credentials ) );
					$response_array['user_key'] = $user_key;
					$response_array['action']   = 'create_company';
				}
			}
			echo wp_json_encode( $response_array );

		} else {
			$response_array = array(
				'status' => 'error',
			);
			if ( ! empty( $response->message ) ) {
				$response_array['errors'] = $this->format_api_error_response( $response->message );
			}
			echo wp_json_encode( $response_array );
		}

        wp_die();
    }

    /**
     * Callback for easyjobs_save_company ajax request
     * handles signed in users company select
     *
     * @since 1.1.2
     * @return void
     */

    public function save_company() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'easyjobs_company_select_nonce' ) || ! isset( $_POST['company'] ) ) {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Bad request !!',
                )
			);
			wp_die();
		}
        $api_key = sanitize_text_field( $_POST['company'] );

        $is_authenticated = Easyjobs_Api::authenticate( $api_key );

		if ( $is_authenticated ) {
			EasyJobs_Settings::save_settings(
                array(
					array(
						'name'  => $this->api_key_field,
						'value' => $api_key,
					),
                )
            );
			$this->connect_analytics();

			echo 'success';
		} else {
			echo 'error';
		}
        wp_die();
    }

    /**
     * Callback for easyjobs_signup ajax request
     * handles sign up process of users
     *
     * @since 1.1.2
     * @return void
     */

    public function sign_up() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'easyjobs_signup_nonce' ) || ! isset( $_POST['form_data'] ) ) {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Bad request !!',
                )
			);
			wp_die();
		}
        $data = array();

        $password        = null;
        $passwordConfirm = null;

		foreach ( $_POST['form_data'] as $formData ) {
			if ( sanitize_text_field( $formData['name'] ) === 'password' ) {
				$password = sanitize_key( $formData['value'] );
			}
			if ( sanitize_text_field( $formData['name'] ) === 'password_confirm' ) {
				$passwordConfirm = sanitize_key( $formData['value'] );
				continue;
			}
			$data[ sanitize_text_field( $formData['name'] ) ] = sanitize_text_field( $formData['value'] );
		}

		if ( ! empty( $password ) && ! empty( $passwordConfirm ) && $password !== $passwordConfirm ) {
			echo json_encode(
                array(
					'status' => 'error',
					'errors' => array(
						'password_confirm' => 'Password confirm dose not match',
					),
                )
            );
			wp_die();
		}

        $response = Easyjobs_Api::post( 'sign_up', null, $data, false );

		if ( $response->status === 'success' ) {
			$user_key = 'ej-' . sha1( time() );
			set_transient( $user_key, serialize( $data ) );
			echo wp_json_encode(
                array(
					'status'   => 'success',
					'user_key' => $user_key,
                )
            );
		} else {
			$response_array = array( 'status' => 'error' );
			if ( ! empty( $response->message ) ) {
				$response_array['errors'] = $this->format_api_error_response( $response->message );
			}
			echo wp_json_encode( $response_array );
		}

        wp_die();

    }

    /**
     * Callback for easyjobs_create_company ajax request
     * handles create company for users if no company is found
     *
     * @since 1.1.2
     * @return void
     */

    public function create_company() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'easyjobs_create_company_nonce' ) || ! isset( $_POST['form_data'] ) || ! isset( $_POST['user_key'] ) ) {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Bad request !!',
                )
			);
			wp_die();
		}
        $data = array();
		foreach ( $_POST['form_data'] as $formData ) {
			if ( is_array( $formData['value'] ) ) {
				$data[ sanitize_text_field( $formData['name'] ) ] = $formData['value'];
			} else {
				$data[ sanitize_text_field( $formData['name'] ) ] = sanitize_text_field( $formData['value'] );
			}
		}
        $user = get_transient( sanitize_text_field( $_POST['user_key'] ) );
		if ( ! empty( $user ) ) {
			$user     = unserialize( $user );
			$response = Easyjobs_Api::post_with_basic_auth(
                'create_company',
                $data,
                array(
					'email'    => $user['email'],
					'password' => $user['password'],
                )
			);
			if ( $response->status === 'success' ) {
				$api_key = end( end( $response->data->companies )->app_keys )->app_key;
				EasyJobs_Settings::save_settings(
					array(
						array(
							'name'  => $this->api_key_field,
							'value' => $api_key,
						),
					)
                );
				$this->connect_analytics();
				delete_transient( sanitize_text_field( $_POST['user_key'] ) );
				echo wp_json_encode(
					array(
						'status'  => 'success',
						'message' => 'Company created',
                    )
                );
			} else {
				echo wp_json_encode(
                    array(
						'status' => 'error',
						'errors' => ! empty( $response->message ) ? $this->format_api_error_response( $response->message ) : array( 'global' => 'Something went wrong, please try again' ),
                    )
				);
			}
		}
        wp_die();
    }

    /**
     *
     */
    public function easyjobs_create_job() {
		if ( empty( $this->settings->easyjobs_api_key ) ) {
            $this->render_landing();
            return;
		}
        $this->jobs->create_job();
    }

    /**
     * Get data
     */
    public function get_data() {
		if ( ! isset( $_POST['type'] ) ) {
            echo wp_json_encode( Easyjobs_Helper::get_error_response( 'Query data type not provided' ) );
		}
        $params = array();
		if ( ! empty( $_POST['params'] ) ) {
			$params = (array) json_decode( stripslashes( $_POST['params'] ) );
		}
        echo wp_json_encode( Easyjobs_Helper::get_generic_response( Easyjobs_Api::get( sanitize_text_field( $_POST['type'] ), $params ) ) );
        wp_die();
    }

    /**
     * Get all countries
     */
    public function get_states() {
		if ( ! isset( $_POST['country'] ) ) {
            echo json_encode(
                array(
					'status'  => 'error',
					'message' => 'Please provide a country',
                )
			);
			wp_die();
		}
        $states = Easyjobs_Api::get_by_id( 'states', sanitize_text_field( $_POST['country'] ) );
		if ( Easyjobs_Helper::is_success_response( $states->status ) ) {
			echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $states->data,
                )
            );
		} else {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $states->message,
                )
			);
		}
        wp_die();
    }

    /**
     * Get all cities
     */
    public function get_cities() {
		if ( ! isset( $_POST['country'] ) ) {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Please provide a country',
                )
			);
			wp_die();
		}
		if ( ! isset( $_POST['state'] ) ) {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Please provide a state',
                )
            );
			wp_die();
		}
        $url = EASYJOBS_APP_URL . '/api/v1/city/' . sanitize_text_field( $_POST['country'] ) . '/' . sanitize_text_field( $_POST['state'] );

        $cities = Easyjobs_Api::get_custom( $url );

		if ( Easyjobs_Helper::is_success_response( $cities->status ) ) {
			echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $cities->data,
                )
            );
		} else {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $cities->message,
                )
			);
		}
        wp_die();
    }

    /**
     * Get company verification status
     */
    public function get_verification_status() {
		if ( $status = get_transient( 'easyjobs_company_verification_status' ) ) {
            echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => array(
						'is_verified' => $status == 'yes',
					),
                )
			);
			wp_die();
		}
        $response = Easyjobs_Api::get( 'settings_basic_info' );
		if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
			set_transient( 'easyjobs_company_verification_status', $response->data->is_verified ? 'yes' : 'no', 3600 );
			echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => array(
						'is_verified' => $response->data->is_verified,
					),
                )
            );
		} else {
			echo wp_json_encode( Easyjobs_Helper::get_error_response( 'Unable to get verification status' ) );
		}
        wp_die();
    }

    /**
     * Set company verification status
     */
    public function set_verification_status() {
		if ( ! get_transient( 'easyjobs_company_verification_status' ) ) {
            $settings = EasyJobs_DB::get_settings();
            if ( ! empty( $settings['easyjobs_api_key'] ) ) {
                $response = Easyjobs_Api::get( 'settings_basic_info' );
                if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
                    set_transient( 'easyjobs_company_verification_status', $response->data->is_verified ? 'yes' : 'no', 3600 );
                }
            }
		}
    }

    /**
     * Post simple data to api
     *
     * @since 1.3.0
     * @return void
     */
    public function post_data() {
		if ( ! isset( $_POST['type'] ) ) {
            echo wp_json_encode( Easyjobs_Helper::get_error_response( 'Data type not provided' ) );
		}

        $id = null;

		if ( isset( $_POST['id'] ) ) {
			$id = abs( $_POST['id'] );
		}
        $data = array();
		if ( ! is_array( $_POST['data'] ) ) {
			foreach ( (array) json_decode( stripslashes( $_POST['data'] ) ) as $key => $value ) {
				if ( $key == 'id' ) {
					$data['id'] = $value;
					continue;
				}
				$data[ $key ] = sanitize_text_field( $value );
			}
		} else {
			foreach ( $_POST['data'] as $key => $value ) {
				if ( $key == 'id' ) {
					$data['id'] = $value;
					continue;
				}
				$data[ $key ] = sanitize_text_field( $value );
			}
		}
        echo wp_json_encode(
            Easyjobs_Helper::get_generic_response(
                Easyjobs_Api::post(
                    sanitize_text_field( $_POST['type'] ),
                    $id,
                    $data
                )
            )
        );
        wp_die();
    }

    /**
     * Redirect to main page after activate plugin
     *
     * @since 1.3.3
     * @return mixed
     */
    public function maybe_redirect() {
		if ( ! get_transient( 'easyjobs_activation_redirect' ) ) {
            return;
		}
		if ( wp_doing_ajax() ) {
			return;
		}

        delete_transient( 'easyjobs_activation_redirect' );

		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

        // Safe Redirect to easyjobs main Page
        wp_safe_redirect( admin_url( 'admin.php?page=easyjobs-admin' ) );
        exit;
    }

    public function sync_data() {
		if ( ! isset( $_POST['nonce'] ) && !
            wp_verify_nonce( $_POST['nonce'], 'easyjobs_sync' ) ) {
            echo wp_json_encode( Easyjobs_Helper::get_error_response( 'Invalid request' ) );
            wp_die();
		}
        $settings = EasyJobs_DB::get_settings();
        $updated  = false;
		if ( ! empty( $settings['easyjobs_api_key'] ) ) {
			$company_info = Easyjobs_Api::get( 'company_info' );
			if ( Easyjobs_Helper::is_success_response( $company_info->status ) ) {
				$updated = update_option( 'easyjobs_company_info', serialize( $company_info->data ) );
			}
			$updated = $this->connect_analytics();
			if ( $updated ) {
				echo wp_json_encode( Easyjobs_Helper::get_success_response( 'Data synced successfully' ) );
			} else {
				echo wp_json_encode( Easyjobs_Helper::get_error_response( 'Data synced failed' ) );
			}
		} else {
			echo wp_json_encode( Easyjobs_Helper::get_error_response( 'Api not connected' ) );
		}

        wp_die();
    }

    /**
     * Format api error response to display in frontend
     *
     * @param object $error_messages
     * @since 1.1.2
     * @return array
     */

    private function format_api_error_response( $error_messages ) {
        $errors = array();
        foreach ( $error_messages as $key => $message ) {
            if ( is_array( $message ) ) {
                foreach ( $message as $k => $m ) {
                    if ( $k > 1 ) {
                        $errors[ $key ] .= ' and ';
                    }
                    $errors[ $key ] .= $m;

                }
                continue;
            } else {
                $errors['global'] = ' ' . $message;
            }
        }

        return $errors;
    }

    /**
     * This function allows you to track usage of your plugin
     * Place in your main plugin file
     * Refer to support@easy.jobs for help
     */
    private function easyjobs_start_plugin_tracking() {
        if ( ! class_exists( 'WPInsights_Easyjobs' ) ) {
            require_once dirname( __FILE__ ) . '/includes/class-easyjobs-plugin-tracker.php';
        }
        $tracker = WPInsights_Easyjobs::get_instance(
            EASYJOBS_FILE,
            array(
				'opt_in'       => true,
				'goodbye_form' => true,
				'item_id'      => '3afabc1d2d2310978396',
			)
        );
        $tracker->set_notice_options(
            array(
				'notice'       => __( 'Want to help make <strong>EasyJobs</strong> even more awesome?', 'easyjobs' ),
				'extra_notice' => __( 'We collect non-sensitive diagnostic data and plugin usage information. Your site URL, WordPress & PHP version, plugins & themes and email address to send you the  discount coupon. This data lets us make sure this plugin always stays compatible with the most popular plugins and themes. No spam, I promise.', 'easyjobs' ),
            )
        );
        $tracker->init();
    }
    public function admin_notice() {

        if ( ! class_exists( 'WPDeveloper_Notice' ) ) {
            require_once dirname( __FILE__ ) . '/includes/class-easyjobs-notice.php';
        }
        $notice = new WPDeveloper_Notice( EASYJOBS_BASENAME, EASYJOBS_VERSION );
        /**
         * Current Notice End Time.
         * Notice will dismiss in 3 days if user does nothing.
         */
        $notice->cne_time = '3 Day';
        /**
         * Current Notice Maybe Later Time.
         * Notice will show again in 7 days
         */
        $notice->maybe_later_time = '21 Day';

        $scheme        = ( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) ? '&' : '?';
        $url           = $_SERVER['REQUEST_URI'] . $scheme;
        $notice->links = array(
            'review' => array(
                'later'            => array(
                    'link'       => 'https://wpdeveloper.net/review-easyjobs',
                    'target'     => '_blank',
                    'label'      => __( 'Ok, you deserve it!', 'easyjobs' ),
                    'icon_class' => 'dashicons dashicons-external',
                ),
                'allready'         => array(
                    'link'       => $url,
                    'label'      => __( 'I already did', 'easyjobs' ),
                    'icon_class' => 'dashicons dashicons-smiley',
                    'data_args'  => array(
                        'dismiss' => true,
                    ),
                ),
                'maybe_later'      => array(
                    'link'       => $url,
                    'label'      => __( 'Maybe Later', 'easyjobs' ),
                    'icon_class' => 'dashicons dashicons-calendar-alt',
                    'data_args'  => array(
                        'later' => true,
                    ),
                ),
                'support'          => array(
                    'link'       => 'https://wpdeveloper.net/support',
                    'label'      => __( 'I need help', 'easyjobs' ),
                    'icon_class' => 'dashicons dashicons-sos',
                ),
                'never_show_again' => array(
                    'link'       => $url,
                    'label'      => __( 'Never show again', 'easyjobs' ),
                    'icon_class' => 'dashicons dashicons-dismiss',
                    'data_args'  => array(
                        'dismiss' => true,
                    ),
                ),
            ),
        );

        /**
         * This is review message and thumbnail.
         */
        $notice->message( 'review', '<p>' . __( 'We hope you\'re enjoying EasyJobs! Could you please do us a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'easyjobs' ) . '</p>' );
        $notice->thumbnail( 'review', EASYJOBS_ADMIN_URL . 'assets/img/easyjobs-icon-blue.svg', EASYJOBS_BASENAME );

        $notice->options_args = array(
            'notice_will_show' => array(
                // 'opt_in' => $notice->timestamp, // after 3 days
                'review' => $notice->makeTime( $notice->timestamp, '7 Day' ), // after 3 days
            ),
        );

        $notice->init();
    }

    private function connect_analytics() {
        $analytics_connected = Easyjobs_Api::post(
            'analytics_url',
            null,
            array(
				'url' => get_bloginfo( 'url' ),
            )
		);
        if ( Easyjobs_Helper::is_success_response( $analytics_connected->status ) ) {
            update_option( 'easyjobs_analytics_connected', 1 );
            return true;
        }
        return false;
    }

    private function update_company_data() {
		if ( empty( $this->settings->easyjobs_api_key ) ) {
            return;
		}
        $company_info = get_option( 'easyjobs_company_info' );
		if ( ! empty( $company_info ) ) {
			$company_info = unserialize( $company_info );
			if ( isset( $company_info->subscription_expired ) ) {
				return;
			}
		}
        $updated_info = Easyjobs_Api::get( 'company_info' );
		if ( Easyjobs_Helper::is_success_response( $updated_info->status ) ) {
			update_option( 'easyjobs_company_info', serialize( $updated_info->data ) );
		}
    }

	/**
	 * Check if current page is a easyjobs plugin page
	 * @return bool
	 */
	private function isEasyJobsPage()
	{
		$allowed_pages = [
			'easyjobs-admin',
			'easyjobs',
			'easyjobs-all-jobs',
			'easyjobs-candidates',
			'easyjobs',
		];
		global $pagenow;
		if($pagenow == 'admin.php') {
			$page = isset($_GET['page']) ? $_GET['page'] : '';
			if(in_array($page, $allowed_pages)) {
				return true;
			}
		}
		return false;
	}
}
