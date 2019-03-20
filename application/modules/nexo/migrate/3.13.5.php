<?php

$this->load->model( 'Nexo_Stores' );

$stores         =   $this->Nexo_Stores->get();

array_unshift( $stores, [
    'ID'        =>  0
]);

foreach( $stores as $store ) {
    $store_prefix       =   $store[ 'ID' ] == 0 ? '' : 'store_' . $store[ 'ID' ] . '_';

    $this->db->query('CREATE TABLE IF NOT EXISTS `'. $this->db->dbprefix . $store_prefix .'nexo_users_activities` (
        `ID` int(11) NOT NULL AUTO_INCREMENT,
        `AUTHOR` int(11) NOT NULL,
        `MESSAGE` text NOT NULL,
        `DATE_CREATION` datetime NOT NULL,
        PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');

}

include_once( dirname( __FILE__ ) . '/../inc/install.php' );

$install    =   new Nexo_Install;

$install->create_permissions();

$this->auth->allow_group( 'store.cashier', 'edit_profile' );
$this->auth->allow_group( 'store.manager', 'edit_profile' );
$this->auth->allow_group( 'sub-store.manager', 'edit_profile' );
$this->auth->deny_group( 'store.cashier', 'nexo.view.stores' );