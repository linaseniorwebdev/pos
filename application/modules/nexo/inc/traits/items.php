<?php

use SimpleExcel\SimpleExcel;

trait Nexo_items
{
    /**
     *  Create Bulk Items
     *  @param
     *  @return
    **/

    public function create_bulk_items_post()
    {
        // get all sku as in an array
        $old_items          =   $this->db->get( store_prefix() . 'nexo_articles' )->result_array();
        $skus               =   [];
        $barcodes           =   [];

        foreach( $old_items as $old_item ) {
            $skus[ $old_item[ 'ID' ] ]      =   $old_item[ 'SKU' ];
            $barcodes[ $old_item[ 'ID' ] ]   =   $old_item[ 'CODEBAR' ];
        }

        if( $this->post( 'refresh' ) == 'false' ) {
            $this->db->query( 'TRUNCATE `' . $this->db->dbprefix . store_prefix() . 'nexo_articles`' );
            $this->db->query( 'TRUNCATE `' . $this->db->dbprefix . store_prefix() . 'nexo_articles_meta`' );
            $this->db->query( 'TRUNCATE `' . $this->db->dbprefix . store_prefix() . 'nexo_articles_stock_flow`' );
        }

        $delivery_id            =   $this->post( 'delivery_id' );
        $delivery_cost          =   0;
        $delivery_quantity      =   0;

        foreach( $this->post( 'items' ) as $index => $item ) {
            $randBarcode            =       $index . date( 'y' ) . date( 'i' ) . rand( 0, 999 );
            if( empty( $item[ 'CODEBAR' ] ) ) {
                $item[ 'CODEBAR' ]      =   $randBarcode;
            }

            if( empty( $item[ 'SKU' ] ) ) {
                $item[ 'SKU' ]      =   $randBarcode;
            }

            if( empty( $item[ 'DESIGN' ] ) ) {
                $item[ 'DESIGN' ]   =   'Unamed Item (' . $index . ')';
            }

            /**
             * Let's pull the barcode of this item if it already exists
             */
            $freshItem  =   $this->db->where( 'CODEBAR', $item[ 'CODEBAR' ] )->get( store_prefix() . 'nexo_articles' )
                ->result_array();
            if ( $freshItem ) {
                $before_quantity    =   $freshItem[0][ 'QUANTITE_RESTANTE' ];
            } else {
                $before_quantity    =   0;
            }

            // include the price with Taxe
            $item[ 'PRIX_DE_VENTE' ]            =   floatval(preg_replace('/[^\d\.]+/', '', $item[ 'PRIX_DE_VENTE' ]) );
            $item[ 'PRIX_DE_VENTE_TTC' ]        =   $item[ 'PRIX_DE_VENTE' ];
            $item[ 'DATE_CREATION' ]            =   date_now();

            // if overwrite is enabeld
            if( $this->post( 'overwrite' ) == 'true' ) {
                if( in_array( $item[ 'SKU' ], $skus ) || in_array( $item[ 'CODEBAR' ], $barcodes ) ) {
                    $this->db->where( 'SKU', $item[ 'SKU' ] )->update( store_prefix() . 'nexo_articles', $item );
                } else {     
                    $this->db->insert( store_prefix() . 'nexo_articles', $item );               
                }
            } else {
                // otherwise, just save product which doesn't yet exists
                if( ! in_array( $item[ 'SKU' ], $skus ) ) {
                    $this->db->insert( store_prefix() . 'nexo_articles', $item );
                }
            }

            if( $this->post( 'overwrite' ) == 'true' ) {
                // Make a supply for this import
                $this->db->insert( store_prefix() . 'nexo_articles_stock_flow', [
                    'REF_ARTICLE_BARCODE'   =>  $item[ 'CODEBAR' ],
                    'BEFORE_QUANTITE'       =>  $before_quantity,
                    'QUANTITE'              =>  ( int ) @$item[ 'QUANTITY' ],
                    'AFTER_QUANTITE'        =>  $before_quantity + floatval( @$item[ 'QUANTITY' ] ),
                    'AUTHOR'                =>  User::id(),
                    'REF_PROVIDER'          =>  $this->post( 'provider_id' ),
                    'TYPE'                  =>  'import',
                    'UNIT_PRICE'            =>  floatval( @$item[ 'PRIX_DACHAT' ] ),
                    'TOTAL_PRICE'           =>  floatval( @$item[ 'PRIX_DACHAT' ] ) * floatval( @$item[ 'QUANTITY' ] ),
                    'REF_SHIPPING'          =>  $delivery_id
                ]);

                $delivery_cost              +=      floatval( @$item[ 'PRIX_DACHAT' ] ) * floatval( @$item[ 'QUANTITY' ] );
                $delivery_quantity          +=      floatval( @$item[ 'QUANTITY' ] );          
            }
        }

        $this->db->where( 'ID', $delivery_id )->update( store_prefix() . 'nexo_arrivages', [
            'VALUE'         =>  $delivery_cost,
            'ITEMS'         =>  $delivery_quantity,
            'REF_PROVIDER'  =>  $this->post( 'provider_id' )
        ]);

        $this->__success();
    }

    /**
     *  Create Shipping Categories
     *  @param POST shippings
     *  @param POST categories
     *  @return json
    **/

    public function create_shipping_categories_post()
    {
        /**
         * if we're adding to the current store, this action is skipped
         */
        if( $this->post( 'refresh' ) != 'true' ) {

            $this->db->query( 'TRUNCATE `' . $this->db->dbprefix . store_prefix() . 'nexo_arrivages`' );
            $this->db->query( 'TRUNCATE `' . $this->db->dbprefix . store_prefix() . 'nexo_categories`' );

            // Create default shipping
            $this->db->insert( store_prefix() . 'nexo_arrivages', [
                'TITRE'             =>      sprintf( __( 'Approvisionnement %s', 'nexo' ), date_now() ),
                'AUTHOR'            =>      $this->post( 'author' ),
                'DATE_CREATION'     =>      $this->post( 'date' ),
                'REF_PROVIDER'      =>      $this->post( 'provider_id' )
            ]);

            $delivery_id    =   $this->db->insert_id();

        } else {
            $delivery_id   =   $this->post( 'delivery_id' );
        }

        $categories             =   array();
        $old_categories         =   $this->db->get( store_prefix() . 'nexo_categories' )->result_array();
        $old_categories_ids     =   [];

        foreach( $old_categories as $category ) {
            $old_categories_ids[ $category[ 'ID' ] ]     =   strtolower( $category[ 'NOM' ] );
        }

        if( $this->post( 'cats' ) ) {
            foreach( $this->post( 'cats' ) as $cat ) {
                // only insert if the category doesn't exists
                if( ! in_array( strtolower( $cat ), $old_categories_ids ) ) {
                    $this->db->insert( store_prefix() . 'nexo_categories', [
                        'NOM'           =>  ucwords( $cat ),
                        'AUTHOR'        =>  $this->post( 'author' ),
                        'DATE_CREATION' =>  $this->post( 'date' )
                    ]);
                }
            }
        } else {
            // only insert if the category doesn't exists
            if( ! in_array( $this->post( 'default_cat_title' ), $old_categories_ids ) ) {
                $this->db->insert( store_prefix() . 'nexo_categories', [
                    'NOM'           =>      $this->post( 'default_cat_title' ),
                    'AUTHOR'        =>      $this->post( 'author' ),
                    'DATE_CREATION' =>  $this->post( 'date' )
                ]);
            }
        }

        $raw_categories         =   $this->db->get( store_prefix() . 'nexo_categories' )
            ->result_array();
        
        /**
         * we should return all the categories including the 
         * new detected on the CSV.
         */
        foreach( $raw_categories as $raw_cat ) {
            $categories[ url_title( $raw_cat[ 'NOM' ], '_' ) ]  =   $raw_cat[ 'ID' ];
        }

        return $this->response( [
            'categories'        =>  $categories,
            'delivery_id'       =>  $delivery_id
        ], 200 );
    }

    /**
     * Get item
     *
    **/

    public function item_get($id = null, $filter = 'ID' )
    {
        if ($id != null && ! in_array( $filter, [ 'sku-barcode', 'search' ] ) ) {
            $result        =    $this->db->where($filter, $id)->get( store_prefix() . 'nexo_articles')->result();
            $result        ?    $this->response($result, 200)  : $this->response(array(), 404);
        } elseif ($filter == 'sku-barcode') {
            
            $result        =    $this->db
            ->where('CODEBAR', $id)
            ->or_where('SKU', $id)
            ->get( store_prefix() . 'nexo_articles')
            ->result();

            /**
             * if the item can't be found
             */
            if ( ! $result ) {
                return $this->__404();
            }

            /**
             * We would like to include the stock for the items grouped
             */
            if ( $result[0]->TYPE == '3' ) {
                $meta   =   $this->db->where( 'REF_ARTICLE', $result[0]->ID )
                ->where( 'KEY', 'included_items' )
                ->get( store_prefix() . 'nexo_articles_meta' )
                ->result_array();

                /**
                 * if meta is set
                 */
                if ( $meta ) {
                    $items  =   json_decode( $meta[0][ 'VALUE' ] );
                    
                    foreach ( $items as $includedItem ) {
                        $_item   =  $this->db->where( 'CODEBAR', $includedItem->barcode )
                        ->get( store_prefix() . 'nexo_articles' )
                        ->result_array();

                        /**
                         * we're including the included items
                         */
                        if ( @$result[0]->INCLUDED_ITEMS == null ) {
                            $result[0]->INCLUDED_ITEMS  =   [];
                        }

                        $result[0]->INCLUDED_ITEMS[]    =   array_merge( $_item[0], ( array )  $includedItem );
                    }
                }
            }

            $result        ?    $this->response($result, 200)  : $this->response(array(), 404);
        } elseif( $filter == 'search' ) {
            $result        =    $this->db
            ->where('CODEBAR', $id)
            ->or_where('SKU', $id)
            ->or_like( 'DESIGN', $id )
            ->get( store_prefix() . 'nexo_articles')
            ->result();
            $result        ?    $this->response($result, 200)  : $this->response(array(), 404);
        } else {
            $this->db->select('*,
			' . store_prefix() . 'nexo_articles.ID as ID,
			' . store_prefix() . 'nexo_categories.ID as CAT_ID
			')
            ->from( store_prefix() . 'nexo_articles')
            ->join( store_prefix() . 'nexo_categories', store_prefix() . 'nexo_articles.REF_CATEGORIE = ' . store_prefix() . 'nexo_categories.ID');
            $this->response($this->db->get()->result());
        }
    }

    /** 
     * Search Item
    **/

    public function item_search_post() 
    {
        $result        =    $this->db
        ->where('CODEBAR', $this->post( 'fetch' ) )
        ->or_where('SKU', $this->post( 'fetch' ) )
        ->or_like( 'DESIGN', $this->post( 'fetch' ) )
        ->get( store_prefix() . 'nexo_articles')
        ->result();
        $result        ?    $this->response($result, 200)  : $this->response(array(), 404);
    }

    /** 
     * Search Item With a GET
    **/

    public function search_item_get() 
    {
        $result        =    $this->db
        ->or_like( 'DESIGN', $this->get( 'search' ) )
        ->or_like( 'CODEBAR', $this->get( 'search' ) )
        ->or_like( 'SKU', $this->get( 'search' ) )
        ->get( store_prefix() . 'nexo_articles')
        ->result();

        /**
         * we might add the SKU and barcode on each item name
         */
        foreach( $result as &$_item ) {
            $_item->DESIGN  .= ' - ' . $_item->CODEBAR . '  - ' . $_item->SKU;
        }

        $result        ?    $this->response($result, 200)  : $this->response(array(), 404);
    }

    /**
     *  item get with meta
     *  @param int id
     *  @return json
    **/

    public function item_with_meta_get( $id = null, $using = 'ID' )
    {
        // return $this->item_get($id, $using );
        if( $using == 'ID' ) {

            if( $id != null ) {
                $this->db
                ->where( store_prefix() . 'nexo_articles.ID', $id );
            }

            $this->db->group_by( 'KEY' );

            $query_meta     =   $this->db
            ->get( store_prefix() . 'nexo_articles_meta' )->result();

        } else if( $using == 'sku-barcode' ) {

            $this->db->select( '*' )
            ->from( 'nexo_articles' )
            ->join( store_prefix() . 'nexo_articles_meta',
                store_prefix() . 'nexo_articles_meta.REF_ARTICLE = ' .
                store_prefix() . 'nexo_articles.ID'
            );

            if( $id != null ) {
                $this->db
                ->where( store_prefix() . 'nexo_articles.CODEBAR', $id )
                ->or_where( store_prefix() . 'nexo_articles.SKU', $id );
            }

            $this->db->group_by( 'KEY' );

            $query_meta     =   $this->db
            ->get()->result();

        } else {
            $this->__failed();
        }

        $query_select   =   [];
        $table_select   =   [];
        $join_select    =   [];
        $where_select   =   [];

        foreach( $query_meta as $key => $meta ) {
            $single_select      =   '_' . $key . 'meta';
            $query_select[]     =   $single_select . '.VALUE as ' . $meta->KEY;
            $table_select[]     =   $single_select;
            $join_select[]      =   "LEFT JOIN {$this->db->dbprefix}nexo_articles_meta as {$single_select} ON articles_meta.REF_ARTICLE = {$single_select}.REF_ARTICLE";
            $where_select[]     =   $single_select . '.KEY = "' . $meta->KEY . '"';
        }

        $key_code   =   $id != null ? "AND ( " .

        (
            $id != null ?
                implode(' OR ', $where_select)
            : ''
        ) .

        ")"

        : '';

        $SQL    =   "SELECT *, nexo_articles.ID as ID, nexo_categories.ID as CAT_ID " . (

        count( $query_select ) > 0 ? ',' : '' ) .

        implode(',', $query_select) .
        " FROM      {$this->db->dbprefix}nexo_articles_meta articles_meta " .

        implode( ' ', $join_select ) .

        " RIGHT JOIN {$this->db->dbprefix}nexo_articles as nexo_articles ON nexo_articles.ID = articles_meta.REF_ARTICLE " .

        " RIGHT JOIN {$this->db->dbprefix}nexo_categories as nexo_categories ON nexo_categories.ID = nexo_articles.REF_CATEGORIE " .

        ( $id != null ?
            "WHERE ( " . ( $id != null ? " nexo_articles.CODEBAR = " . $this->db->escape( $id ) : '' ) .
            ( $id != null ?
                " OR nexo_articles.SKU = " . $this->db->escape( $id ) : '' ) . " ) " . $key_code . " "

        : "" ) . " GROUP BY nexo_articles.ID";

        var_dump( $SQL );die;

        $query  =   $this->db->query(
            $SQL
        )->result();

        $this->response( $query, 200 );
    }

    /**
     * Delete Item from Shop
     *
    **/

    public function item_delete($id = null)
    {
        if ($id == null) {
            $this->response(array(
                'status' => 'failed'
            ));
        } else {
            $this->db->where('ID', $id)->delete( store_prefix() . 'nexo_articles')->result();

            $this->response(array(
                'status' => 'failed'
            ));
        }
    }

    /**
     * PUt item
     *
    **/

    public function item_put()
    {
        $request    =    $this->db->where($this->put('id'))
        ->set('DESIGN', $this->put('design'))
        ->set('REF_RAYON', $this->put('ref_rayon'))
        ->set('REF_SHIPPING', $this->put('ref_shipping'))
        ->set('REF_CATEGORIE', $this->put('ref_categorie'))
        ->set('QUANTITY', $this->put('quantity'))
        ->set('SKU', $this->put('sku'))
        ->set('QUANTITE_RESTANTE', $this->put('quantite_restante'))
        ->set('QUANTITE_VENDUE', $this->put('quantite_vendue'))
        ->set('DEFECTUEUX', $this->put('defectueux'))
        ->set('PRIX_DACHAT', $this->put('prix_dachat'))
        ->set('FRAIS_ACCESSOIRE', $this->put('frais_accessoire'))
        ->set('COUT_DACHAT', $this->put('cout_dachat'))
        ->set('TAUX_DE_MARGE', $this->put('taux_de_marge'))
        ->set('PRIX_DE_VENTE', $this->put('prix_de_vente'))
        ->update( store_prefix() . 'nexo_articles');

        if ($request) {
            $this->response(array(
                'status'        =>        'success'
            ), 200);
        } else {
            $this->response(array(
                'status'        =>        'error'
            ), 404);
        }
    }

    /**
     * Item insert
    **/

    public function item_post()
    {
        $request    =    $this->db
        ->set('DESIGN', $this->put('design'))
        ->set('REF_RAYON', $this->put('ref_rayon'))
        ->set('REF_SHIPPING', $this->put('ref_shipping'))
        ->set('REF_CATEGORIE', $this->put('ref_categorie'))
        ->set('QUANTITY', $this->put('quantity'))
        ->set('SKU', $this->put('sku'))
        ->set('QUANTITE_RESTANTE', $this->put('quantite_restante'))
        ->set('QUANTITE_VENDUE', $this->put('quantite_vendue'))
        ->set('DEFECTUEUX', $this->put('defectueux'))
        ->set('PRIX_DACHAT', $this->put('prix_dachat'))
        ->set('FRAIS_ACCESSOIRE', $this->put('frais_accessoire'))
        ->set('COUT_DACHAT', $this->put('cout_dachat'))
        ->set('TAUX_DE_MARGE', $this->put('taux_de_marge'))
        ->set('PRIX_DE_VENTE', $this->put('prix_de_vente'))
        ->insert( store_prefix() . 'nexo_articles');

        if ($request) {
            $this->response(array(
                'status'        =>        'success'
            ), 200);
        } else {
            $this->response(array(
                'status'        =>        'error'
            ), 404);
        }
    }

	/**
	 * Item by collection
	 *
	 * @param int collection id
	 * @return json
	**/

	public function item_by_collection_get( $collection_id )
	{
		$this->response(
		$this->db->select( '*,
		' . store_prefix() . 'nexo_categories.NOM as CAT_NAME' )
		->from( store_prefix() . 'nexo_articles' )
		->join( store_prefix() . 'nexo_categories', store_prefix() . 'nexo_articles.REF_CATEGORIE = ' . store_prefix() . 'nexo_categories.ID', 'inner' )
		->where( 'REF_SHIPPING', $collection_id )
		->get()->result() );
	}

    /**
     *  Import Item from CSV
     *  @return json
    **/

    public function import_csv_post()
    {
        if( $this->post( 'ext' ) == 'csv' ) {
            $inputFileName          =   APPPATH . 'temp/sample.csv';
            file_put_contents( $inputFileName, $this->post( 'csv' ) );

            switch( $this->post( 'separator' ) ) {
                case 'coma' : $separator    = ','; break;
                case 'dotcoma' : $separator = ';'; break;
                default: $separator = ','; break;
            }

            $csv_reader         =   new SimpleExcel( 'CSV' );
            $csv_reader->parser->setDelimiter( $separator );
            $csv_reader->parser->loadFile( $inputFileName );

            $data               =   $csv_reader->parser->getField();
            $cols               =   $this->post( 'cols' );

            unset( $data[0] );
            // unset( $cols[0] );

            $finalData      =   array();
            $categories     =   array();

            foreach( $data as $entry ) {
                $currentArray       =   array();
                foreach( $cols as $key => $col ) {
                    if( !empty( $col ) ) {
                        // Get Categorie
                        if( in_array( $col, array( 'REF_CATEGORIE' ) ) ) {
                            if( ! in_array( $entry[ $key ], $categories ) && $col == 'REF_CATEGORIE' ) {
                                $categories[] = $entry[ $key ];
                            }
                        }

                        //
                        if( in_array( $col, array( 'REF_CATEGORIE', 'BARCODE' ) ) ) {
                            $currentArray[ $col ]   =   $entry[ $key ] == '' ?  null : $entry[ $key ];
                        } else {
                            $currentArray[ $col ]   =   $entry[ $key ];
                        }
                    }
                }

                // Category is requried
                if( ! in_array( 'REF_CATEGORIE', $cols ) ) {
                    $currentArray[ 'REF_CATEGORIE' ] =   1; // default category
                }

                $finalData[]    =   $currentArray;
            }

            return $this->response( array(
                'categories'    =>  array_filter( $categories ),
                'items'          =>  $finalData
            ));
        }
    }

    /**
     * Stock Adjustment
     * @return json
    **/

    public function item_stock_post()
    {
        // get current item stock
        $item       =   $this->db->where( 'CODEBAR', $this->post( 'item_barcode' ) )->get( store_prefix() . 'nexo_articles' )->result_array();
        // required
        if( $this->post( 'item_barcode' ) != null && $this->post( 'item_qte' ) != null && $this->post( 'type' ) != null && $item ) {

            // Now increase the current stock of the item
            if( in_array( $this->post( 'type' ), [ 'defective', 'adjustment' ] ) ) {
                $remaining_qte      =   intval( $item[0][ 'QUANTITE_RESTANTE' ] ) - intval( $this->post( 'item_qte' ) );
            } else if( in_array( $this->post( 'type' ), [ 'supply' ] )) { // 'usable' is only used by the refund feature
                $remaining_qte      =   intval( $item[0][ 'QUANTITE_RESTANTE' ] ) + intval( $this->post( 'item_qte' ) );
            }

            if( $remaining_qte < 0 ) {
                return $this->__failed();
            }
            
            $this->db->insert( store_prefix() . 'nexo_articles_stock_flow', [
                'REF_ARTICLE_BARCODE'   =>  $this->post( 'item_barcode' ),
                'QUANTITE'              =>  $this->post( 'item_qte' ),
                'BEFORE_QUANTITE'       =>  $item[0][ 'QUANTITE_RESTANTE' ],
                'AFTER_QUANTITE'        =>  $remaining_qte,
                'DATE_CREATION'         =>  date_now(),
                'AUTHOR'                =>  User::id(),
                'TYPE'                  =>  $this->post( 'type' ), // defective, usable, supply, adjustment, import
                'UNIT_PRICE'            =>  ( float ) $this->post( 'unit_price' ),
                'TOTAL_PRICE'           =>  ( float ) $this->post( 'unit_price' ) * ( int ) $this->post( 'item_qte' ),
                'DESCRIPTION'           =>  $this->post( 'description' ) == null ? '' : $this->post( 'description' ),
                'REF_PROVIDER'          =>  $this->post( 'ref_provider' ) == null ? '' : $this->post( 'ref_provider' ),
                'REF_SHIPPING'          =>  $this->post( 'ref_shipping' ) == null ? '' : $this->post( 'ref_shipping' ),
            ]);

            $this->db->where( 'CODEBAR', $this->post( 'item_barcode' ) )->update( store_prefix() . 'nexo_articles', [
                'QUANTITE_RESTANTE'     =>  $remaining_qte
            ]);

            /**
             * let's calculate the stock summary to fix the 
             * value of the sotck taking
             * @since 3.12.13
             */
            $this->load->module_model( 'nexo', 'NexoStockTaking', 'StockTaking' );
            $this->StockTaking->refresh_stock_taking( $this->post( 'ref_shipping' ) );

            return $this->__success();
        }
        return $this->__failed();
    }

    /**
     * Get Item Stock
     * @param string barcode if the item
     * @param int id of the sypply
     * @return json
    **/
    public function item_stock_get( $barcode, $supply_id )
    {
        $stock_query    =   $this->db->select( '
        ' . store_prefix() . 'nexo_articles_stock_flow.DATE_CREATION as date,
        ' . store_prefix() . 'nexo_articles_stock_flow.ID as id,
        ' . store_prefix() . 'nexo_articles_stock_flow.QUANTITE as quantity,
        ' . store_prefix() . 'nexo_articles_stock_flow.TYPE as type,
        ' . store_prefix() . 'nexo_articles_stock_flow.REF_SHIPPING as shipping_id,
        ' . store_prefix() . 'nexo_articles.CODEBAR as codebar,
        ' . store_prefix() . 'nexo_articles.ID as item_id,
        ' . store_prefix() . 'nexo_articles.STOCK_ENABLED as stock_enabled,
        ' . store_prefix() . 'nexo_articles_stock_flow.DESCRIPTION as description,
        aauth_users.name as author' )
        ->from( store_prefix() . 'nexo_articles_stock_flow' )
        ->join( store_prefix() . 'nexo_articles', store_prefix() . 'nexo_articles.CODEBAR = ' . store_prefix() . 'nexo_articles_stock_flow.REF_ARTICLE_BARCODE' )
        ->join( 'aauth_users', 'aauth_users.id = ' . store_prefix() . 'nexo_articles_stock_flow.AUTHOR' )
        ->join( store_prefix() . 'nexo_arrivages', store_prefix() . 'nexo_articles_stock_flow.REF_SHIPPING = ' . store_prefix() . 'nexo_arrivages.ID' )
        ->where( store_prefix() . 'nexo_articles.CODEBAR', $barcode )
        ->where( store_prefix() . 'nexo_arrivages.ID', $supply_id )
        ->order_by( store_prefix() . 'nexo_articles_stock_flow.ID', 'desc' )
        ->get()->result();

        return $this->response( $stock_query, 200 );
    }

    /**
     * Item Bulk Supply Submit
     * @return json response
    **/

    public function bulk_supply_post()
    {
        if( is_array( $this->post( 'items' ) ) ) {
            $delivery_cost                  =   [];
            $provider_amount_due            =   [];
            $items                          =   $this->post( 'items' );
            $provider                       =   $this->post( 'ref_provider');

            /**
             * If the delivery name is provided. it's used in priority to create
             * a new delivery
             */
            if ( $this->post( 'new_delivery' ) != '' ) {
                $this->db->insert( store_prefix() . 'nexo_arrivages', [
                    'REF_PROVIDER'      =>  $provider[ 'ID' ],
                    'AUTHOR'            =>  User::id(),
                    'DATE_CREATION'     =>  date_now(),
                    'TITRE'             =>  $this->post( 'new_delivery' ) 
                ]);
    
                $shipping_id        =   $this->db->insert_id();
            } else {
                $selectedDelivery   =   $this->post( 'selected_delivery' );;
                $shipping_id        =   $selectedDelivery[ 'ID' ]; 
            }
            
            foreach( $this->post( 'items' ) as $item ) {
                // get current item stock
                $saved_item       =   $this->db->where( 'CODEBAR', $item[ 'item_barcode' ] )->get( store_prefix() . 'nexo_articles' )->result_array();
                // required
                if( @$item[ 'item_barcode' ] != null && @$item[ 'item_qte' ] != null ) {

                    // Now increase the current stock of the item
                    if( in_array( $item[ 'type' ], [ 'defective', 'adjustment' ] ) ) {
                        $remaining_qte      =   intval( $saved_item[0][ 'QUANTITE_RESTANTE' ] ) - intval( $item[ 'item_qte' ] );
                    } else if( in_array( $item[ 'type' ], [ 'supply' ] )) { // 'usable' is only used by the refund feature
                        $remaining_qte      =   intval( $saved_item[0][ 'QUANTITE_RESTANTE' ] ) + intval( $item[ 'item_qte' ] );
                    }

                    if( $remaining_qte < 0 ) {
                        break;
                    }
                    
                    $this->db->insert( store_prefix() . 'nexo_articles_stock_flow', [
                        'REF_ARTICLE_BARCODE'   =>  $item[ 'item_barcode' ],
                        'BEFORE_QUANTITE'       =>  $saved_item[0][ 'QUANTITE_RESTANTE' ],
                        'AFTER_QUANTITE'        =>  $remaining_qte,
                        'QUANTITE'              =>  $item[ 'item_qte' ],
                        'DATE_CREATION'         =>  date_now(),
                        'AUTHOR'                =>  User::id(),
                        'TYPE'                  =>  $item[ 'type' ], // defective, usable, supply, adjustment
                        'UNIT_PRICE'            =>  ( float ) $item[ 'unit_price' ],
                        'TOTAL_PRICE'           =>  ( float ) $item[ 'unit_price' ] * ( int ) $item[ 'item_qte' ],
                        // 'DESCRIPTION'           =>  $this->post( 'description' ) == null ? '' : $this->post( 'description' ),
                        'REF_PROVIDER'          =>  $item[ 'ref_provider' ],
                        'REF_SHIPPING'          =>  $shipping_id,
                        'PROVIDER_TYPE'         =>  'suppliers'
                    ]);

                    $updatable_columns          =   $this->events->apply_filters( 'items_columns_updatable_after_supply', [
                        $item, [
                            'QUANTITE_RESTANTE'     =>  $remaining_qte
                        ]
                    ]);

                    // use the updatable columns
                    $this->db->where( 'CODEBAR', $item[ 'item_barcode' ] )
                    ->update( store_prefix() . 'nexo_articles', $updatable_columns[1] );

                    // Calculating the delivery Cost
                    if( @$delivery_cost[ $shipping_id ] == null ) {
                        $delivery_cost[ $shipping_id ]       =   [
                            'cost'          =>  0,
                            'items'         =>  0,
                            'ref_provider'  =>  $item[ 'ref_provider' ]
                        ];
                    }

                    $item_cost          =   floatval( $item[ 'unit_price'] ) * floatval( $item[ 'item_qte' ] ) ;

                    $delivery_cost[ $shipping_id ][ 'cost' ]        +=   $item_cost;
                    $delivery_cost[ $shipping_id ][ 'items' ]       += floatval( $item[ 'item_qte' ] );

                    // update average price
                    $supplies       =   $this->db->where( 'REF_ARTICLE_BARCODE', $item[ 'item_barcode' ] )
                        ->get( store_prefix() . 'nexo_articles_stock_flow' )
                        ->result_array();
                    
                    $totalPurchase          =   0;
                    
                    foreach( $supplies as $supply ) {
                        $totalPurchase      +=  floatval( $item[ 'unit_price' ] );
                    }
                    
                    $averagePurchase        =   $totalPurchase / count( $supplies );
                    
                    $this->db->where( 'CODEBAR', $item[ 'item_barcode' ] )->update( store_prefix() . 'nexo_articles', [
                        'QUANTITE_RESTANTE'     =>  $remaining_qte,
                        'PRIX_DACHAT'           =>  $averagePurchase
                    ]);

                    // Save item cost to the supplier
                    if( store_option( 'enable_providers_account', 'no' ) == 'yes' ) {
                        if( @$provider_amount_due[ $item[ 'ref_provider' ] ] == null ) {
                            $provider_amount_due[ $item[ 'ref_provider' ] ]     =   [
                                'cost'          =>  0,
                                'supply_id'     =>  $shipping_id
                            ];
                        }

                        $provider_amount_due[ $item[ 'ref_provider' ] ][ 'cost' ]  +=    $item_cost;
                    }
                }
            }

            if( store_option( 'enable_providers_account', 'no' ) == 'yes' ) {
                // update amount due
                foreach( $provider_amount_due as $provider => $data ) {
                    $currentProvider    =   $this->db->where( 'ID', $provider )
                    ->get( store_prefix() . 'nexo_fournisseurs' )
                    ->result_array();
    
                    // loop amount
                    $currentAmountDue       =   floatval( $currentProvider[0][ 'PAYABLE' ] );
                    $transactionAmount      =   $data[ 'cost' ];
                    $currentAmountDue       +=  $transactionAmount;
    
                    // Update customer payable.
                    $this->db->where( 'ID', $provider )->update( store_prefix() . 'nexo_fournisseurs', [
                        'PAYABLE'   =>  $currentAmountDue
                    ]);

                    // add it as an history
                    $this->db->insert( store_prefix() . 'nexo_fournisseurs_history', [
                        'REF_PROVIDER'      =>  $provider,
                        'REF_SUPPLY'        =>  $data[ 'supply_id' ], // @since 3.10.0
                        'TYPE'              =>  'stock_purchase',
                        'BEFORE_AMOUNT'     =>  $currentProvider[0][ 'PAYABLE' ],
                        'AMOUNT'            =>  $transactionAmount,
                        'AFTER_AMOUNT'      =>  $currentAmountDue,
                        'DATE_CREATION'     =>  date_now(),
                        'DATE_MOD'          =>  date_now(),
                        'AUTHOR'            =>  User::id()
                    ]);
                }
            }

            // Update new values
            foreach( $delivery_cost as $delivery_id => $data ) {
                $this->db->where( store_prefix() . 'nexo_arrivages.ID', $delivery_id )->update( store_prefix() . 'nexo_arrivages', [
                    'ITEMS'         =>  $data[ 'items' ],
                    'VALUE'         =>  $data[ 'cost' ]
                ]);
            }
            
            return $this->response([
                'shipping_id'   =>  $shipping_id
            ]);
        }
        return $this->__failed();
    }

    /**
     * Get Item history
     * @param string barcode
     * @return json 
    **/

    public function history_get( $barcode )
    {
        $items_all        =   $this->db->select(
            store_prefix() . 'nexo_articles.DESIGN as ITEM_NAME,'
        .   store_prefix() . 'nexo_articles.QUANTITE_RESTANTE as REMAINING,'
        .   store_prefix() . 'nexo_articles.QUANTITY as INITIAL_QUANTITY,'
        .   store_prefix() . 'nexo_articles.PRIX_DE_VENTE as SALE_PRICE,'
        .   store_prefix() . 'nexo_articles.PRIX_DE_VENTE_TTC as SALE_PRICE_TI,' // tax included
        .   store_prefix() . 'nexo_articles_stock_flow.QUANTITE as SUPPLY_QUANTITY,'
        .   store_prefix() . 'nexo_articles_stock_flow.BEFORE_QUANTITE as BEFORE_QUANTITE,'
        .   store_prefix() . 'nexo_articles_stock_flow.AFTER_QUANTITE as AFTER_QUANTITE,'
        .   store_prefix() . 'nexo_articles_stock_flow.UNIT_PRICE as SUPPLY_PRICE,'
        .   store_prefix() . 'nexo_articles_stock_flow.TYPE as TYPE,'
        .   store_prefix() . 'nexo_articles_stock_flow.TOTAL_PRICE as SUPPLY_TOTAL_PRICE' )
        ->from( store_prefix() . 'nexo_articles' )
        ->join( store_prefix() . 'nexo_articles_stock_flow', store_prefix() . 'nexo_articles.CODEBAR = ' . store_prefix() . 'nexo_articles_stock_flow.REF_ARTICLE_BARCODE', 'left' )
        ->where( store_prefix() . 'nexo_articles.CODEBAR', $barcode )
        ->get()->result();

        $items          =   $this->db->select(
            store_prefix() . 'nexo_articles.DESIGN as name,'
        .   store_prefix() . 'nexo_articles.QUANTITE_RESTANTE as remaining_qte,'
        .   store_prefix() . 'nexo_articles.QUANTITY as initial_qte,'
        .   store_prefix() . 'nexo_articles.PRIX_DE_VENTE as sale_price,'
        .   store_prefix() . 'nexo_articles.PRIX_DE_VENTE_TTC as sale_price_ti,' // tax included
        .   store_prefix() . 'nexo_articles_stock_flow.QUANTITE as quantity,'
        .   store_prefix() . 'nexo_articles_stock_flow.BEFORE_QUANTITE as before_quantity,'
        .   store_prefix() . 'nexo_articles_stock_flow.AFTER_QUANTITE as after_quantity,'
        .   store_prefix() . 'nexo_articles_stock_flow.UNIT_PRICE as price,'
        .   store_prefix() . 'nexo_articles_stock_flow.TYPE as type,'
        .   store_prefix() . 'nexo_articles_stock_flow.DATE_CREATION as date,'
        .   store_prefix() . 'nexo_articles_stock_flow.AUTHOR as author_id,'
        . 'aauth_users.name as author_name,'
        .   store_prefix() . 'nexo_articles_stock_flow.TOTAL_PRICE as total_price' )
        ->from( store_prefix() . 'nexo_articles' )
        ->join( store_prefix() . 'nexo_articles_stock_flow', store_prefix() . 'nexo_articles.CODEBAR = ' . store_prefix() . 'nexo_articles_stock_flow.REF_ARTICLE_BARCODE', 'left' )
        ->join( 'aauth_users', 'aauth_users.id = ' . store_prefix() . 'nexo_articles_stock_flow.AUTHOR' )
        ->where( store_prefix() . 'nexo_articles.CODEBAR', $barcode )
        ->order_by( store_prefix() . 'nexo_articles_stock_flow.ID', 'desc' )
        ->limit( $this->get( 'limit' ), $this->get( 'page' ) )
        ->get()->result();

        $this->response([
            'entries'   =>  count( $items_all ),
            'items'     =>  $items
        ]);
    }
}
