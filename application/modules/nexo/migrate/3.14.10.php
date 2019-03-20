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

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_commandes_paiements' );
    if( ! in_array( 'REF_ID', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_commandes_paiements` 
        ADD `REF_ID` int(11) NULL AFTER `OPERATION`' );
    }

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_commandes_coupons' );
    if( ! in_array( 'REF_PAYMENT', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_commandes_coupons` 
        ADD `REF_PAYMENT` int(11) NULL AFTER `REF_COUPON`' );
    }
}