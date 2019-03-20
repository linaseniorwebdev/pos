<?php
/**
 * @since 3.12.13
 * @author NexoPOS Solutions
 */
class NexoProvidersModel extends Tendoo_Module
{
    /**
     * Get provider using the iD
     * @param int provider id
     * @return array | null
     */
    public function get( $id )
    {
        $provider   =   $this->db->where( 'ID', $id )
            ->get( store_prefix() . 'nexo_fournisseurs' )
            ->result_array();
        
        return @$provider[0];
    }
}