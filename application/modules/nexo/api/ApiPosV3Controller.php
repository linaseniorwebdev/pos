<?php
/**
 * @todo : provide route to {
 *      crud products
 *      crud orders
 * }
 */
class ApiPosV3Controller extends Tendoo_Api
{
    public function getCategoriesAndProducts( $catid = null )
    {
        $categories     =   get_instance()->db
        ->where( 'PARENT_REF_ID',  $catid )
        ->get( store_prefix() . 'nexo_categories' )
        ->result_array();

        $type       =   count( $categories ) > 0 ? 'categories' : 'items';

        if ( count( $categories ) === 0 ) {
            $items  =   get_instance()->db
                ->where( 'REF_CATEGORIE', $catid )
                ->where("( ( `TYPE` = 2 AND `STATUS`=1 ) OR ( `TYPE` = 1 AND `STATUS`=1 AND `QUANTITE_RESTANTE` > 0 ) )" )
                ->get( store_prefix() . 'nexo_articles' )
                ->result_array();
        }

        /**
         * prepare a return to
         */
        $category   =   get_instance()->db->where( 'ID', $catid )
            ->get( store_prefix() . 'nexo_categories' )
            ->result_array();
        $return_to  =   empty( $category ) ? 0 : $category[0][ 'PARENT_REF_ID' ];
        $category   =   @$category[0];

        $this->response( compact( 'categories', 'items', 'type', 'return_to', 'category' ) ); 
    }
}