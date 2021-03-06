<?php
/*
	Plugin Name: GravityForms Estados e Cidades
	Version: 0.1.0
	Author: Elvis Henrique Pereira
	Description: Preenche campos do GravityForms com "choices" com os estados e cidades do Brasil.
*/

namespace GravityForms;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

class EstadosCidades
{
	private $data;

	public function __construct()
	{
		$this->data = $this->get_data();

		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		add_action( 'gform_admin_pre_render', array( &$this, 'pre_render' ) );
		add_action( 'gform_pre_render', array( &$this, 'pre_render' ) );
		add_action( 'gform_pre_submission', array( &$this, 'pre_submission' ) );
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script(
			'gravityforms-estados-cidades',
			plugins_url( 'js/custom.js', __FILE__ ),
			array( 'jquery' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'js/custom.js' ),
			true
		);

		wp_localize_script(
			'gravityforms-estados-cidades',
			'gfec',
			array(
				'estadosCidades' => $this->data,
			)
		);
	}

	public function pre_submission( $form )
	{
		foreach ( $form['fields'] as $field ) {
			if ( strpos( $field->cssClass, 'estado' ) !== false ) {
				setcookie( "gf_{$form['id']}_{$field["id"]}", $_POST[ "input_{$field["id"]}" ], time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
			}
		}
	}

	public function pre_render( $form )
	{
		$field_id = null;

		foreach ( $form['fields'] as &$field ) {
			if ( strpos( $field->cssClass, 'estado' ) !== false ) {
				$field_id = $field->id;
				$field->choices = $this->get_estados_choices();
			}
		}

		foreach ( $form['fields'] as &$field ) {
			if ( strpos( $field->cssClass, 'cidade' ) !== false ) {
				$field->choices = $this->get_cidades_choices( $form['id'], $field_id );
			}
		}

		return $form;
	}

	public function get_estados_choices()
	{
		$choices[] = array( 'value' => '', 'text' => 'Selecione' );

		foreach ( $this->data as $value ) {
			$choices[] = array( 'value' => $value['sigla'], 'text' => $value['nome'] );
		}

		return $choices;
	}

	public function get_cidades_choices( $form_id, $state_id )
	{
		$sigla     = $this->get_current_state( $form_id, $state_id );
		$choices[] = array( 'value' => '', 'text' => 'Selecione' );

		foreach ( $this->data as $value ) {
			if ( $value['sigla'] === $sigla ) {
				foreach ( $value['cidades'] as $cidade ) {
					$choices[] = array( 'value' => $cidade, 'text' => $cidade );
				}
			}
		}

		return $choices;
	}

	public function get_current_state( $form_id, $field_id )
	{
		if ( isset( $_POST[ "input_{$field_id}" ] ) ) {
			return $_POST[ "input_{$field_id}" ];
		}

		if ( isset( $_COOKIE[ "gf_{$form_id}_{$field_id}" ] ) ) {
			return $_COOKIE[ "gf_{$form_id}_{$field_id}" ];
		}

		return false;
	}

	function get_data()
	{
		return json_decode( file_get_contents( plugin_dir_path( __FILE__ ) . 'data.json' ), true )['estados'];
	}
}

new EstadosCidades();
