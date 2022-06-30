<?php

/**
 * This class is responsible for all job functionality in admin area
 *
 * @since 1.0.0
 */
class Easyjobs_Admin_Jobs {

    public $job_with_page = array();

    /**
     * Easyjobs_Admin_Jobs constructor.
     *
     * @since 1.0.5
     */
    public function __construct() {
		add_action( 'wp_ajax_easyjobs_search_jobs', array( $this, 'show_search_results' ) );
        add_action( 'wp_ajax_easyjobs_get_job_create_meta', array( $this, 'get_job_create_meta' ) );
        add_action( 'wp_ajax_easyjobs_save_job_information', array( $this, 'save_job_information' ) );
        add_action( 'wp_ajax_easyjobs_get_screening_question_meta', array( $this, 'get_screening_question_meta' ) );
        add_action( 'wp_ajax_easyjobs_save_screening_questions', array( $this, 'save_screening_questions' ) );
        add_action( 'wp_ajax_easyjobs_get_quiz_meta', array( $this, 'get_quiz_meta' ) );
        add_action( 'wp_ajax_easyjobs_save_quiz', array( $this, 'save_quiz' ) );
        add_action( 'wp_ajax_easyjobs_change_job_status', array( $this, 'change_job_status' ) );
        add_action( 'wp_ajax_easyjobs_get_job_data', array( $this, 'get_job_data' ) );
        add_action( 'wp_ajax_easyjobs_save_required_fields', array( $this, 'save_required_fields' ) );
        add_action( 'wp_ajax_easyjobs_delete_job', array( $this, 'delete_job' ) );
        add_action( 'wp_ajax_easyjobs_get_initial_job_templates', array( $this, 'get_initial_job_templates' ) );
        add_action( 'wp_ajax_easyjobs_duplicate_job', array( $this, 'duplicate_job' ) );
    }
    /**
     * Show jobs
     *
     * @since 1.0.0
     * @return void
     */

    public function show_all() {
         $jobs = (object) array(
			 'published' => $this->get_published_jobs(),
			 'draft'     => $this->get_draft_jobs(),
			 'archived'  => $this->get_archived_jobs(),
		 );

		 $job_with_page_id       = Easyjobs_Helper::get_job_with_page( $jobs->published );
		 $new_job_with_page_id   = Easyjobs_Helper::create_pages_if_required( $jobs->published, $job_with_page_id );
		 $published_job_page_ids = $job_with_page_id + $new_job_with_page_id;

		 include EASYJOBS_ADMIN_DIR_PATH . 'partials/easyjobs-jobs-display.php';
    }

    /**
     * Get published jobs
     *
     * @since 1.0.0
     * @return object|bool
     */
    public function get_published_jobs() {
         $jobs = Easyjobs_Api::get( 'published_jobs' );
        if ( $jobs && $jobs->status == 'success' ) {
            return $jobs->data;
        }
        return false;
    }

    /**
     * Get draft jobs
     *
     * @since 1.0.0
     * @return object|bool
     */
    public function get_draft_jobs() {
         $jobs = Easyjobs_Api::get( 'draft_jobs' );
        if ( $jobs && $jobs->status == 'success' ) {
            return $jobs->data;
        }
        return false;
    }

    /**
     * Get archived jobs from api
     *
     * @since 1.0.0
     * @return object|bool
     */
    public function get_archived_jobs() {
         $jobs = Easyjobs_Api::get( 'archived_jobs' );
        if ( $jobs && $jobs->status == 'success' ) {
            return $jobs->data;
        }
        return false;
    }

    /**
     * Show search result
     *
     * @since 1.0.0
     */
    public function show_search_results() {
		if ( ! isset( $_POST['keyword'] ) && ! isset( $_POST['type'] ) ) {
            return;
		};
        $keyword        = sanitize_text_field( $_POST['keyword'] );
        $type           = sanitize_text_field( $_POST['type'] );
        $job_page_links = array();
		if ( $type == 'published-jobs' ) {
			$result               = $this->get_search_results( 'published_jobs', $keyword );
			$job_with_page_id     = Easyjobs_Helper::get_job_with_page( $result );
			$new_job_with_page_id = Easyjobs_Helper::create_pages_if_required( $result, $job_with_page_id );
			$job_page_ids         = $job_with_page_id + $new_job_with_page_id;
			foreach ( $result as $r ) {
				$job_page_links[ $r->id ] = get_permalink( $job_page_ids[ $r->id ] );
			}
		}
		if ( $type == 'draft-jobs' ) {
			$result = $this->get_search_results( 'draft_jobs', $keyword );
		}
		if ( $type == 'archived-jobs' ) {
			$result = $this->get_search_results( 'archived_jobs', $keyword );
		}
		if ( ! empty( $result ) ) {
			echo wp_json_encode(
                array(
					'status'         => 'success',
					'jobs'           => $result,
					'job_page_links' => $job_page_links,
                )
            );
			wp_die();
		} else {
			echo wp_json_encode(
                array(
					'status' => 'error',
                )
			);
			wp_die();
		}
    }

    /**
     * Get search result from api
     *
     * @since 1.0.0
     * @param string $type
     * @param string $keyword
     * @return object|bool
     */
    public function get_search_results( $type, $keyword ) {
        $jobs = Easyjobs_Api::search( $type, $keyword );
        if ( $jobs && $jobs->status == 'success' ) {
            return $jobs->data;
        }
        return false;
    }

    public function create_job() {
         wp_enqueue_script( 'easyjobs-react' );
        include EASYJOBS_ADMIN_DIR_PATH . '/partials/easyjobs-react-layout.php';
    }


    public function get_job_create_meta() {
         $metas = Easyjobs_Api::get( 'job_metas' );
        $data   = array();
        if ( Easyjobs_Helper::is_success_response( $metas->status ) ) {
            $data['meta'] = $metas->data;
        }
	    $data['company_info'] = Easyjobs_Helper::get_company_info();
        if ( ! empty( $data ) ) {
            echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $data,
                )
            );
        } else {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Unable to fetch all data required for job create.',
                )
            );
        }

        wp_die();
    }

    public function save_job_information() {
         $fields        = array(
			 'title',
			 'details',
			 'responsibilities',
			 'category',
			 'vacancies',
			 'is_remote',
			 'country',
			 'state',
			 'city',
			 'expire_at',
			 'employment_type',
			 'employment_type_other',
			 'experience_level',
			 'salary_type',
			 'salary',
			 'office_time',
			 'skills',
			 'benefits',
			 'has_benefits',
			 'coverPhoto',
		 );
		 $object_values = array(
			 'category',
			 'country',
			 'state',
			 'city',
			 'skills',
			 'employment_type',
			 'experience_level',
			 'salary_type',
		 );
		 $data          = array();
		 foreach ( $this->sanitize_form_fields( $_POST, $fields ) as $key => $form_field ) {
			 if ( in_array( $key, $object_values ) ) {
				 $data[ $key ] = ! empty( $form_field ) ? json_decode( stripslashes( $form_field ) ) : null;
			 } else {
				 $data[ $key ] = $form_field;
			 }
		 }
		 if ( isset( $_POST['job_id'] ) ) {
			 $response = Easyjobs_Api::post( 'update_job_info', absint( sanitize_text_field($_POST['job_id']) ), $data );
		 } else {
			 $response = Easyjobs_Api::post( 'save_job_info', null, $data );
		 }
		 if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
			 echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $response->data,
                )
			 );
		 } else {
			 echo wp_json_encode(
                array(
					'status' => 'error',
					'error'  => ! empty( $response->message ) ? Easyjobs_Helper::format_api_error_response( $response->message ) : array( 'global' => 'Something went wrong, please try again' ),
                )
			 );
		 }
		 wp_die();
    }

    public function get_screening_question_meta() {
         $meta = Easyjobs_Api::get( 'screening_question_meta' );
        if ( Easyjobs_Helper::is_success_response( $meta->status ) ) {
            echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $meta->data,
                )
            );
        } else {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $meta->message,
                )
            );
        }
        wp_die();
    }

    public function save_screening_questions() {
		if ( ! isset( $_POST['job_id'] ) ) {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Job id not found',
                )
			);
			wp_die();
		}
		if ( ! isset( $_POST['questions'] ) ) {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Questions not found',
                )
            );
			wp_die();
		}
        $questions = json_decode( wp_unslash( $_POST['questions'] ) );
        $job_id    = absint( sanitize_text_field($_POST['job_id']) );
        $sanitized = array();
		foreach ( $questions as $question ) {
			$sanitized[] = $this->sanitize_form_fields( $question, array( 'id', 'title', 'type', 'options', 'answers' ) );
		}
        $response = Easyjobs_Api::post( 'save_questions', $job_id, array( 'questions' => $sanitized ) );

		if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
			echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $response->data,
                )
            );
		} else {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $response->message,
                )
			);
		}

        wp_die();

    }

    public function get_quiz_meta() {
         $meta = Easyjobs_Api::get( 'quiz_meta' );
        if ( $meta->status === 'success' ) {
            echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $meta->data,
                )
            );
        } else {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $meta->message,
                )
            );
        }
        wp_die();
    }

    public function save_quiz() {
		if ( ! isset( $_POST['job_id'] ) ) {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Job id not found',
                )
			);
			wp_die();
		}
		if ( ! isset( $_POST['form_data'] ) ) {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'No data to save',
                )
            );
			wp_die();
		}
        $form_data = json_decode( wp_unslash( $_POST['form_data'] ) );
        $questions = $form_data->questions;
        $job_id    = absint( sanitize_text_field($_POST['job_id']) );
        $sanitized = array();
		foreach ( $questions as $question ) {
			$sanitized[] = $this->sanitize_form_fields( $question, array( 'id', 'title', 'type', 'options', 'answers' ) );
		}

        $response = Easyjobs_Api::post(
            'save_quiz',
            $job_id,
            array(
				'questions'          => $sanitized,
				'exam_duration'      => sanitize_text_field( $form_data->exam_duration ),
				'marks_per_question' => sanitize_text_field( $form_data->marks_per_question ),
			)
        );

        if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
            echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $response->data,
                )
            );
        } else {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $response->message,
                )
            );
        }
        wp_die();

    }

    public function change_job_status() {
		if ( ! isset( $_POST['job_id'] ) ) {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Job id not found',
                )
			);
			wp_die();
		}
		if ( ! isset( $_POST['status'] ) ) {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Status not provided',
                )
            );
			wp_die();
		}
        $company = Easyjobs_Helper::get_company_info();
		if ( ! empty( $company ) && $company->stats->published_jobs >= 1 ) {
			if ( ! $company->is_pro || ( $company->subscription_expired && absint( sanitize_text_field($_POST['status']) ) == 2 ) ) {
				echo wp_json_encode(
                    array(
						'status'  => 'error',
						'message' => 'Your subscription is expired, you can not publish more than one job',
                    )
                );
				wp_die();
			}
		}

        $response = Easyjobs_Api::post(
            'change_status',
            absint( sanitize_text_field($_POST['job_id']) ),
            array( 'status' => absint( sanitize_text_field($_POST['status']) ) )
        );

        if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
            echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $response->data,
                )
            );
        } else {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $response->message,
                )
            );
        }
        wp_die();
    }

    public function get_job_data() {
		if ( ! isset( $_POST['job_id'] ) ) {
            echo wp_json_encode(Easyjobs_Helper::get_error_response( 'Job id not provided' ));
            wp_die();
		}
		if ( ! isset( $_POST['type'] ) ) {
			echo wp_json_encode(Easyjobs_Helper::get_error_response( 'No type provided' ));
			wp_die();
		}

        echo wp_json_encode(Easyjobs_Helper::get_generic_response(
			Easyjobs_Api::get_by_id(
				'job',
				absint( $_POST['job_id'] ),
				sanitize_text_field( $_POST['type'] )
			)
		));

        wp_die();
    }

    public function save_required_fields() {
        if ( ! isset( $_POST['job_id'] ) ) {
            echo wp_json_encode(Easyjobs_Helper::get_error_response( 'Job id not provided' ));
            wp_die();
        }
        if ( ! isset( $_POST['data'] ) ) {
            echo wp_json_encode(Easyjobs_Helper::get_error_response( 'No data provided' ));
            wp_die();
        }
        echo wp_json_encode(Easyjobs_Helper::get_generic_response(
			Easyjobs_Api::post(
				'required_fields',
				absint( sanitize_text_field($_POST['job_id']) ),
				(array) json_decode( wp_unslash( $_POST['data'] ) )
			)
		));

        wp_die();
    }

    public function delete_job() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'easyjobs_delete_job' ) ) {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Nonce not verified',
                )
			);
			wp_die();
		}
		if ( ! isset( $_POST['form_data'] ) && ! isset( $_POST['job_id'] ) ) {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Empty form data or job id',
                )
            );
			wp_die();
		}
        $response = Easyjobs_Api::post( 'delete_job', absint( sanitize_text_field($_POST['job_id']) ), array() );
		if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
			$this->delete_job_page( absint( $_POST['job_id'] ) );
			echo wp_json_encode(Easyjobs_Helper::get_success_response( __( 'Job deleted successfully', 'easyjobs' ) ));
		} else {
			echo wp_json_encode(Easyjobs_Helper::get_error_response( __( 'Failed to delete job, please try again or contact support', 'easyjobs' ) ));
		}

        wp_die();
    }

    public function get_initial_job_templates() {
         $response_data = array();
        if ( isset( $_POST['industry_id'] ) && ! empty( $_POST['industry_id'] ) ) {
			$industry = sanitize_text_field($_POST['industry_id']);
            if ( trim( $industry ) == 'all' ) {
                $industry = '';
            } else {
                $industry = abs( $industry );
            }
        } else {
            $company = Easyjobs_Helper::get_company_info();
            if ( empty( $company->industry ) ) {
                $company_info = Easyjobs_Api::get( 'company_info' );
                if ( Easyjobs_Helper::is_success_response( $company_info->status ) ) {
                    $company = $company_info->data;
                    update_option( 'easyjobs_company_info', serialize( $company ) );
                }
            }
            $industry                 = $company->industry->id;
            $response_data['company'] = $company;
        }
        $initial_templates = Easyjobs_Api::get(
            'job_templates',
            array(
				'industry_id' => $industry,
				'title'       => sanitize_text_field( $_POST['title'] ),
				'page'        => absint( sanitize_text_field($_POST['page']) ),
			)
        );
        if ( Easyjobs_Helper::is_success_response( $initial_templates->status ) ) {
            $response_data['templates'] = $initial_templates->data;
            echo wp_json_encode(Easyjobs_Helper::get_success_response( 'Successfully get templates', $response_data ));
        } else {
            echo wp_json_encode(Easyjobs_Helper::get_error_response( 'Unable to get job templates, please try again' ));
        }
        wp_die();
    }


	public function duplicate_job()
	{
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ej_duplicate_nonce' ) ) {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => 'Invalid request',
				)
			);
			wp_die();
		}
		if ( empty( $_POST['job_id'] )) {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => 'Job not provided',
				)
			);
			wp_die();
		}
		echo wp_json_encode(
			Easyjobs_Helper::get_generic_response(
				Easyjobs_Api::post(
					'job_duplicate',
					sanitize_text_field($_POST['job_id'])
				)
			)
		);
		wp_die();
	}

    private function sanitize_form_fields( $post_data, $fields ) {
        $data          = array();
        $editor_fields = array( 'details', 'responsibilities' );
        foreach ( $post_data as $key => $value ) {
            if ( in_array( $key, $fields ) ) {
                if ( Easyjobs_Helper::is_iterable( $value ) ) {
                    $data[ sanitize_text_field( $key ) ] = $value;
                } else {
                    if ( $key === 'id' ) {
                        if ( ! empty( $value ) ) {
                            $data[ sanitize_text_field( $key ) ] = absint( $value );
                        } else {
                            $data[ sanitize_text_field( $key ) ] = null;
                        }
					} else {
                        if ( in_array( $key, $editor_fields ) ) {
                            $data[ sanitize_text_field( $key ) ] = wp_kses_post( $value );
						} else {
                            $data[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
						}
					}
                }
			}
        }
        return $data;
    }

    private function delete_job_page( $job_id ) {
        $pages = get_posts(
            array(
				'post_type'      => 'page',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => 'easyjobs_job_id',
						'value'   => $job_id,
						'compare' => 'IN',
					),
				),
            )
        );
        foreach ( $pages as $page ) {
            wp_delete_post( $page->ID, true );
        }
        return $pages;
    }
}
