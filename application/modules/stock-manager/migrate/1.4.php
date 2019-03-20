<?php
$this->load->model( 'Nexo_Stores' );

$stores         =   $this->Nexo_Stores->get();

array_unshift( $stores, [
    'ID'        =>  0
]);

foreach( $stores as $store ) {

    $store_prefix       =   $store[ 'ID' ] == 0 ? '' : 'store_' . $store[ 'ID' ] . '_';

    $columns            =   $this->db->list_fields( 'nexo_stock_transfert' );

    if ( ! array_in([ 'FROM_RESPONSE' ], $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . 'nexo_stock_transfert` ADD `FROM_RESPONSE` TEXT NOT NULL AFTER `FROM_STORE`' );
    }
    
    if ( ! array_in([ 'TO_RESPONSE' ], $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . 'nexo_stock_transfert` ADD `TO_RESPONSE` TEXT NOT NULL AFTER `FROM_STORE`' );
    }
    
    if ( ! array_in([ 'STATUS' ], $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . 'nexo_stock_transfert` ADD `STATUS` varchar(200) NOT NULL AFTER `FROM_STORE`' );
    }
    
    $columns            =   $this->db->list_fields( 'nexo_stock_transfert_items' );
    if ( ! array_in([ 'SKU' ], $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . 'nexo_stock_transfert_items` ADD `SKU` varchar(200) NOT NULL AFTER `DESIGN`' );
    }
}