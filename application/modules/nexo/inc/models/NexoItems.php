<?php
class NexoItems extends Tendoo_Module
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {
        return $this->db
            ->get( store_prefix() . 'nexo_articles' )
            ->result_array();
    }

    public function getUsingBarcode( $barcode )
    {
        $item   =   $this->_get( $barcode, 'BARCODE' );
        return $item ? $item[0] : false;
    }

    private function _get( $ref, $index = 'ID' ) 
    {
        $item   =   $this->db
            ->where( $index, $ref )
            ->get( store_prefix() . 'nexo_articles' );
        
        return $item->result_array();
    }

    /**
     * Get Product using SKU
     * @param string SKU
     * @return array
     */
    public function getUsingSKU( $sku ) 
    {
        $item   =   $this->_get( $sku, 'SKU' );
        return $item ? $item[0] : false;
    }

    /**
     * Get stock flow using barcode and supply id
     * @param int id
     * @return stock flow
     */
    public function getStockFlow( $barcode )
    {
        $stockFlow  =   $this->db->where( 'ID', $barcode )
            ->get( store_prefix() . 'nexo_articles_stock_flow' )
            ->result_array();
        
        return $stockFlow ? $stockFlow[0] : false;
    }

    /**
     * Delete product using sku
     * @param string sku
     * @return json
     */
    public function deleteProductUsingSKU( $sku )
    {
        $item   =   $this->db->where( 'SKU', $sku )
            ->get( store_prefix() . 'nexo_articles' )
            ->result_array();

        if ( $item ) {
            $item_id   =   $item[0][ 'ID' ];
            $this->db->where( 'REF_ARTICLE', $item_id )->delete( store_prefix() . 'nexo_articles_meta' );
            $this->db->where( 'REF_ARTICLE', $item_id )->delete( store_prefix() . 'nexo_articles_variations' );
            $this->db->where( 'REF_ARTICLE_BARCODE', $item[0][ 'CODEBAR' ] )->delete( store_prefix() . 'nexo_articles_stock_flow' );
            $this->db->where( 'ID', $item_id )->delete( store_prefix() . 'nexo_articles' );
            return true;
        }
        return false;
    }
}