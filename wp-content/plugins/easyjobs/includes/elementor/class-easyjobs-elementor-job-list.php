<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Elementor\Controls_Manager;
use \Elementor\Icons_Manager;
use \Elementor\Widget_Base;
use \Elementor\Plugin;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Border as Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow as Group_Control_Box_Shadow;
use \Elementor\Group_Control_Typography as Group_Control_Typography;

class Easyjobs_Elementor_Job_List extends Widget_Base {
	use Easyjobs_Elementor_Template;

	protected $is_editor;

	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
		$this->is_editor = Plugin::$instance->editor->is_edit_mode();
	}

	public function get_name() {
		return 'easyjobs-job-list';
	}

	public function get_title() {
		return esc_html__( 'Easyjobs Job List', 'easyjobs' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return array( 'easyjobs' );
	}

	public function get_keywords() {
		return array(
			'easyjobs',
			'jobs',
		);
	}

	public function get_custom_help_url() {
		return 'https://easy.jobs/docs/';
	}


	/**
	 * Get published job from api
	 *
	 * @param  array $arg
	 *
	 * @return object|false
	 * @since 1.0.0
	 */
	private function get_published_jobs( $arg = array() ) {

		$query_param = wp_parse_args(
            $arg,
            array(
				'limit'   => 10,
				'orderby' => 'id:desc',
			)
        );
		if ( ! $this->is_editor ) {
			$job_info = Easyjobs_Api::get( 'published_jobs', $query_param );

			return $job_info->status == 'success' ? $job_info->data : array();
		}

		// cache only editor mode
		$arg = array(
			'key'     => $this->get_token(),
			'limit'   => $query_param['limit'],
			'orderby' => $query_param['orderby'],
		);

		$key  = 'elej_job_' . md5( implode( '', $arg ) );
		$jobs = get_transient( $key );
		if ( empty( $jobs ) ) {
			$job_info = Easyjobs_Api::get( 'published_jobs', $query_param );
			if ( $job_info->status === 'success' ) {
				$jobs = $job_info->data;
				set_transient( $key, $jobs, 0.5 * HOUR_IN_SECONDS );
			}
		}

		return $jobs;
	}

	private function get_token() {
		$settings = EasyJobs_DB::get_settings();

		return ! empty( $settings['easyjobs_api_key'] ) ? $settings['easyjobs_api_key'] : false;
	}


	public function check_token() {

		$this->start_controls_section(
			'easyjobs_api_warning',
			array(
				'label' => __( 'Warning!', 'easyjobs' ),
			)
		);

		$this->add_control(
			'easyjobs_api_warning_text',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __('Please set your API key on the ','easyjobs') .
                    '<strong style="color: #d30c5c"><a href="' . admin_url( 'admin.php?page=easyjobs-settings#general' ) . '" target="_blank">'.__('EasyJobs','easyjobs').'</a> </strong> ' . __('settings page.', 'easyjobs'),
                'content_classes' => 'elej-warning',
			)
		);

		$this->end_controls_section();

	}

	protected function _register_controls() {

		if ( ! $this->get_token() ) {
			$this->check_token();

			return;
		}

		// content tab
		$this->content_job_list_general();
		$this->content_job_list_control();

		// style tab
		$this->style_general_controls();
		$this->style_section_controls();
		$this->style_job_list_control();
	}

	public function content_job_list_general() {
		$this->start_controls_section(
			'section_easyjobs_info_box',
			array(
				'label' => __( 'General', 'easyjobs' ),
			)
		);

		$this->add_control(
			'easyjobs_job_list_title_control',
			array(
				'label'        => __( 'Hide Title', 'easyjobs' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'easyjobs' ),
				'label_off'    => __( 'No', 'easyjobs' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'easyjobs_job_list_title',
			array(
				'label'       => __( 'Title', 'easyjobs' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Open Job Positions', 'easyjobs' ),
				'placeholder' => __( 'Type your title here', 'easyjobs' ),
				'condition'   => array(
					'easyjobs_job_list_title_control!' => 'yes',
				),
			)
		);

		$this->add_control(
			'easyjobs_job_list_heading_icon_control',
			array(
				'label'        => __( 'Hide Icon', 'easyjobs' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'easyjobs' ),
				'label_off'    => __( 'No', 'easyjobs' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'easyjobs_job_list_heading_icon',
			array(
				'label'     => __( 'Icon', 'easyjobs' ),
				'type'      => Controls_Manager::ICONS,
				'condition' => array(
					'easyjobs_job_list_heading_icon_control!' => 'yes',
				),
			)
		);

		$this->add_control(
			'easyjobs_joblist_apply_button_text',
			array(
				'label'       => __( 'Apply Button Text', 'easyjobs' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Apply', 'easyjobs' ),
				'placeholder' => __( 'Apply', 'easyjobs' ),
			)
		);

		$this->end_controls_section();
	}

	public function content_job_list_control() {
		$this->start_controls_section(
			'easyjobs_job_list_query',
			array(
				'label' => __( 'Job List', 'easyjobs' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'easyjobs_job_list_order_by',
			array(
				'label'   => __( 'Order BY', 'easyjobs' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'id',
				'options' => array(
					'id'           => __( 'ID', 'easyjobs' ),
					'title'        => __( 'Title', 'easyjobs' ),
					'vacancies'    => __( 'Vacancies', 'easyjobs' ),
					'salary'       => __( 'Salary', 'easyjobs' ),
					'published_at' => __( 'Published Date', 'easyjobs' ),
					'created_at'   => __( 'Created Date', 'easyjobs' ),
					'updated_at'   => __( 'Updated Date', 'easyjobs' ),
				),
			)
		);

		$this->add_control(
			'easyjobs_job_list_sort_by',
			array(
				'label'   => __( 'Sort BY', 'easyjobs' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'asc',
				'options' => array(
					'asc'  => __( 'ASC', 'easyjobs' ),
					'desc' => __( 'DESC', 'easyjobs' ),
				),
			)
		);

		$this->add_control(
			'easyjobs_jobs_per_page',
			array(
				'label'   => esc_html__( 'Show Jobs', 'easyjobs' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					),
				),
				'default' => array(
					'unit' => 'px',
					'size' => 2,
				),
            )
        );

		$this->add_control(
			'easyjobs_show_open_job',
			array(
				'label'        => __( 'Show Open Job Only', 'easyjobs' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'easyjobs' ),
				'label_off'    => __( 'No', 'easyjobs' ),
				'return_value' => 'yes',
				'default'      => 'yes',

			)
		);

		$this->end_controls_section();
	}

	/**
	 * It prints controls for managing general style of Easyjobs landing page
	 */
	public function style_general_controls() {
		$this->start_controls_section(
            'section_style_general',
            array(
				'label' => __( 'General', 'easyjobs' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
        );

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'easyjobs_job_list_section_background',
				'label'    => __( 'Background', 'easyjobs' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .easyjobs-elementor,{{WRAPPER}} .easyjobs-elementor  .ej-job-list-item',
			)
		);

		$this->add_responsive_control(
			'easyjobs_job_list_section_alignment',
			array(
				'label'        => esc_html__( 'Alignment', 'easyjobs' ),
				'type'         => Controls_Manager::CHOOSE,
				'label_block'  => true,
				'options'      => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'easyjobs' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'easyjobs' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'easyjobs' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'prefix_class' => 'ej-landingpage-alignment-',
				'default'      => 'center',
			)
		);

		$this->add_responsive_control(
			'easyjobs_job_list_section_width',
			array(
				'label'      => esc_html__( 'Width', 'easyjobs' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
					'%',
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1300,
						'step' => 5,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors'  => array(
					'{{WRAPPER}} .easyjobs-elementor' => 'width: {{SIZE}}{{UNIT}};',
				),
            )
        );

		$this->add_control(
			'easyjobs_job_list_section_margin',
			array(
				'label'      => __( 'Margin', 'easyjobs' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array(
					'px',
					'em',
					'%',
				),
				'selectors'  => array(
					'{{WRAPPER}} .easyjobs-elementor' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
            )
        );

		$this->add_control(
			'easyjobs_job_list_section__padding',
			array(
				'label'      => __( 'Form Padding', 'easyjobs' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array(
					'px',
					'em',
					'%',
				),
				'selectors'  => array(
					'{{WRAPPER}} .easyjobs-elementor' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
            )
        );

		$this->add_control(
			'easyjobs_job_list_border_radius',
			array(
				'label'      => __( 'Border Radius', 'easyjobs' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .easyjobs-elementor' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'easyjobs_job_list_section_boxshadow',
				'selector' => '{{WRAPPER}} .easyjobs-elementor',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * It prints controls for managing section heading
	 */
	public function style_section_controls() {
		$this->start_controls_section(
            'style_easyjobs_section',
            array(
				'label' => __( 'Section', 'easyjobs' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
        );

		$this->add_control(
			'easyjobs_section_heading_margin',
			array(
				'label'      => __( 'Margin', 'easyjobs' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array(
					'px',
					'em',
					'%',
				),
				'selectors'  => array(
					'{{WRAPPER}} .ej-section-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
            )
        );

		$this->add_control(
			'easyjobs_section_heading_padding',
			array(
				'label'      => __( 'Padding', 'easyjobs' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array(
					'px',
					'em',
					'%',
				),
				'selectors'  => array(
					'{{WRAPPER}} .ej-section-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
            )
        );

		$this->add_control(
			'easyjobs_landingpage_section_heading',
			array(
				'label'     => __( 'Section Heading', 'easyjobs' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'easyjobs_section_heading_color',
			array(
				'label'     => esc_html__( 'Color', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ej-section-title .ej-section-title-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'easyjobs_section_heading_typography',
				'label'    => __( 'Typography', 'easyjobs' ),
				'selector' => '{{WRAPPER}} .ej-section-title .ej-section-title-text',
			)
		);

		$this->add_control(
			'easyjobs_job_list_el_section_icon',
			array(
				'label'     => __( 'Section Icon', 'easyjobs' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'easyjobs_job_list_el_section_icon_width',
			array(
				'label'     => __( 'Width', 'easyjobs' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 5,
					),
				),
				'default'   => array(
					'unit' => 'px',
					'size' => 50,
				),
				'selectors' => array(
					'{{WRAPPER}} .ej-section-title-icon' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'easyjobs_job_list_el_section_icon_height',
			array(
				'label'     => __( 'Height', 'easyjobs' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 5,
					),
				),
				'default'   => array(
					'unit' => 'px',
					'size' => 50,
				),
				'selectors' => array(
					'{{WRAPPER}} .ej-section-title-icon' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'easyjobs_section_icon_background',
			array(
				'label'     => esc_html__( 'Background', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ej-section-title span.ej-section-title-icon' => 'background: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'easyjobs_section_icon_color',
			array(
				'label'     => esc_html__( 'Color', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ej-section-title span.ej-section-title-icon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'easyjobs_job_list_el_section_icon_size',
			array(
				'label'     => __( 'Icon Size', 'easyjobs' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 5,
					),
				),
				'default'   => array(
					'unit' => 'px',
					'size' => 30,
				),
				'selectors' => array(
					'{{WRAPPER}} .ej-section-title i' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}


	/**
	 * prints controls for managing Job list box
	 */
	public function style_job_list_control() {

		$this->start_controls_section(
            'section_style_job_list',
            array(
				'label' => __( 'Job List', 'easyjobs' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
        );

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'easyjobs_joblist_background_color',
				'label'    => __( 'Background', 'easyjobs' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item',
			)
		);

		$this->add_control(
			'easyjobs_joblist_bar_color',
			array(
				'label'     => __( 'Separator Color ', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-list-item-inner .ej-job-list-item-col' => 'border-right-color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'easyjobs_joblist_box_padding',
			array(
				'label'      => __( 'Padding', 'easyjobs' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'easyjobs_joblist_box_margin',
			array(
				'label'      => __( 'Margin', 'easyjobs' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'easyjobs_joblist_border',
				'selector' => '{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item',
			)
		);

		$this->add_control(
			'easyjobs_joblist__border_radius',
			array(
				'label'      => __( 'Border Radius', 'easyjobs' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'easyjobs_joblist_box_shadow',
				'selector'  => '{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item',
				'separator' => 'before',
			)
		);

		// Title section
		$this->add_control(
			'easyjobs_joblist_title_section',
			array(
				'label'     => __( 'Job Title', 'easyjobs' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'easyjobs_joblist_title_color',
			array(
				'label'     => __( 'Color', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-title a' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'easyjobs_joblist_title_typography',
				'label'    => __( 'Typography', 'easyjobs' ),
				'selector' => '{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-title a',
			)
		);

		$this->add_responsive_control(
			'easyjobs_joblist_title_space',
			array(
				'label'      => __( 'Space', 'easyjobs' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-title' => 'padding-bottom:{{BOTTOM}}{{UNIT}};',
				),
				'separator'  => 'after',
			)
		);

		// Category section
		$this->add_control(
			'easyjobs_joblist_category_section',
			array(
				'label'     => __( 'Company Name', 'easyjobs' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'easyjobs_joblist_company_name_color',
			array(
				'label'     => __( 'Color', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-list-item-inner .ej-job-list-info-block.ej-job-list-company-name i,{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-list-item-inner .ej-job-list-info-block.ej-job-list-company-name a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'easyjobs_joblist_company_name_typography',
				'label'    => __( 'Typography', 'easyjobs' ),
				'selector' => '{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-list-item-inner .ej-job-list-info-block.ej-job-list-company-name i,{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-list-item-inner .ej-job-list-info-block.ej-job-list-company-name a',
			)
		);

		// Location section
		$this->add_control(
			'easyjobs_joblist_location_section',
			array(
				'label'     => __( 'Job Location', 'easyjobs' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'easyjobs_joblist_location_color',
			array(
				'label'     => __( 'Color', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-list-item-inner .ej-job-list-info-block.ej-job-list-location i,{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-list-item-inner .ej-job-list-info-block.ej-job-list-location span' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'easyjobs_joblist_location_typography',
				'label'    => __( 'Typography', 'easyjobs' ),
				'selector' => '{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-list-item-inner .ej-job-list-info-block.ej-job-list-location i,{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-list-item-inner .ej-job-list-info-block.ej-job-list-location span',
			)
		);

		// Deadline section
		$this->add_control(
			'easyjobs_joblist_deadline_section',
			array(
				'label'     => __( 'Job Deadline', 'easyjobs' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'easyjobs_joblist_deadline_color',
			array(
				'label'     => __( 'Color', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list-item-col .ej-deadline' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'easyjobs_joblist_deadline_typography',
				'label'    => __( 'Typography', 'easyjobs' ),
				'selector' => '{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list-item-col .ej-deadline',
			)
		);

		// vacancies section
		$this->add_control(
			'easyjobs_joblist_vacancies_section',
			array(
				'label'     => __( 'Job Vacancies', 'easyjobs' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'easyjobs_joblist_vacancies_color',
			array(
				'label'     => __( 'Color', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-list-item-inner .ej-job-list-item-col .ej-list-sub' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'easyjobs_joblist_vacancies_typography',
				'label'    => __( 'Typography', 'easyjobs' ),
				'selector' => '{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-list-item-inner .ej-job-list-item-col .ej-list-sub',
			)
		);

		$this->style_job_apply_button();

		$this->end_controls_section();
	}

	/**
	 * prints controls for managing Job list apply button
	 */
	public function style_job_apply_button() {

		$this->add_control(
			'easyjobs_job_apply_button',
			array(
				'label'     => __( 'Job Apply Button', 'easyjobs' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'easyjobs_job_apply_button_typography',
				'label'    => __( 'Typography', 'easyjobs' ),
				'selector' => '{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-apply-btn .ej-info-btn-light',
			)
		);

		$this->add_responsive_control(
			'easyjobs_job_apply_btn_alignment',
			array(
				'label'        => esc_html__( 'Alignment', 'easyjobs' ),
				'type'         => Controls_Manager::CHOOSE,
				'label_block'  => true,
				'options'      => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'easyjobs' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'easyjobs' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'easyjobs' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'prefix_class' => 'ej-job-apply-btn-alignment-',
				'default'      => 'center',
			)
		);

		$this->start_controls_tabs( 'easyjobs_job_apply_tabs_button_style' );

		$this->start_controls_tab(
			'easyjobs_job_apply_tab_button_normal',
			array(
				'label' => __( 'Normal', 'easyjobs' ),
			)
		);

		$this->add_control(
			'easyjobs_job_apply_button_color',
			array(
				'label'     => __( 'Text Color', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-apply-btn .ej-info-btn-light' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'easyjobs_job_apply_background_color',
			array(
				'label'     => __( 'Background Color', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-apply-btn .ej-info-btn-light' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'easyjobs_job_apply_tab_button_hover',
			array(
				'label' => __( 'Hover', 'easyjobs' ),
			)
		);

		$this->add_control(
			'easyjobs_job_apply_button_color_hover',
			array(
				'label'     => __( 'Text Color', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-apply-btn .ej-info-btn-light:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'easyjobs_job_apply_button_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'easyjobs' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-apply-btn .ej-info-btn-light:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'easyjobs_job_apply_button_border',
				'selector'  => '{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-apply-btn .ej-info-btn-light',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'easyjobs_job_apply_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'easyjobs' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-apply-btn .ej-info-btn-light' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'easyjobs_job_apply_button_box_shadow',
				'selector' => '{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-apply-btn .ej-info-btn-light',
			)
		);

		$this->add_responsive_control(
			'easyjobs_job_apply_button_box_padding',
			array(
				'label'      => __( 'Padding', 'easyjobs' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .easyjobs-shortcode-wrapper .ej-job-list .ej-job-list-item .ej-job-apply-btn .ej-info-btn-light' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);
	}


	protected function render() {
		if ( ! $this->get_token() ) {
			printf( "<h2 class='elej-error-msg'>%s</h2>", 'Please add API key' );
			return;
		}
		$settings = $this->get_settings_for_display();
		$this->add_render_attribute(
			'easyjobs-job-list',
			'class',
			array(
				'ej-job-body',
				'easyjobs-elementor',
				'easyjobs-elementor-job-list',
			)
		);
		$icon = '<i class="easyjobs-icon easyjobs-briefcase"></i>';
		if ( ! empty( $settings['easyjobs_job_list_heading_icon']['value'] ) ) {
			ob_start();
			Icons_Manager::render_icon( $settings['easyjobs_job_list_heading_icon'] );
			$icon = ob_get_clean();
		}
		?>

        <div <?php echo $this->get_render_attribute_string( 'easyjobs-job-list' ); ?>>
            <div class="ej-section">
                <div class="ej-section-title">
					<?php if ( $settings['easyjobs_job_list_heading_icon_control'] !== 'yes' ) : ?>
                        <span class="ej-section-title-icon"><?php echo $icon; ?></span>
					<?php endif; ?>
					<?php if ( $settings['easyjobs_job_list_title_control'] !== 'yes' ) : ?>
                        <span class="ej-section-title-text"><?php echo $settings['easyjobs_job_list_title']; ?></span>
					<?php endif; ?>
                </div>
                <div class="ej-section-content">
					<?php $this->job_list_template(); ?>
                </div>
            </div>
        </div>
		<?php
	}
}