<?php
class Transfert_model extends Tendoo_Module
{
    /**
     * Stock Transfert
     * @param int stock id
     * @return array transfert array
    **/

    public function get( $id = null )
    {
        if( $id != null ) {
            $this->db->where( 'ID', $id );
        } 
        $stock  =   $this->db->get( 'nexo_stock_transfert' )->result_array();
        return $stock;
    }

    /** 
     * Get Transfert Items
     * @param int transfert id
     * @return array
    **/

    public function get_with_items( $transfert_id ) 
    {
        return $this->db->where( 'REF_TRANSFER', $transfert_id )
        ->get( 'nexo_stock_transfert_items' )
        ->result_array();
    }

    /**
     * Send Email for a stock
     */
    private function __sendEmail( $data )
    {
        extract( $data );
        /**
         * Extract : 
         * -> transfert,
         * -> description,
         * -> items
         * -> status
         * -> transfert_id
         * -> transfert_details
         */
        if ( store_option( 'enable_email_notification' ) === 'yes' ) {
            $this->load->module_model( 'nexo', 'NexoEmailModel' );
            $details        =   [
                'transfert'         =>  $transfert[0],
                'items'             =>  $items,
                'email_from'        =>  $transfert[0][ 'FROM_STORE' ] !== '0' ? get_option( 'store_' . $transfert[0][ 'FROM_STORE' ] . '_notification_receiver_email' ) : get_option( 'notification_receiver_email' ),
                'email_to'          =>  $transfert[0][ 'DESTINATION_STORE' ] !== '0' ? get_option( 'store_' . $transfert[0][ 'DESTINATION_STORE' ] . '_notification_receiver_email' ) : get_option( 'notification_receiver_email' ),
                'status'            =>  $status,
                'description'       =>  $description,
                'domain'            =>  base_url(),
                'currency'          =>  $transfert[0][ 'FROM_STORE' ] !== '0' ? get_option( 'store_' . $transfert[0][ 'DESTINATION_STORE' ] . '_nexo_currency' )            : get_option( 'nexo_currency' ),
                'currency_position' =>  $transfert[0][ 'FROM_STORE' ] !== '0' ? get_option( 'store_' . $transfert[0][ 'DESTINATION_STORE' ] . '_nexo_currency_position' )   : get_option( 'nexo_currency_position' ),
                'to_stock_history_url' =>  $transfert[0][ 'DESTINATION_STORE' ] !== '0' ? 
                    site_url([ 'dashboard', 'stores', $transfert[0][ 'DESTINATION_STORE' ], 'nexo', 'transfert' ]) :
                    site_url([ 'dashboard', 'nexo', 'transfert' ]),
                'from_stock_history_url' =>  $transfert[0][ 'FROM_STORE' ] !== '0' ? 
                    site_url([ 'dashboard', 'stores', $transfert[0][ 'DESTINATION_STORE' ], 'nexo', 'transfert' ]) :
                    site_url([ 'dashboard', 'nexo', 'transfert' ]),
                'store_from_name'   =>  $transfert[0][ 'FROM_STORE' ] !== '0' ? get_option( 'store_' . $transfert[0][ 'FROM_STORE' ] . '_site_name' ) : get_option( 'site_name' ),
                'store_to_name'     =>  $transfert[0][ 'DESTINATION_STORE' ] !== '0' ? get_option( 'store_' . $transfert[0][ 'DESTINATION_STORE' ] . '_site_name' ) : get_option( 'site_name' )
            ];

            return $this->NexoEmailModel->send( $details );            
        }

        return [
            'statust'   =>  'failed',
            'message'   =>  __( 'The notification has been disabled from the settings.' )
        ];
    }

    /**
     * Update Transfert Status
     * @param int transfert id
     * @param int status: pending, approved, rejected, requested
     * @return void
    **/

    public function status( $transfert_id, $status, $description = '' ) 
    {
        $transfert          =   $this->get( $transfert_id );
        $transfert_details  =   [
            'STATUS'        =>  $status,
            'APPROUVED_BY'  =>  User::id()
        ];

        $emailResponse      =   [];
        $items              =   $this->get_with_items( $transfert_id );

        $emailResponse      =   $this->__sendEmail( compact( 
            'transfert', 
            'transfert_id', 
            'status', 
            'description', 
            'items',
            'transfert_details' 
        ) );

        /**
         * Let's save the response
         */
        switch( $status ) {
            case 'approved':
                $transfert_details[ 'TO_RESPONSE' ]     =   $description;
            break;
            case 'canceled':
                $transfert_details[ 'FROM_RESPONSE' ]   =   $description;
            break;
            case 'rejected':
                $transfert_details[ 'TO_RESPONSE' ]     =   $description;
            break;
        }

        $this->db->where( 'ID', $transfert_id )->update( 'nexo_stock_transfert', $transfert_details );

        return $emailResponse;
    }

    /**
     * Accept Transfert 
     * @param data transfert details
     * @return array
     */
    public function approve_transfert( $data )
    {
        /**
         * extract
         * ->transfert_id
         * ->description
         */
        extract( $data );

        $this->load->module_model( 'nexo', 'NexoProducts' );
        $this->load->module_model( 'nexo', 'NexoStockTaking' );

        $transfert      =   $this->get( $transfert_id );

        /**
         * If the request is being approved, it can be approved by the store applicant or 
         * the store sender. For both cases, the slug of the table should be updated accordingly.
         * For the request, the data should be created on the applicant store.
         */
        if ( $transfert[0][ 'STATUS' ] === 'requested' ) {
            $store_prefix   =   $transfert[0][ 'DESTINATION_STORE' ] === '0' ? '' : 'store_' . $transfert[0][ 'DESTINATION_STORE' ] . '_';
        } else {
            $store_prefix   =   store_prefix();
        }


        if ( empty( $transfert ) ) {
            return [
                'status'    =>  'failed',
                'message'   =>  __( 'Unable to locate the transfert.' )
            ];
        }

        $items      =   $this->get_with_items( $transfert_id );
        $allItems   =   $this->db->get( store_prefix() . 'nexo_articles' )
        ->result_array();

        $storeItems     =   [];
        foreach( $allItems as $item ) {
            $storeItems[ $item[ 'CODEBAR' ] ]   =   $item;
        }
        unset( $item );

        // get all items from source
        $slug                   =   intval( $transfert[0][ 'FROM_STORE' ] ) == 0 ? '' : 'store_' . intval( $transfert[0][ 'FROM_STORE' ] ) . '_';
        
        // get item details from orignal store
        $raw_items              =   $this->db->get( $slug . 'nexo_articles' )
        ->result_array();

        // source categories
        $raw_categories         =   $this->db->get( $slug . 'nexo_categories' )
        ->result_array();

        // current categories
        $raw_current_categories         =   $this->db->get( $store_prefix . 'nexo_categories' )
        ->result_array();

        $source_items_details           =   nexo_convert_raw( $raw_items, 'CODEBAR' );
        $source_categories_details      =   nexo_convert_raw( $raw_categories, 'ID' );
        $current_categories_details     =   nexo_convert_raw( $raw_current_categories, 'ID' );

        // var_dump( $source_items_details, $source_categories_details, $current_categories_details );
        $notFoundStock      =   [];
        $notEnoughtStock    =   [];

        // create shipping on the store receiver
        $this->db->insert( $store_prefix . 'nexo_arrivages', [
            'TITRE'             =>  $transfert[0][ 'TITLE' ],
            'DESCRIPTION'       =>  $transfert[0][ 'DESCRIPTION' ],
            'VALUE'             =>  0,
            'ITEMS'             =>  0,
            'DATE_CREATION'     =>  date_now(),
            'REF_PROVIDER'      =>  $transfert[0][ 'FROM_STORE' ],
            'PROVIDER_TYPE'     =>  'store',
            'AUTHOR'            =>  User::id() 
        ]);
        
        $delivery_id        =   $this->db->insert_id();

        foreach( $items as $item ) {
            // check if item exists on the current store otherwise create it
            $item_data              =   [];

            if( @$storeItems[ $item[ 'BARCODE' ] ] === null ) {
                // if item exist on the original store
                if( @$source_items_details[ $item[ 'BARCODE' ] ] != null ) {
                    /**
                     * the id should be omitted, specially if we're trying
                     * to create an image which has a already used ID.
                     * @since 3.12.14
                     */
                    $item_data                              =   $source_items_details[ $item[ 'BARCODE' ] ];
                    $item_data[ 'CODEBAR' ]                 =   $item[ 'BARCODE' ];
                    $item_data[ 'QUANTITE_RESTANTE' ]       =   0;
                    $item_data[ 'DESIGN' ]                  =   $item[ 'DESIGN' ];
                    $item_data[ 'AUTHOR' ]                  =   User::id();
                    $itemOnCurrentStore                     =   $item_data;
                    unset( $item_data[ 'ID' ] );

                    $this->db->insert( $store_prefix . 'nexo_articles', $item_data );

                } else {
                    // if item can't be transfered, save it as not found
                    $notFoundStock[]     =   $item;
                    continue;
                }            
            } else {
                // the item already exist then no need to create it. Just proceed.
                $itemExists         =   true;

                /**
                 * If the stock is actually being requested, we assume the current request
                 * is been proceessed from the warehouse. We should then use the actual item on the 
                 * applicant store to update the quantity before and after
                 */
                if ( $transfert[0][ 'STATUS' ] === 'requested' ) {
                    $itemOnCurrentStore     =   $this->NexoProducts->get( 
                        $item[ 'BARCODE' ], 
                        'CODEBAR', 
                        $transfert[0][ 'DESTINATION_STORE' ] 
                    );
                } else {
                    $itemOnCurrentStore     =   @$storeItems[ $item[ 'BARCODE' ] ];
                }

                $item_data          =   @$storeItems[ $item[ 'BARCODE' ] ];
            }

            if( ! empty( $item_data ) ) {
                // if destination store category don't exists
                // get the source category and create on the destination store
                // May need to get the complete hierarchy
                if( @$current_categories_details[ $item_data[ 'REF_CATEGORIE' ] ] == null ) {
                    if( @$source_categories_details[ $item_data[ 'REF_CATEGORIE' ] ] != null  ) {  
                        $this->db->insert( 
                            $store_prefix . 'nexo_categories', 
                            $source_categories_details[ $item_data[ 'REF_CATEGORIE' ] ] 
                        );
                    }
                }

                /**
                 * We need to check if the stock is enought before transfering
                 */
                $canTransfert       =   ( floatval( @$source_items_details[ $item[ 'BARCODE' ] ][ 'QUANTITE_RESTANTE' ] ) - floatval( $item[ 'QUANTITY' ] ) ) > 0;
                
                if ( ! $canTransfert ) {
                    $notEnoughtStock[]     =   $item;

                    /**
                     * Some the script here since there is not enought
                     * stock and save it so that it can be reported.
                     */
                    continue;
                }

                // Make a supply entry
                // store which receive item
                $this->db->insert( $store_prefix . 'nexo_articles_stock_flow', [
                    'REF_ARTICLE_BARCODE'           =>  $item[ 'BARCODE' ],
                    'BEFORE_QUANTITE'               =>  $itemOnCurrentStore[ 'QUANTITE_RESTANTE' ],
                    'QUANTITE'                      =>  $item[ 'QUANTITY' ],
                    'AFTER_QUANTITE'                =>  floatval( $itemOnCurrentStore[ 'QUANTITE_RESTANTE' ] ) + floatval( $item[ 'QUANTITY' ] ),
                    'DATE_CREATION'                 =>  date_now(),
                    'AUTHOR'                        =>  User::id(),
                    'REF_SHIPPING'                  =>  $delivery_id,
                    'TYPE'                          =>  'transfert_in',
                    'UNIT_PRICE'                    =>  $item[ 'UNIT_PRICE' ],
                    'TOTAL_PRICE'                   =>  $item[ 'TOTAL_PRICE' ],
                    'REF_PROVIDER'                  =>  $transfert[0][ 'FROM_STORE' ],
                    'PROVIDER_TYPE'                 =>  'store'
                ]);

                // Update item created on the destination store and update the remaining quantity
                $this->db->where( 'CODEBAR', $item[ 'BARCODE' ] )->update( $store_prefix . 'nexo_articles', [
                    'QUANTITE_RESTANTE'             => floatval( $itemOnCurrentStore[ 'QUANTITE_RESTANTE' ] ) + floatval( $item[ 'QUANTITY' ] )
                ]);

                // create the valid slug. which should be the source slug
                $slug   =   ( intval( $transfert[0][ 'FROM_STORE'] ) == 0 ) ? '' : 'store_' . $transfert[0][ 'FROM_STORE' ] . '_';

                // if transfert allow deduction
                if( @$transfert[0][ 'DEDUCT_FROM_SOURCE' ] == 'no' ) {
                    $item_details           =   $this->db->where( 'CODEBAR', $item[ 'BARCODE' ] )
                    ->get( $slug . 'nexo_articles' )
                    ->result_array();

                    // reduce from quantity
                    $this->db->where( 'CODEBAR', $item[ 'BARCODE' ] )->update( $slug . 'nexo_articles', [
                        'QUANTITE_RESTANTE'     =>      floatval( $item_details[0][ 'QUANTITE_RESTANTE' ] ) - floatval( $item[ 'QUANTITY' ] )
                    ]);

                    // Input in stock flow as transfer
                    $this->db->insert( $slug . 'nexo_articles_stock_flow', [
                        'BEFORE_QUANTITE'       =>      floatval( $item_details[0][ 'QUANTITE_RESTANTE' ] ),
                        'QUANTITE'              =>      floatval( $item[ 'QUANTITY' ] ),
                        'AFTER_QUANTITE'        =>      floatval( $item_details[0][ 'QUANTITE_RESTANTE' ] ) - floatval( $item[ 'QUANTITY' ] ),
                        'TYPE'                  =>      'transfert_out',
                        'UNIT_PRICE'            =>      $item[ 'UNIT_PRICE' ],
                        'TOTAL_PRICE'           =>      floatval( $item[ 'QUANTITY' ] ) * floatval( $item[ 'UNIT_PRICE' ] ),
                        'REF_ARTICLE_BARCODE'   =>      $item[ 'BARCODE' ],
                        'DATE_CREATION'         =>      date_now(),
                        'AUTHOR'                =>      User::id(),
                        'REF_PROVIDER'          =>      $transfert[0][ 'DESTINATION_STORE' ],
                        'PROVIDER_TYPE'         =>      'store'
                    ]);
                }

            }
        }

        $this->NexoStockTaking->refresh_stock_taking( $delivery_id );

        // update transfert status
        if ( $transfert[0][ 'STATUS' ] === 'requested' ) {
            $emailResponse  =   $this->status( $transfert_id, 'transfered', $description );
        } else {
            $emailResponse  =   $this->status( $transfert_id, 'approved', $description );
        }

        return $transfert[0][ 'STATUS' ] === 'requested' ? [
            'status'            =>  'success',
            'message'           =>  __( 'The stock request has successfully been transfered.', 'stock-manager' ),
            'not_found'         =>  $notFoundStock,
            'not_enought'       =>  $notEnoughtStock
        ] : [
            'status'            =>  'success',
            'message'           =>  __( 'The stock transfert has successfully been approved.', 'stock-manager' ),
            'not_found'         =>  $notFoundStock,
            'not_enought'       =>  $notEnoughtStock,
            'email'             =>  $emailResponse
        ];
    }

    /**
     * Reject Transfert
     * @param data
     * @return array
     */
    public function reject_transfert( $data )
    {
        /**
         * extract : 
         * -> transfert_id
         * -> description
         */
        extract( $data );

        $transfert   =   $this->get( $transfert_id );

        $from_prefix         =   $transfert[0][ 'FROM_STORE' ] == '0' ? '' : 'store_' . $transfert[0][ 'FROM_STORE' ] . '_';

        if( $transfert[0][ 'STATUS' ] === 'approved' ) {
            return [
                'status'    =>  'failed',
                'message'   =>  __( 'You cannot reject this transfert, it may have yet been approved or canceled.', 'stock-manager' )
            ];
        }

        // restrict access to FROM store
        if( intval( $transfert[0][ 'DESTINATION_STORE' ] ) != get_store_id() ) {
            return [
                'status'    =>  'failed',
                'message'   =>  __( 'Access Denied.', 'stock-manager' )
            ];
        }

        // if the stock hasn't been send, then we don't need to return anything, just change the transfert status.
        if( $transfert[0][ 'FROM_STORE'] == '0' ) {
            if( get_option( 'deduct_from_store', 'yes' ) == 'no' ) {
                // update transfert status
                $this->status( $transfert[0][ 'ID' ], 3 );

                // redirect with errors
                return redirect([ 'dashboard', store_slug(), 'stock-transfert', 'history?notice=cancel_done&errors=' . count( $failures )]);
            }
        } else {
            if( get_option( 'store_' . $transfert[0][ 'FROM_STORE' ] . '_deduct_from_store', 'yes' ) == 'no' ) {
                // update transfert status
                $this->status( $transfert[0][ 'ID' ], 3 );

                // redirect with errors
                return redirect([ 'dashboard', store_slug(), 'stock-transfert', 'history?notice=cancel_done&errors=' . count( $failures )]);
            }
        }

        $items      =   $this->get_with_items( $transfert_id );
        $allItems   =   $this->db->get( $from_prefix . 'nexo_articles' )
        ->result_array();

        $storeItems     =   [];
        foreach( $allItems as $item ) {
            $storeItems[ $item[ 'CODEBAR' ] ]   =   $item;
        }

        $this->db->insert( $from_prefix . 'nexo_arrivages', [
            'TITRE'             =>  sprintf( __( 'Rejecting Transfert : %s', 'stock-transfer' ), $transfert[0][ 'TITLE' ] ),
            'DESCRIPTION'       =>  $transfert[0][ 'DESCRIPTION' ],
            'VALUE'             =>  0,
            'ITEMS'             =>  0,
            'REF_PROVIDER'      =>  0, // we might add a store as a provider
            'DATE_CREATION'     =>  date_now(),
            'AUTHOR'            =>  User::id() 
        ]);

        $delivery_id        =   $this->db->insert_id();
        $supply_value       =   0;
        $supply_quantity    =   0;

        $failures       =   [];
        foreach( $items as $item ) {
            // check if item exists on the current store otherwise create it
            // Create Slug
            $slug                   =   intval( $transfert[0][ 'FROM_STORE' ] ) == 0 ? '' : 'store_' . get_store_id() . '_';
            $itemExists             =   false;

            if( @$storeItems[ $item[ 'BARCODE' ] ] == null ) {

                // get item details from orignal store
                $item_details           =   $this->db->where( 'CODEBAR', $item[ 'BARCODE' ] )
                ->get( $slug . 'nexo_articles' )
                ->result_array();

                // if item exist on the original store
                if( $item_details ) {
                    $itemExists                 =   true;
                    $this->db->insert( $from_prefix . 'nexo_articles', [
                        'CODEBAR'               =>  $item[ 'BARCODE' ],
                        'QUANTITE_RESTANTE'     =>  $item[ 'QUANTITY' ],
                        'DESIGN'                =>  $item[ 'DESIGN' ],
                        'REF_RAYON'             =>  $item_details[0][ 'REF_RAYON' ],
                        'REF_CATEGORIE'         =>  $item_details[0][ 'REF_CATEGORIE' ],
                        'SKU'                   =>  $item_details[0][ 'SKU' ],
                        'PRIX_DACHAT'           =>  $item_details[0][ 'PRIX_DACHAT' ],
                        'PRIX_DE_VENTE'         =>  $item_details[0][ 'PRIX_DE_VENTE' ],
                        'PRIX_DE_VENTE_TTC'     =>  $item_details[0][ 'PRIX_DE_VENTE_TTC' ],
                        'SHADOW_PRICE'          =>  $item_details[0][ 'SHADOW_PRICE' ],
                        'TAILLE'                =>  $item_details[0][ 'TAILLE' ],
                        'POIDS'                 =>  $item_details[0][ 'POIDS' ],
                        'DATE_CREATION'         =>  date_now()
                    ]);
                } else {
                    // if item can't be transfered, save it as failures
                    $failures[]     =   $item;
                    break;
                }            
            }            

            $item_details           =   $this->db->where( 'CODEBAR', $item[ 'BARCODE' ] )
            ->get( $from_prefix . 'nexo_articles' )
            ->result_array();

            // Make a supply entry
            // store which receive item
            $this->db->insert( $from_prefix . 'nexo_articles_stock_flow', [
                'REF_ARTICLE_BARCODE'           =>  $item[ 'BARCODE' ],
                'BEFORE_QUANTITE'               =>  $item_details[0][ 'QUANTITE_RESTANTE' ],
                'QUANTITE'                      =>  $item[ 'QUANTITY' ],
                'BEFORE_QUANTITE'               =>  floatval( $item_details[0][ 'QUANTITE_RESTANTE' ] ) + floatval( $item[ 'QUANTITY' ] ),
                'DATE_CREATION'                 =>  date_now(),
                'AUTHOR'                        =>  User::id(),
                'REF_SHIPPING'                  =>  $delivery_id,
                'TYPE'                          =>  'transfert_rejected',
                'UNIT_PRICE'                    =>  $item[ 'UNIT_PRICE' ],
                'TOTAL_PRICE'                   =>  $item[ 'TOTAL_PRICE' ],
            ]);

            $supply_value                   +=  floatval( $item[ 'TOTAL_PRICE' ] );
            $supply_quantity                +=  floatval( $item[ 'QUANTITY' ] );

            if( ! $itemExists ) {
                $this->db->where( 'CODEBAR', $item[ 'BARCODE' ] )->update( $from_prefix . 'nexo_articles', [
                    'QUANTITE_RESTANTE'             => floatval( $item_details[0][ 'QUANTITE_RESTANTE' ] ) + floatval( $item[ 'QUANTITY' ] )
                ]);
            }                
        }

        // update delivery item quantity
        $this->db->where( 'ID', $delivery_id )->update( $from_prefix . 'nexo_arrivages', [
            'VALUE'         =>  $supply_value,
            'ITEMS'         =>  $supply_quantity
        ]);

        // update transfert status
        $this->status( $transfert_id, 'rejected', $description ); // mean the transferst has been rejected

        return [
            'status'    =>  'success',
            'message'   =>  __( 'The transfert has been rejected', 'stock-manager' ),
            'failures'  =>  $failures
        ];
    }

    /**
     * Cancel Transfert
     * @param array data
     * @return array response
     */
    public function cancel_transfert( $data )
    {
        extract( $data );
        /**
         * extract
         * -> description
         * -> transfert_id
         */
        $transfert   =   $this->get( $transfert_id );

        if( $transfert[0][ 'STATUS' ] === 'approved' ) {
            return [
                'status'    =>  'failed',
                'message'   =>  __( 'You cannot cancel this transfert, it may have yet been approved.', 'stock-manager' )
            ];
        }

        // restrict access to FROM store
        if( intval( $transfert[0][ 'FROM_STORE' ] ) != get_store_id() ) {
            return [
                'status'    =>  'failed',
                'message'   =>  __( 'Access Denied.', 'stock-manager' )
            ];
        }

        /**
         * This array hold all the failures 
         * (transfert which has failed)
         */
        $failures       =   [];

        /**
         * Let's determine if it's a request
         * because the request, should restore hold item 
         * for the transfert. Request only change the status of the query
         */
        $isRequest      =   $transfert[0][ 'STATUS' ]   === 'request' ? true : false;
        
        if ( ! $isRequest ) {
            // if the stock hasn't been send, then we don't need to return anything, just change the transfert status.
            if( $transfert[0][ 'DEDUCT_FROM_SOURCE' ] == 'no' ) {
                // update transfert status
                $this->status( $transfert_id, 'canceled', $description );
    
                // redirect with errors
                return redirect([ 'dashboard', store_slug(), 'stock-transfert', 'history?notice=cancel_done&errors=' . count( $failures )]);
            }

            $items      =   $this->get_with_items( $transfert_id );
            $allItems   =   $this->db->get( store_prefix() . 'nexo_articles' )
            ->result_array();
    
            $storeItems     =   [];
            foreach( $allItems as $item ) {
                $storeItems[ $item[ 'CODEBAR' ] ]   =   $item;
            }
    
            $this->db->insert( store_prefix() . 'nexo_arrivages', [
                'TITRE'             =>  sprintf( __( 'Canceling Transfert : %s', 'stock-transfer' ), $transfert[0][ 'TITLE' ] ),
                'DESCRIPTION'       =>  $transfert[0][ 'DESCRIPTION' ],
                'VALUE'             =>  0,
                'ITEMS'             =>  0,
                'REF_PROVIDER'      =>  0, // we should at a store as a provider
                'DATE_CREATION'     =>  date_now(),
                'AUTHOR'            =>  User::id() 
            ]);
    
            $delivery_id        =   $this->db->insert_id();
            $supply_value       =   0;
            $supply_quantity    =   0;

            foreach( $items as $item ) {
                // check if item exists on the current store otherwise create it
                // Create Slug
                $slug                   =   intval( $transfert[0][ 'FROM_STORE' ] ) == 0 ? '' : 'store_' . get_store_id() . '_';
                $itemExists             =   false;
    
                if( @$storeItems[ $item[ 'BARCODE' ] ] == null ) {
    
                    // get item details from orignal store
                    $item_details           =   $this->db->where( 'CODEBAR', $item[ 'BARCODE' ] )
                    ->get( $slug . 'nexo_articles' )
                    ->result_array();
    
                    // if item exist on the original store
                    if( $item_details ) {
                        $itemExists                 =   true;
                        $this->db->insert( store_prefix() . 'nexo_articles', [
                            'CODEBAR'               =>  $item[ 'BARCODE' ],
                            'QUANTITE_RESTANTE'     =>  $item[ 'QUANTITY' ],
                            'DESIGN'                =>  $item[ 'DESIGN' ],
                            'REF_RAYON'             =>  $item_details[0][ 'REF_RAYON' ],
                            'REF_CATEGORIE'         =>  $item_details[0][ 'REF_CATEGORIE' ],
                            'SKU'                   =>  $item_details[0][ 'SKU' ],
                            'PRIX_DACHAT'           =>  $item_details[0][ 'PRIX_DACHAT' ],
                            'PRIX_DE_VENTE'         =>  $item_details[0][ 'PRIX_DE_VENTE' ],
                            'PRIX_DE_VENTE_TTC'     =>  $item_details[0][ 'PRIX_DE_VENTE_TTC' ],
                            'SHADOW_PRICE'          =>  $item_details[0][ 'SHADOW_PRICE' ],
                            'TAILLE'                =>  $item_details[0][ 'TAILLE' ],
                            'POIDS'                 =>  $item_details[0][ 'POIDS' ],
                            'DATE_CREATION'         =>  date_now()
                        ]);
                    } else {
                        // if item can't be transfered, save it as failures
                        $failures[]     =   $item;
                        break;
                    }            
                }            
    
                $item_details           =   $this->db->where( 'CODEBAR', $item[ 'BARCODE' ] )
                ->get( store_prefix() . 'nexo_articles' )
                ->result_array();
    
                // Make a supply entry
                // store which receive item
                $this->db->insert( store_prefix() . 'nexo_articles_stock_flow', [
                    'REF_ARTICLE_BARCODE'           =>  $item[ 'BARCODE' ],
                    'BEFORE_QUANTITE'               =>  $item_details[0][ 'QUANTITE_RESTANTE' ],
                    'QUANTITE'                      =>  $item[ 'QUANTITY' ],
                    'BEFORE_QUANTITE'               =>  floatval( $item_details[0][ 'QUANTITE_RESTANTE' ] ) + floatval( $item[ 'QUANTITY' ] ),
                    'DATE_CREATION'                 =>  date_now(),
                    'AUTHOR'                        =>  User::id(),
                    'REF_SHIPPING'                  =>  $delivery_id,
                    'TYPE'                          =>  'transfert_canceled',
                    'UNIT_PRICE'                    =>  $item[ 'UNIT_PRICE' ],
                    'TOTAL_PRICE'                   =>  $item[ 'TOTAL_PRICE' ],
                ]);
    
                $supply_value                   +=  floatval( $item[ 'TOTAL_PRICE' ] );
                $supply_quantity                +=  floatval( $item[ 'QUANTITY' ] );
    
                if( ! $itemExists ) {
                    $this->db->where( 'CODEBAR', $item[ 'BARCODE' ] )->update( store_prefix() . 'nexo_articles', [
                        'QUANTITE_RESTANTE'             => floatval( $item_details[0][ 'QUANTITE_RESTANTE' ] ) + floatval( $item[ 'QUANTITY' ] )
                    ]);
                }                
            }
    
            // update delivery item quantity
            $this->db->where( 'ID', $delivery_id )->update( store_prefix() . 'nexo_arrivages', [
                'VALUE'         =>  $supply_value,
                'ITEMS'         =>  $supply_quantity
            ]);
        }

        // update transfert status
        $emailResponse  =   $this->status( $transfert_id, 'canceled', $description );

        return [
            'status'    =>  'success',
            'message'   =>  __( 'Translate the transfert has been canceled', 'stock-manager' ),
            'failures'  =>  $failures,
            'email'     =>  $emailResponse
        ];
    }

    /**
     * Transfert Model
     * @return void
     */
    public function transfert( $data )
    {
        extract( $data );
        /**
         * Extract
         * ->title
         * ->store
         * ->items
         * ->is_request
         * ->description
         */
        $this->db->insert( 'nexo_stock_transfert', [
            'TITLE'                 =>  $title,
            'FROM_STORE'            =>  $data[ 'is_request' ] ? $store[ 'ID' ] : get_store_id(),
            'DESTINATION_STORE'     =>  $data[ 'is_request' ] ? get_store_id() : $store[ 'ID' ],
            'AUTHOR'                =>  User::id(),
            'TYPE'                  =>  'supply',
            'DATE_CREATION'         =>  date_now(),
            'STATUS'                =>  $data[ 'is_request' ] ? 'requested' : 'pending',
            'DEDUCT_FROM_SOURCE'    =>  $data[ 'is_request' ] ? 'no' : store_option( 'deduct_from_store', 'yes' )
        ]);

        $transfert_id                =   $this->db->insert_id();
        $updatedItems               =   [];

        foreach( $items as  $item ) {

            $singleItem             =   [
                'DESIGN'        =>  $item[ 'DESIGN' ],
                'QUANTITY'      =>  $item[ 'QTE_ADDED' ],
                'BARCODE'       =>  $item[ 'CODEBAR' ],
                'SKU'           =>  $item[ 'SKU' ],
                'DATE_CREATION' =>  date_now(),
                'REF_TRANSFER'  =>  $transfert_id,
                'UNIT_PRICE'    =>  $item[ 'PRIX_DACHAT' ],
                'TOTAL_PRICE'   =>  floatval( $item[ 'PRIX_DACHAT' ] ) * floatval( $item[ 'QTE_ADDED' ] )
            ];
            $updatedItems[]             =   $singleItem;
            // make sure to save the tranfert at the right place
            $this->db->insert( 'nexo_stock_transfert_items', $singleItem );

            // reduce stock from main warehouse
            if( ! $data[ 'is_request' ] && store_option( 'deduct_from_store', 'yes' ) == 'yes' ) {
                // reduce from quantity
                $this->db->where( 'CODEBAR', $item[ 'CODEBAR' ] )->update( 
                    store_prefix() . 'nexo_articles', [
                    'QUANTITE_RESTANTE'     =>      floatval( $item[ 'QUANTITE_RESTANTE' ] ) - floatval( $item[ 'QTE_ADDED' ] )
                ]);

                // Input in stock flow as transfer
                $this->db->insert( store_prefix() . 'nexo_articles_stock_flow', [
                    'BEFORE_QUANTITE'       =>      floatval( $item[ 'QUANTITE_RESTANTE' ] ),
                    'QUANTITE'              =>      floatval( $item[ 'QTE_ADDED' ] ),
                    'AFTER_QUANTITE'        =>      floatval( $item[ 'QUANTITE_RESTANTE' ] ) - floatval( $item[ 'QTE_ADDED' ] ),
                    'TYPE'                  =>      'transfert_out',
                    'UNIT_PRICE'            =>      $item[ 'PRIX_DACHAT' ],
                    'TOTAL_PRICE'           =>      floatval( $item[ 'QTE_ADDED' ] ) * floatval( $item[ 'PRIX_DACHAT' ] ),
                    'REF_ARTICLE_BARCODE'   =>      $item[ 'CODEBAR' ],
                    'DATE_CREATION'         =>      date_now(),
                    'AUTHOR'                =>      User::id()
                ]);
            } 
        }   

        /**
         * Send an email for the current stock request/send
         */
        $emailResponse      =   $this->__sendEmail([
            'transfert'     =>  $this->get( $transfert_id ),
            'status'        =>  $data[ 'is_request' ] ? 'requested' : 'pending',
            'description'   =>  null, // no description while requesting or sending stock
            'items'         =>  $updatedItems
        ]);

        return [
            'status'        =>  'success',
            'message'       =>  $data[ 'is_request' ] ? 
                __( 'The stock has been successfully requested', 'stock-manager' ) :
                __( 'The stock has been successfully send', 'stock-manager' ),
            'transfert_id'  =>  $transfert_id,
            'email'         =>  $emailResponse
        ];
    }

    /**
     * Verify a stock before proceeding
     * @return json response
     */
    public function verify( $data )
    {
        $this->load->model( 'Nexo_Products' );
        extract( $data );
        /**
         * ->transfert_id
         * ->description
         */


        $transfert      =   $this->get( $transfert_id );
        $items          =   $this->get_with_items( $transfert_id );

        if( $transfert[0][ 'STATUS' ] === 'requested' ) {
            $hasFoundItems      =   false;
            $exhaustedItems     =   [];
            foreach( $items as $item ) {
                $fullItem   =   $this->Nexo_Products->get_single( $item[ 'BARCODE' ], 'CODEBAR' );
                if( ! empty( $fullItem ) ) {
                    /**
                     * We need to check if requested items already exist on the stock.
                     */
                    $hasFoundItems      =   true;

                    $stockCalculation   =   floatval( $fullItem[ 'QUANTITE_RESTANTE' ] ) - floatval( $item[ 'QUANTITY' ] );
                    if ( $stockCalculation < 0 ) {
                        $exhaustedItems[]   =   [
                            'item'      =>  $item,
                            'full'      =>  $fullItem,
                            'stock'     =>  $fullItem[ 'QUANTITE_RESTANTE' ],
                            'missing'   =>  $stockCalculation,
                            'requested' =>  $item[ 'QUANTITY' ],
                        ];
                    }
                }
            }

            /**
             * We've not found any item. We should
             * notify that the request can't proceed.
             */
            if ( ! $hasFoundItems ) {
                return [
                    'status'    => 'failed',
                    'message'   =>  __( 'The requested stock doesn\'t seem to exist on your warehouse', 'stock-manager' )
                ];
            }

            /**
             * Not enought stock to transfert
             */
            if ( count( $exhaustedItems ) === count( $items ) ) {
                return [
                    'status'    =>  'failed',
                    'message'   =>  __( 'Unable to transfert the stock. The available stock is not enought to proceed to the request.', 'stock-manager' )
                ];
            }

            /**
             * Does the current request has 
             * exhausted stock ? 
             */
            if ( ! empty( $exhaustedItems ) ) {
                return [
                    'status'        =>  'info',
                    'message'       =>  __( 'Some items requested are exhausted on your store. Only available items will be proceessed. Would you like to proceed ?', 'stock-manager' ),
                    'items'         =>  $exhaustedItems,
                    'transfert_id'  =>  $transfert_id
                ];
            }

            /**
             * Everything was successful. Let's notify the user.
             */
            return [
                'status'            =>  'success',
                'message'           =>  __( 'The stock request can be proceeded. All items requested are available on the warehouse.', 'stock-manager' ),
                'transfert_id'      =>  $transfert_id
            ];
        }

        return [
            'status'    =>  'failed',
            'message'   =>  __( 'Unable to check a request which state has changed.' )
        ];
    }

    /**
     * Proceed to stock request
     * which mean sending stock to the applicant
     * @return array
     */
    public function proceessStockRequest( $data )
    {
        return $this->approve_transfert( $data );
    }
}