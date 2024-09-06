<?php
/**
 * @file CFQE.php
 *       Contact Form Quick Edit Main class
 */

namespace RMPel\ContactFormQuickEdit;

class CFQE {
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	public function init() {
		// Add the edit links to the admin bar
		add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_links' ], 100 );

		// Filter to gather forms for a post.
		add_filter( 'cfqe_forms_for_post', [ $this, 'get_forms_for_post' ], 10, 2 );
	}

	/**
	 * Add the edit links to the admin bar
	 *
	 * @param \WP_Admin_Bar $admin_bar
	 */
	public function add_admin_bar_links( $admin_bar ) {
		// Get the current post
		$post = get_post();
		if ( ! $post ) {
			return;
		}

		// Get the forms from the page.
		/**
		 * @param array $form_list Must be in structure [ [ 'id' => xxx, 'title' => 'xxx', 'edit_link' => 'xxx' ], ... ]
		 */
		$form_list = [];
		$forms     = apply_filters( 'cfqe_forms_for_post', $form_list, $post->ID );

		if ( empty( $forms ) ) {
			return;
		}

		// Add the forms to the admin bar
		foreach ( $forms as $form ) {
			$admin_bar->add_menu( [
				'id'     => 'edit-cf-' . $form['id'],
				'title'  => sprintf( 'Edit form: %s', $form['title'] ),
				'href'   => $form['edit_link'],
				'meta'   => [
					'target' => '_blank',
				],
				'parent' => 'edit',
			] );
		}
	}

	/**
	 * Add a column "Forms" to the post overview with links to edit the forms in new tabs
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_post_columns( $columns ) {
		$columns['cfqe_forms'] = 'Forms';

		return $columns;
	}

	/**
	 * Add the content to the "Forms" column
	 *
	 * @param string $column_name
	 * @param int    $post_id
	 */
	public function add_post_column_content( $column_name, $post_id ) {
		if ( 'cfqe_forms' !== $column_name ) {
			return;
		}

		// Get the forms from the page.
		/**
		 * @param array $form_list Must be in structure [ [ 'id' => xxx, 'title' => 'xxx', 'edit_link' => 'xxx' ], ... ]
		 */
		$form_list = [];
		$forms     = apply_filters( 'cfqe_forms_for_post', $form_list, $post_id );

		// Output the forms
		foreach ( $forms as $form ) {
			printf( '<a href="%s" target="_blank">%s</a><br>', $form['edit_link'], $form['title'] );
		}
	}

	/**
	 * Get the forms for a post
	 *
	 * @param array $form_list
	 * @param int   $post_id
	 *
	 * @return array
	 */
	public function get_forms_for_post( $forms, $post_id ) {
		// Brute force - get all forms from the post content and all meta fields.
		$post = get_post( $post_id );

		if ( $post ) {
			$data_dump = $post->post_content;
			$meta      = get_post_meta( $post_id );
			$data_dump = $data_dump . var_export( $meta, true );
			$forms     = $this->get_forms_from_content( $data_dump );
		}

		return $forms;
	}

	/**
	 * Get the forms from the post content
	 *
	 * @param string $content Any HTML with form shortcodes.
	 *
	 * @return array
	 */
	protected function get_forms_from_content( $content ) {
		$forms = [];

		// Find all CF7 forms in the content.
		preg_match_all( '/\[contact-form-7[^\]]+id="([^"]+)"/', $content, $matches );

		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $form_id ) {
				// ID can be a hash, or an ID. Ask Contact Form 7 for the real ID.
				$contact_form = wpcf7_get_contact_form_by_hash( $form_id );

				if ( ! $contact_form ) {
					$contact_form = wpcf7_contact_form( $form_id );
				}
				/**
				 * @var \WPCF7_ContactForm $contact_form The Contact Form 7 form object.
				 */

				$form = [
					'id'        => $contact_form->id(),
					'title'     => get_the_title( $contact_form->id() ),
					'edit_link' => admin_url( 'admin.php?page=wpcf7&post=' . $contact_form->id() ),
				];

				$forms[ 'cf7-' . $form['id'] ] = $form;
			}
		}

		// Todo: Support Gravity Forms.

		return $forms;
	}
}
