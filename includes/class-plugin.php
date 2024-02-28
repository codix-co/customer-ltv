<?php

namespace Codix\CustomerLTV\Includes;

class Plugin {

	private static ?Plugin $instance = null;

	public function __construct() {
		$this->init();
	}

	public function init() {
		add_action( 'init', [ $this, 'add_roles' ] );

		add_action( 'woocommerce_order_status_changed', [ $this, 'update_user_role' ] );

		add_shortcode( 'cdx_ltv_list', [ $this, 'shortcode' ] );

		register_deactivation_hook( __FILE__, [ $this, 'remove_roles' ] );
	}

	/**
	 * Add Option Pages.
	 */
	public function add_option_pages() {
		new Admin_Option_Page();
	}

	/**
	 * Get user roles.
	 */
	public function get_roles() {
		$roles = [
			'gold_customer'   => [
				'label'   => 'Gold Customer',
				'min_ltv' => 50000,
			],
			'silver_customer' => [
				'label'   => 'Silver Customer',
				'min_ltv' => 25000,
			],
			'bronze_customer' => [
				'label'   => 'Bronze Customer',
				'min_ltv' => 10000,
			],
		];

		// sort roles by min_ltv
		uasort( $roles, function( $a, $b ) {
			return $b['min_ltv'] - $a['min_ltv'];
		} );

		return $roles;
	}

	/**
	 * Add new user roles.
	 */
	public function add_roles() {
		foreach ( $this->get_roles() as $role => $data ) {
			add_role( $role, $data['label'], get_role( 'customer' )->capabilities );
		}
	}

	/**
	 * Remove user roles.
	 */
	public function remove_roles() {
		foreach ( $this->get_roles() as $role => $data ) {
			remove_role( $role );
		}
	}

	/**
	 * Update user role based on lifetime value orders.
	 *
	 * @param int $order_id
	 */
	public function update_user_role( int $order_id ) {

		$order   = wc_get_order( $order_id );
		$user_id = $order->get_user_id();
		$user    = new \WP_User( $user_id );

		// get $user role
		if ( in_array( 'administrator', $user->roles, true ) ) {
			return;
		}

		$ltv = $this->get_user_ltv( $user_id );

		// update user role based on get_roles array
		foreach ( $this->get_roles() as $role => $data ) {
			if ( $ltv >= $data['min_ltv'] ) {
				$user->set_role( $role );
				return;
			}
		}

	}

	/**
	 * Get user lifetime orders value.
	 *
	 * @param $user_id
	 * @return float
	 */
	protected function get_user_ltv( $user_id ) : float {
		$args = array(
			'customer_id' => $user_id,
			'return'      => 'ids',
			'status'      => array( 'wc-completed', 'wc-processing' ),
		);

		$orders = wc_get_orders( $args );
		$total  = 0;

		foreach ( $orders as $order_id ) {
			$order  = wc_get_order( $order_id );
			$total += $order->get_total();
		}

		return $total;
	}

	/**
	 * Shortcode to display users lifetime value.
	 *
	 * @return string
	 */
	public function shortcode(): string {
		$users = get_users( [
			'role__in' => array_merge( [ 'customer' ], array_keys( $this->get_roles() ) ),
		] );

		$html = [];

		$html[] = '<table>';
		$html[] = '<thead>';
		$html[] = '<tr>';
		$html[] = '<th style="text-align: start;">User</th>';
		$html[] = '<th style="text-align: start;">Role</th>';
		$html[] = '<th style="text-align: start;">LTV</th>';
		$html[] = '</tr>';
		$html[] = '</thead>';
		$html[] = '<tbody>';
		foreach ( $users as $user ) {
			$html[] = '<tr>';
			$ltv    = $this->get_user_ltv( $user->ID );
			$role   = $user->roles[0];

			$html[] = "<td>{$user->display_name}</td>";
			$html[] = "<td>{$role}</td>";
			$html[] = "<td>{$ltv} NIS</td>";
		};
		$html[] = '</tr>';
		$html[] = '</body>';
		$html[] = '</table>';

		return join( '', $html );
	}

	/**
	 * @return Plugin|null
	 */
	public static function get_instance(): ?Plugin {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}
}
