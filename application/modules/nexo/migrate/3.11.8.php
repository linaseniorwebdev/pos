<?php

$this->load->model( 'Nexo_Stores' );

$stores         =   $this->Nexo_Stores->get();

array_unshift( $stores, [
    'ID'        =>  0
]);

foreach( $stores as $store ) {

    $store_prefix       =   $store[ 'ID' ] == 0 ? '' : 'store_' . $store[ 'ID' ] . '_';

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_articles' );
    if( ! in_array( 'ALTERNATIVE_NAME', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_articles` 
        ADD `ALTERNATIVE_NAME` varchar(200) NOT NULL AFTER `DESIGN`' );
    }
    
    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_commandes_produits' );
    if( ! in_array( 'ALTERNATIVE_NAME', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_commandes_produits` 
        ADD `ALTERNATIVE_NAME` varchar(200) NOT NULL AFTER `NAME`' );
    }
}