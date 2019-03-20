<?php

include_once(APPPATH . '/modules/nexo/vendor/autoload.php');

trait Nexo_Registers
{
	/**
	 * Get Register Status
	 * @param int register id
	 * @return json
	**/
	public function register_status_get( $register_id ) 
	{
		$this->response( $this->db->where( 'ID', $register_id )->get( store_prefix() . 'nexo_registers' )->result(), 200 );
	}
	
	/**
	 * Put register status
	 * @param int register_id
	 * @return json
	**/
	public function open_register_post( $register_id ) 
	{
		// Change Status
		$this->db->where( 'ID', $register_id )->update( store_prefix() . 'nexo_registers', array(
			'STATUS'	=>	'opened',
			'USED_BY'	=>	$this->post( 'used_by' )
		) );
		
		// 
		$this->db->insert( store_prefix() . 'nexo_registers_activities', array(
			'REF_REGISTER'	=>	$register_id,
			'AUTHOR'		=>	$this->post( 'used_by' ),
			'NOTE'			=> 	$this->post( 'note' ),
			'BALANCE'		=>	$this->post( 'balance' ),
			'DATE_CREATION'	=>	date_now(),
			'TYPE'			=>	'opening'
		) );
		
		$this->response( array(
			'status'		=>	'success'
		), 200 );
	}
	
	/**
	 * Close register
	 * @param int register_id
	 * @return json
	**/
	public function close_register_post( $register_id ) 
	{
		/**
		 * Before closing  a register
		 * we should make sure there is not a idle time enabled
		 * if there is one, let's close the idle time
		 */
		$registersActivities 	=	$this->db->where( 'REF_REGISTER', $register_id )
		->order_by( 'ID', 'desc' )
		->get( store_prefix() . 'nexo_registers_activities' )
		->result_array();

		if ( @$registersActivities[0][ 'TYPE' ] === 'idle_starts' ) {
			$this->db->insert( store_prefix() . 'nexo_registers_activities', array(
				'REF_REGISTER'	=>	$register_id,
				'BALANCE'		=>	0,
				'AUTHOR'		=>	$this->post( 'used_by' ),
				'NOTE'			=>	__( 'Inactivitée fermée automatiquement.', 'nexo' ),
				'DATE_CREATION'	=>	date_now(),
				'TYPE'			=>	'idle_ends'
			) );
		}

		// Change Status
		$this->db->where( 'ID', $register_id )->update( store_prefix() . 'nexo_registers', array(
			'STATUS'	=>	'closed',
			'USED_BY'	=>	0
		) );
		
		// 
		$this->db->insert( store_prefix() . 'nexo_registers_activities', array(
			'REF_REGISTER'	=>	$register_id,
			'BALANCE'		=>	$this->post( 'balance' ),
			'AUTHOR'		=>	$this->post( 'used_by' ),
			'NOTE'			=>	$this->post( 'note' ),
			'DATE_CREATION'	=>	date_now(),
			'TYPE'			=>	'closing'
		) );
		
		$this->response( array(
			'status'		=>	'success'
		), 200 );
	}
	
	/**
	 * Get Register
	 * @param int register id
	 * @return json
	**/
	public function registers_get( $id = null ) 
	{
		if( $id != null ) {
			$this->db->where( 'ID', $id );
		}
		$result		=	$this->db->get( store_prefix() . 'nexo_registers' )->result();
		$this->response( $result, 200 );
	}
	
	/**
	 * Register Activity
	 * @param int register id
	**/
	public function register_activities_get( $id )
	{
		$this->response( 
			$this->db->select( '*' )
			->from( store_prefix() . 'nexo_registers_activities' )
			->join( 'aauth_users', 'aauth_users.id = ' . store_prefix() . 'nexo_registers_activities.AUTHOR', 'left' )
			->where( store_prefix() . 'nexo_registers_activities.REF_REGISTER', $id )
			->get()->result() 
		);
	}
	
	/**
	 * Register activity by timerange
	 * @param int register id
	 * @return string json
	**/
	public function register_activities_by_timerange_post( $id ) 
	{
		$this->db->where('DATE_CREATION >=', $this->post('start'));
        $this->db->where('DATE_CREATION <=', $this->post('end'));
		
		$this->response( 
			$this->db->select( '*' )
			->from( store_prefix() . 'nexo_registers_activities' )
			->join( 'aauth_users', 'aauth_users.id = ' . store_prefix() . 'nexo_registers_activities.AUTHOR', 'left' )
			->where( store_prefix() . 'nexo_registers_activities.REF_REGISTER', $id )
			->get()->result() 
		);
	}
}