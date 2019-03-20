<?php
class NexoCategories extends Tendoo_Module
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * return a list of available categories
     * @return array
     */
    public function get()
    {
        return $this->db
            ->get( store_prefix() . 'nexo_categories' )
            ->result_array();
    }

    /**
     * get single category
     * @param int cateogry id
     * @return array | false
     */
    public function getSingle( $id ) 
    {
        $category   =   $this->db->where( 'ID', $id )
            ->get( store_prefix() . 'nexo_categories' )
            ->result_array();
        
        return ! empty( $category ) ? $category[0] : false;
    }
}