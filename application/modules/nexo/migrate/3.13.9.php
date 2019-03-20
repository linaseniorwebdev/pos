<?php

$this->load->model( 'Nexo_Stores' );

$stores         =   $this->Nexo_Stores->get();

array_unshift( $stores, [
    'ID'        =>  0
]);

foreach( $stores as $store ) {
    $store_prefix       =   $store[ 'ID' ] == 0 ? '' : 'store_' . $store[ 'ID' ] . '_';

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_commandes' );
    if( ! in_array( 'STATUS', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_commandes` 
        ADD `STATUS` varchar(200) NOT NULL AFTER `TYPE`' );
    }

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_commandes' );
    if( ! in_array( 'STATUS_DESCRIPTION', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_commandes` 
        ADD `STATUS_DESCRIPTION` text NOT NULL AFTER `TYPE`' );
    }

    /**
     * Update all orders, 
     * to set a default state to "completed".
     */
    $orders     =   $this->db->get( $store_prefix . 'nexo_commandes' )
        ->result_array();
    
    foreach( $orders as $order ) {
        if( $order[ 'TYPE' ] === 'nexo_order_comptant' ) {
            $this->db
                ->where( 'ID', $order[ 'ID' ] )
                ->update( $store_prefix . 'nexo_commandes', [
                'STATUS'    =>  'completed'
            ]);
        } else {
            $this->db
                ->where( 'ID', $order[ 'ID' ] )
                ->update( $store_prefix . 'nexo_commandes', [
                'STATUS'    =>  'pending'
            ]);
        }
    }
}