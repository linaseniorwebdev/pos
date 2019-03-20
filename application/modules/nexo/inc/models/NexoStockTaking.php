<?php
/**
 * @since 3.12.13
 * @author NexoPOS Solutions
 */
class NexoStockTaking
{
    /**
     * refresh stock taking
     * @param int stock id
     * @return void
     */
    public function refresh_stock_taking( $id )
    {
        $items  =   get_instance()->db->where( 'REF_SHIPPING', $id )
            ->get( store_prefix() . 'nexo_articles_stock_flow' )
            ->result_array();

        $itemsNegative     =   array_filter( $items, function( $item ){
            return in_array( $item[ 'TYPE' ], [ 'defective', 'adjustment', 'sale', 'transfert_out' ]);
        });

        $itemsPositive     =   array_filter( $items, function( $item ){
            return in_array( $item[ 'TYPE' ], [ 'supply', 'transfert_in' ]);
        });

        /**
         * positive operations
         */
        $positiveTotal          =   0;
        $positiveTotalItems     =   0;
        foreach( $itemsPositive as $item ) {
            $positiveTotalItems     +=  floatval( $item[ 'QUANTITE' ] );
            $positiveTotal          +=  ( floatval( $item[ 'QUANTITE' ] ) * floatval( $item[ 'UNIT_PRICE' ] ) );
        }

        /**
         * negative operations
         */
        $negativeTotal          =   0;
        $negativeTotalItems     =   0;
        foreach( $itemsNegative as $item ) {
            $negativeTotalItems     +=  floatval( $item[ 'QUANTITE' ] );
            $negativeTotal          +=  ( floatval( $item[ 'QUANTITE' ] ) * floatval( $item[ 'UNIT_PRICE' ] ) );
        }

        /**
         * once ready, let's update the stock taking
         */
        get_instance()->db->where( 'ID', $id )
            ->update( store_prefix() . 'nexo_arrivages', [
                'VALUE' =>  $positiveTotal - $negativeTotal,
                'ITEMS' =>  $positiveTotalItems - $negativeTotalItems
            ]);

        return [
            'status'    =>  'success',
            'message'   =>  __( 'L\'approvisionnement a été correctement rafraichi', 'nexo' ),
            'result'    =>  [
                'outcome'               =>  $positiveTotal,
                'incoming_items'        =>  $positiveTotalItems,
                'income'                =>  $negativeTotal,
                'outgoing_items'        =>  $negativeTotalItems
            ]
        ];
    }

    
}