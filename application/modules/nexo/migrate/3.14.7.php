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

    $permissions[ 'nexo.read.today-report' ] 	=	__( 'Lire le rapport journalier', 'nexo' );

    foreach( $permissions as $namespace => $perm ) {
        get_instance()->auth->create_perm( 
            $namespace,
            $perm
        );
    }

    foreach(['nexo.read.today-report' ] as $reportPermission ) {
        get_instance()->auth->allow_group( 'store.manager', $reportPermission );
        get_instance()->auth->allow_group( 'master', $reportPermission );
        get_instance()->auth->allow_group( 'admin', $reportPermission );
        get_instance()->auth->allow_group( 'sub-store.manager', $reportPermission );
    }

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_coupons' );
    if( ! in_array( 'REF_CUSTOMER', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_coupons` 
        ADD `REF_CUSTOMER` int(11) NOT NULL AFTER `REWARDED_CASHIER`' );
    }

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_clients' );
    if( ! in_array( 'REWARD_POINT_COUNT', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_clients` 
        ADD `REWARD_POINT_COUNT` int(11) NOT NULL AFTER `REF_GROUP`' );
    }

    $columns            =   $this->db->list_fields( $store_prefix . 'nexo_clients_groups' );
    if( ! in_array( 'REF_REWARD', $columns ) ) {
        $this->db->query( 'ALTER TABLE `' . $this->db->dbprefix . $store_prefix . 'nexo_clients_groups` 
        ADD `REF_REWARD` int(11) NOT NULL AFTER `AUTHOR`' );
    }

    /**
     * introducing the reward syste
     * @since 3.14.6
     */
    $this->db->query('CREATE TABLE IF NOT EXISTS `'. $this->db->dbprefix . $store_prefix .'nexo_rewards_system` (
        `ID` int(11) NOT NULL AUTO_INCREMENT,
        `NAME` varchar(200) NOT NULL,
        `DESCRIPTION` text NOT NULL,
        `DATE_CREATION` datetime NOT NULL,
        `DATE_MOD` datetime NOT NULL,
        `AUTHOR` int(11) NOT NULL,
        `REF_COUPON` int(11) NOT NULL,
        `COUPON_EXPIRATION` int(11) NOT NULL,
        `MAXIMUM_POINT` float(11) NOT NULL,
        PRIMARY KEY (`ID`)
    )' );

    $this->db->query('CREATE TABLE IF NOT EXISTS `'. $this->db->dbprefix . $store_prefix .'nexo_rewards_rules` (
        `ID` int(11) NOT NULL AUTO_INCREMENT,
        `DESCRIPTION` text NOT NULL,
        `DATE_CREATION` datetime NOT NULL,
        `DATE_MOD` datetime NOT NULL,
        `AUTHOR` int(11) NOT NULL,
        `REF_REWARD` int(11) NOT NULL,
        `PURCHASES` int(11) NOT NULL,
        `POINTS` int(11) NOT NULL,
        PRIMARY KEY (`ID`)
    )' );
}