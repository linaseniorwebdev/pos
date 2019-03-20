<?php
/**
 * introduce multiple refund
 * per orders
 * @since 3.13.11
 */
$this->load->model( 'Nexo_Stores' );

$stores         =   $this->Nexo_Stores->get();

array_unshift( $stores, [
    'ID'        =>  0
]);

foreach( $stores as $store ) {
    $store_prefix       =   $store[ 'ID' ] == 0 ? '' : 'store_' . $store[ 'ID' ] . '_';

    $allRefunds     =   $this->db->get( $store_prefix . 'nexo_commandes_refunds' );

    $this->db->query( 'DROP TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_commandes_refunds`' );

    $this->db->query( 'CREATE TABLE IF NOT EXISTS `' . $this->db->dbprefix . $store_prefix . 'nexo_commandes_refunds` (
        `ID` int(11) NOT NULL AUTO_INCREMENT,
        `TITLE` varchar(200) NOT NULL, 
        `SUB_TOTAL` float(11) NOT NULL,
        `TOTAL` float(11) NOT NULL,
        `SHIPPING` float(11) NOT NULL,
        `PAYMENT_TYPE` varchar(200) NOT NULL,
        `DESCRIPTION` text NOT NULL,
        `DATE_CREATION` datetime NOT NULL,
        `AUTHOR` int(11) NOT NULL,
        `REF_ORDER` int(11) NOT NULL,
        `TYPE` varchar(200) NOT NULL,
        PRIMARY KEY (`ID`)
    )');

    $this->db->query( 'CREATE TABLE IF NOT EXISTS `' . $this->db->dbprefix . $store_prefix . 'nexo_commandes_refunds_products` (
        `ID` int(11) NOT NULL AUTO_INCREMENT,
        `NAME` varchar(200) NOT NULL,
        `REF_ITEM` int(11) NOT NULL, 
        `REF_REFUND` int(11) NOT NULL,
        `PRICE` float(11) NOT NULL,
        `QUANTITY` float(11) NOT NULL,
        `TOTAL_PRICE` float(11) NOT NULL,
        `STATUS` varchar(200) NOT NULL,
        `DESCRIPTION` text,
        `DATE_CREATION` datetime NOT NULL,
        `DATE_MOD` datetime NOT NULL,
        `AUTHOR` int(11) NOT NULL,
        PRIMARY KEY (`ID`)
    )');

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_commandes_paiements' );
    if( ! in_array( 'OPERATION', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_commandes_paiements` 
        ADD `OPERATION` varchar(50) NOT NULL AFTER `PAYMENT_TYPE`' );
    }

    /**
     * Set all payments as incoming
     */
    $orders     =   $this->db->get( $this->db->dbprefix . $store_prefix . 'nexo_commandes_paiements' )
        ->result_array();
    foreach( $orders as $order ) {
        $this->db->update( $this->db->dbprefix . $store_prefix . 'nexo_commandes_paiements', [
            'OPERATION' =>  'incoming'
        ]);
    }

}
foreach([
    'nexo.read.detailed-report',
    'nexo.read.best-sales',
    'nexo.read.daily-sales',
    'nexo.read.incomes-losses',
    'nexo.read.expenses-listings',
    'nexo.read.cash-flow',
    'nexo.read.annual-sales',
    'nexo.read.cashier-performances',
    'nexo.read.customer-statistics',
    'nexo.read.inventory-tracking',
    'nexo.view.registers-history',
    'nexo.manage.settings',
    'nexo.manage.stores-settings',
] as $reportPermission ) {
    $this->auth->allow_group( 'sub-store.manager', $reportPermission );
}

// Cashier Permissions
foreach([
    'nexo.create.orders',
    'nexo.use.registers',
    'nexo.view.stores',
    'nexo.enter.stores',
    'edit_profile'
] as $permission ) {
    $this->auth->allow_group( 'sub-store.manager', $permission );
}

foreach([ 
    'coupons',
    'stores',
    'items',
    'categories',
    'departments',
    'providers',
    'supplies',
    'customers-groups',
    'customers',
    'invoices',
    'registers',
    'backups',
    'refund',
    'stock-adjustment',
    'taxes',
] as $component ) {
    foreach([ 'create.', 'edit.', 'delete.', 'view.' ] as $action ) {
        $this->auth->allow_group( 'admin', 'nexo.' . $action . $component );
    }
}