<?php
/**
 * update permission for users
 * @since 3.13.15
 */
$this->load->model( 'Nexo_Stores' );

$stores         =   $this->Nexo_Stores->get();

array_unshift( $stores, [
    'ID'        =>  0
]);

foreach( $stores as $store ) {
    $store_prefix       =   $store[ 'ID' ] == 0 ? '' : 'store_' . $store[ 'ID' ] . '_';

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_rewards_system' );
    if( ! in_array( 'NAME', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_rewards_system` 
        ADD `NAME` varchar(200) NOT NULL AFTER `ID`' );
    }
}