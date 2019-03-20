<?php
class NexoProducts extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get Product from a specific table
     * @param string/int id, sku, barocde
     * @param string column
     * @param int/null 
     * @return array/boolean
     */
    public function get( $id, $column = 'ID', $store = null )
    {
        $table_name   =   $store !== null ? 
            'store_' . $store . '_nexo_articles' : 
            store_prefix() . 'nexo_articles';

        $product    =   $this->db->where( $column, $id )   
            ->get( $table_name )
            ->result_array();
        
        return empty( $product ) ? false : $product[0];
    }

    /**
     * Add product stock flow
     * @param int order id
     * @param array flow config
     * @return array
     */
    public function addStockFlow( $item_id, $data ) 
    {
        $item   =   $this->get( $item_id );

        if ( intval( $item[ 'STOCK_ENABLED' ] ) === 1 ) {

            switch( $data[ 'TYPE' ] ) {
                case 'usable': 
                    $after_quantite     =   floatval( $item[ 'QUANTITE_RESTANTE' ] ) + floatval( $data[ 'QUANTITE' ] );
                break;
                default:
                    $after_quantite     =   floatval( $item[ 'QUANTITE_RESTANTE' ] ) - floatval( $data[ 'QUANTITE' ] );;
                break;
            }
    
            $data       =   array_merge( $data, [
                'BEFORE_QUANTITE'       =>  $item[ 'QUANTITE_RESTANTE' ],
                'AFTER_QUANTITE'        =>  $after_quantite,
                'REF_ARTICLE_BARCODE'   =>  $item[ 'CODEBAR' ]
            ]);

            /**
             * update remaining quantity
             */
            $this->db->where( 'ID', $item[ 'ID' ] )
                ->update( store_prefix() . 'nexo_articles', [
                    'QUANTITE_RESTANTE'     =>  $after_quantite,
                    'DATE_MOD'              =>  date_now(),
                ]);
        } else {
            /**
             * no changes should be made on the history.
             * so we just reference a sale has been made
             * nothing more.
             */
            $data       =   array_merge( $data, [
                'BEFORE_QUANTITE'       =>  $item[ 'QUANTITE_RESTANTE' ],
                'AFTER_QUANTITE'        =>  $item[ 'QUANTITE_RESTANTE' ],
                'REF_ARTICLE_BARCODE'   =>  $item[ 'CODEBAR' ]
            ]);
        }

        /**
         * update stock flow
         */
        $this->db->where( 'REF_ARTICLE_BARCODE', $item[ 'CODEBAR' ] )
            ->insert( store_prefix() . 'nexo_articles_stock_flow', $data );
            
        return [
            'status'    =>  'success',
            'message'   =>  __( 'Le stock a été mis à jour', 'nexo' )
        ];
    }
}