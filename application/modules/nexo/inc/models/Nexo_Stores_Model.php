<?php
class Nexo_Stores_Model extends Tendoo_Module
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getOpenedStores()
    {
        return $this->db->where( 'STATUS', 'opened' )
            ->get( 'nexo_stores' )
            ->result_array();
    }

    /**
     * Get Store by id
     * @param int store id
     * @return array
     */
    public function get( $id )
    {
        $result     =   $this->db->where( 'ID', $id )
            ->get( 'nexo_stores' )
            ->result_array();
        return @$result[0];
    }
}