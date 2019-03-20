<?php
$this->load->model( 'Nexo_Stores' );

$stores         =   $this->Nexo_Stores->get();

array_unshift( $stores, [
    'ID'        =>  0
]);

foreach( $stores as $store ) {

    $store_prefix       =   $store[ 'ID' ] == 0 ? '' : 'store_' . $store[ 'ID' ] . '_';

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_stock_transfert' );
    if ( ! array_in([ 'DEDUCT_FROM_SOURCE' ], $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_stock_transfert` ADD `DEDUCT_FROM_SOURCE` VARCHAR(200) NOT NULL AFTER `FROM_STORE`' );
    }
}