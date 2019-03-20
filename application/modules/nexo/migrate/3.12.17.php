<?php

$this->load->model( 'Nexo_Stores' );

$stores         =   $this->Nexo_Stores->get();

array_unshift( $stores, [
    'ID'        =>  0
]);

foreach( $stores as $store ) {
    $store_prefix       =   $store[ 'ID' ] == 0 ? '' : 'store_' . $store[ 'ID' ] . '_';

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_registers' );
    if( ! in_array( 'NPS_URL', $columns ) && ! in_array( 'ASSIGNED_PRINTER', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_registers` 
        ADD `NPS_URL` varchar(200) NOT NULL AFTER `USED_BY`' );
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_registers` 
        ADD `ASSIGNED_PRINTER` varchar(200) NOT NULL AFTER `USED_BY`' );
    }
}