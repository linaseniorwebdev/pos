<?php
class Nexo_Stock_Manager_Install extends Tendoo_Module
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Global Installation
     * The table for the transfert is set only once
     * @return void
    **/

    public function complete()
    {
        $this->sql( $this->db->dbprefix );
    }

    /**
     * Table SQL
     * @param string table prefix
     * @return void
    **/

    public function sql( $table_prefix = '' )
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `'. $table_prefix . 'nexo_stock_transfert` (
            `ID` int(11) NOT NULL AUTO_INCREMENT,
            `TITLE` varchar(200) NOT NULL,
            `DESCRIPTION` text NOT NULL,
            `STATUS` varchar(200) NOT NULL,
            `APPROUVED_BY` int(11) NOT NULL,
            `TYPE` varchar(200) NOT NULL,
            `AUTHOR` int(11) NOT NULL,
            `DATE_CREATION` datetime NOT NULL,
            `DATE_MOD` datetime NOT NULL,
            `DESTINATION_STORE` int(11) NOT NULL,
            `DEDUCT_FROM_SOURCE` varchar(200) NOT NULL,
            `FROM_STORE` int(11) NOT NULL,
            `FROM_RESPONSE` text NOT NULL,
            `TO_RESPONSE` text NOT NULL,
            PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'. $table_prefix . 'nexo_stock_transfert_items` (
            `ID` int(11) NOT NULL AUTO_INCREMENT,
            `DESIGN` varchar(200) NOT NULL,
            `SKU` varchar(200) NOT NULL,
            `QUANTITY` float(11) NOT NULL,
            `UNIT_PRICE` float(11) NOT NULL,
            `TOTAL_PRICE` float(11) NOT NULL,
            `REF_ITEM` int(11) NOT NULL,
            `DATE_CREATION` datetime NOT NULL,
            `DATE_MOD` datetime NOT NULL,
            `REF_TRANSFER` int(11) NOT NULL,
            `BARCODE` varchar(200) NOT NULL,
            PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
    }

    /**
     * Remove
     * @param string table prefix
     * @return void
    **/

    public function remove( $table_prefix = '' )
    {
        $this->db->query('DROP TABLE IF EXISTS `'.$table_prefix . 'nexo_stock_transfert`;');
        $this->db->query('DROP TABLE IF EXISTS `'.$table_prefix . 'nexo_stock_transfert_items`;');
    }

    /**
    * Remove All
    * @return void
    **/

    public function remove_all()
    {
        $this->remove( $this->db->dbprefix );
    }
}