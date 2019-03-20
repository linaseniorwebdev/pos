<?php

use Carbon\Carbon;
use Curl\Curl;

trait Nexo_orders
{
    /**
     * Get Order
     * @param string/int
     * @param string
     * @return json
    **/

    public function order_get($var = null, $filter = 'ID')
    {
        if ($var != null) {
            $this->db->where($filter, $var);
        }
        $query    =    $this->db->get( store_prefix() . 'nexo_commandes');
        $this->response($query->result(), 200);
    }

    /**
     * Post Order
     * @param int Author id
     * @return json
    **/

    public function order_post($author_id)
    {
        $this->load->model( 'Nexo_Checkout' );
        $return     =   $this->Nexo_Checkout->postOrder( $this->post(), $author_id );
        extract( $return );

        if( @$return[ 'status' ] == 'failed' ) {
            $this->response( $return );
        }

        // filter post response
        $this->response( $this->events->apply_filters( 'post_order_response', array(
            'order_id'          =>    $current_order[0][ 'ID' ],
            'order_type'        =>    $order_details[ 'TYPE' ],
            'order_code'        =>    $current_order[0][ 'CODE' ]
        ), [
            'order_details'     =>  $order_details,
            'current_order'     =>  $current_order[0]
        ]), 200 );
    }

    /**
     * Update Order
     * @param int Author id
     * @param int order id
     * @return json
    **/

    public function order_put($author_id, $order_id)
    {
        $this->load->module_model( 'nexo', 'NexoRewardSystemModel', 'reward_model' );
        $this->load->model('Nexo_Checkout');
        $this->load->model('Options');
        // Get old order details with his items
        $old_order              =    $this->Nexo_Checkout->get_order_products($order_id, true);

        $old_customer           =   $this->db
            ->where('ID',  $old_order['order'][0][ 'REF_CLIENT' ])
            ->get( store_prefix() . 'nexo_clients')
            ->result_array();

        $current_order          =    $this->db->where('ID', $order_id)
            ->get( store_prefix() . 'nexo_commandes')
            ->result_array();

        if( ! $current_order ) {
            return response()->httpCode( 403 )->json(array(
                'message'   =>  __( 'Impossible de retrouver la commande dans la base de données. Il est probable que la commande n\'existe plus', 'nexo' ),
                'status'    =>  'failed'
            ) );
        }

        // Only incomplete order can be edited
        if ( ! in_array( $current_order[0][ 'TYPE' ], $this->events->apply_filters( 'order_editable', [ 'nexo_order_devis', 'nexo_order_advance' ] ) ) ) { // $this->put( 'EDITABLE_ORDERS' )
            return response()->httpCode( 403 )->json(array(
                'message'   =>  __( 'Impossible de modifier cette commande. Son statut ayant changé, cette commande n\'est plus modifiable.', 'nexo' ),
                'status'    =>  'failed'
            ) );
        }

        $shipping                   =   ( array ) $this->put( 'shipping' );

        $order_details            =    array(
            'RISTOURNE'             =>      $this->put('RISTOURNE'),
            'REMISE'                =>      $this->put('REMISE'),
            // @since 2.9.6
            'REMISE_PERCENT'        =>      $this->put( 'REMISE_PERCENT' ),
            'REMISE_TYPE'           =>      $this->put( 'REMISE_TYPE' ),
            // @endSince
            'RABAIS'                =>      $this->put('RABAIS'),
            'GROUP_DISCOUNT'        =>      $this->put('GROUP_DISCOUNT'),
            'TOTAL'                 =>      $this->put('TOTAL'),
            'AUTHOR'                =>      $author_id,
            'PAYMENT_TYPE'          =>      $this->put('PAYMENT_TYPE'),
            'REF_CLIENT'            =>      $this->put('REF_CLIENT'),
            'TVA'                   =>      $this->put('TVA'),
            'DATE_MOD'              =>      date_now(),
            'DESCRIPTION'           =>      $this->put( 'DESCRIPTION' ),
            'REF_REGISTER'          =>      $this->put( 'REGISTER_ID' ),
            // @since 3.1
            'SHIPPING_AMOUNT'       =>      floatval( @$shipping[ 'price' ] ),
            'REF_SHIPPING_ADDRESS'  =>      @$shipping[ 'id' ],
            // @since 3.1.0
            'TITRE'                 =>      $this->put( 'TITRE' ),
            'REF_TAX'               =>      $this->put( 'REF_TAX' ),
            'STATUS'                =>      $this->put( 'STATUS' ) !== null ? $this->put( 'STATUS' ) : 'processing'
        );

        /**
         * let's update the order status if it has changed
         */
        if ( floatval( $this->put( 'TOTAL' ) ) <= floatval( $this->put( 'SOMME_PERCU' ) ) ) {
            // $order_details[ 'STATUS' ]  =   'complete';
        }

        // If customer has changed
        if ( $this->put('REF_CLIENT') != $old_order['order'][0][ 'REF_CLIENT' ] ) {
            
            // Increase customers purchases
            $query                  =    $this->db->where('ID', $this->put('REF_CLIENT'))->get( store_prefix() . 'nexo_clients');
            $client                 =    $query->result_array();
            
            if ( empty( $client ) ) {
                return array(
                    'message'   =>  __( 'Impossible d\'identifier le client. Veuillez choisir un client ou définir un client par défaut.', 'nexo' ),
                    'status'    =>  'failed'
                );
            }
        } 

        // Restore Bought items 
        // only if the stock management is enabled
        foreach ( $old_order[ 'products' ] as $product ) {
            if( intval( $product[ 'STOCK_ENABLED' ] ) == 1 ) {
                // to avoid saving negative values
                // pull fresh value a make a comparison
                // @since 3.7.5
                $fresh_product      =   $this->db->where( 'ID', $product[ 'ID' ] )->get( store_prefix() . 'nexo_articles' )
                ->result_array();

                if( intval( $fresh_product[0][ 'QUANTITE_RESTANTE' ] ) - intval($product[ 'QUANTITE' ]) > 0 ) {
                    $this->db
                    ->set('QUANTITE_RESTANTE', '`QUANTITE_RESTANTE` + ' . intval($product[ 'QUANTITE' ]), false)
                    ->set('QUANTITE_VENDU', '`QUANTITE_VENDU` - ' . intval($product[ 'QUANTITE' ]), false)
                    ->where('CODEBAR', $product[ 'REF_PRODUCT_CODEBAR' ])
                    ->update( store_prefix() . 'nexo_articles');        
                }
            }
        }

        // Delete item from order
        $this->db->where( 'REF_COMMAND_CODE', $old_order[ 'order' ][0][ 'CODE' ] )->delete( store_prefix() . 'nexo_commandes_produits');

        // Delete item metas
        $this->db->where( 'REF_COMMAND_CODE', $old_order[ 'order' ][0][ 'CODE' ] )->delete( store_prefix() . 'nexo_commandes_produits_meta');

        // Save Order items
        /**
         * Item structure
         * array( ID, QUANTITY_ADDED, BARCODE, PRICE, QTE_SOLD, LEFT_QTE, STOCK_ENABLED );
        **/

        foreach ( $this->put('ITEMS') as $item ) {

            // let the user filter items
            $item   =   $this->events->apply_filters( 'put_order_filter_item', $item );

            // Get Items
            $fresh_item    =    $this->db->where('CODEBAR', $item[ 'codebar' ])
            ->get( store_prefix() . 'nexo_articles')
            ->result_array();

            /**
             * deplete included items
             * only works for grouped items
             * @todo restore previouly bought items
             */
            if ( @$fresh_item[0][ 'TYPE' ] === '3' && @$fresh_item[0][ 'STOCK_ENABLED' ] == '1' ) {
                $meta   =   $this->db->where( 'REF_ARTICLE', $fresh_item[0][ 'ID' ] )
                ->where( 'KEY', 'included_items' )
                ->get( store_prefix() . 'nexo_articles_meta' )
                ->result_array();

                /**
                 * if meta is set
                 */
                if ( $meta ) {
                    $items  =   json_decode( $meta[0][ 'VALUE' ] );
                    
                    foreach ( $items as $includedItem ) {

                        $totalQuantity      =   ( floatval( $includedItem->quantity ) * floatval( $item[ 'qte_added' ] ) );

                        // Add history for this item on stock flow
                        $stock_flow     =   [
                            'REF_ARTICLE_BARCODE'       =>  $includedItem->barcode,
                            'QUANTITE'                  =>  $totalQuantity,
                            'UNIT_PRICE'                =>  $includedItem->sale_price,
                            'TOTAL_PRICE'               =>  floatval( $includedItem->sale_price ) * $totalQuantity,
                            'REF_COMMAND_CODE'          =>  $old_order[ 'order' ][0][ 'CODE' ],
                            'AUTHOR'                    =>  User::id(),
                            'DATE_CREATION'             =>  date_now(),
                            'TYPE'                      =>  'sale'
                        ];

                        $__included     =   $this->db->where( 'CODEBAR', $includedItem->barcode )
                        ->get( store_prefix() . 'nexo_articles' )
                        ->result_array();

                        // if item is a physical item, than we can consider using before and after quantity
                        if( @$__included[0][ 'TYPE' ] === '1' && @$__included[0][ 'STOCK_ENABLED' ] === '1' ) {
                            $stock_flow[ 'BEFORE_QUANTITE' ]    =   $__included[0][ 'QUANTITE_RESTANTE' ];
                            $stock_flow[ 'AFTER_QUANTITE' ]     =   floatval( $__included[0][ 'QUANTITE_RESTANTE' ] ) - floatval( $totalQuantity );
                        } else {
                            $stock_flow[ 'BEFORE_QUANTITE' ]    =   $__included[0][ 'QUANTITE_RESTANTE' ];
                            $stock_flow[ 'AFTER_QUANTITE' ]     =   $__included[0][ 'QUANTITE_RESTANTE' ];
                        }
                        
                        $this->db->insert( store_prefix() . 'nexo_articles_stock_flow', $stock_flow);

                        /**
                         * updating included item stock
                         */
                        $this->db->where( 'CODEBAR', $includedItem->barcode )
                        ->set( 'QUANTITE_RESTANTE', 'QUANTITE_RESTANTE-' . $totalQuantity, false )
                        ->set( 'QUANTITE_VENDU', 'QUANTITE_VENDU+' . $totalQuantity, false )
                        ->update( store_prefix() . 'nexo_articles' );
                    }
                }
            }

			/**
			 * If Stock Enabled is active
			**/

			if( intval( $item['stock_enabled'] ) == 1 && ! in_array( $item[ 'inline' ], [ 'true', '1' ]) ) {
				$this->db->where('CODEBAR', $item['codebar'])->update( store_prefix() . 'nexo_articles', array(
					'QUANTITE_RESTANTE'        =>    intval($fresh_item[0][ 'QUANTITE_RESTANTE' ]) - intval($item['qte_added']),
					'QUANTITE_VENDU'        =>    intval($fresh_item[0][ 'QUANTITE_VENDU' ]) + intval($item['qte_added'])
				) );
			}

			// Adding to order product
			if( $item[ 'discount_type' ] == 'percentage' && $item[ 'discount_percent' ] != '0' ) {
				$discount_amount		=	__floatval( ( __floatval($item[ 'qte_added' ]) * __floatval($item[ 'sale_price' ]) ) * floatval( $item[ 'discount_percent' ] ) / 100 );
			} elseif( $item[ 'discount_type' ] == 'flat' ) {
				$discount_amount		=	__floatval( $item[ 'discount_amount' ] );
			} else {
				$discount_amount		=	0;
			}

            // Adding to order product
			$item_data			=	array(
                'REF_PRODUCT_CODEBAR'       =>    $item[ 'codebar' ],
                'REF_COMMAND_CODE'          =>    $old_order[ 'order' ][0][ 'CODE' ],
                'QUANTITE'                  =>    $item[ 'qte_added' ],
                'PRIX_BRUT'                 =>    $item[ 'sale_price' ],
                'PRIX'                      =>    floatval( $item[ 'sale_price' ] ) - $discount_amount,
                'PRIX_TOTAL'                =>    ( __floatval($item[ 'qte_added' ]) * __floatval($item[ 'sale_price' ]) ) - $discount_amount,
                'PRIX_BRUT_TOTAL'           =>    ( __floatval($item[ 'qte_added' ]) * __floatval($item[ 'sale_price' ]) ),
				// @since 2.9.0
				'DISCOUNT_TYPE'             =>    $item[ 'discount_type' ],
				'DISCOUNT_AMOUNT'           =>    $item[ 'discount_amount' ],
                'DISCOUNT_PERCENT'          =>    $item[ 'discount_percent' ],
                // @since 3.1
                'NAME'                      =>    $item[ 'name' ],
                'INLINE'                    =>    $item[ 'inline' ],
                'ALTERNATIVE_NAME'          =>    @$item[ 'alternative_name' ] ? @$item[ 'alternative_name' ] : '' // @sicne 3.11.8
            );

            // filter item
            $item_data                      =     $this->events->apply_filters_ref_array( 'put_order_item', [$item_data, $item] );

            $this->db->insert( store_prefix() . 'nexo_commandes_produits', $item_data );

            $command_product_id             =   $this->db->insert_id();

            // Saving item metas
            foreach( ( array ) @$item[ 'metas' ] as $key => $value ) {
                $meta     =   [
                    'VALUE'                 =>  is_array( $value ) ? json_encode( $value ) : $value,
                    'DATE_MODIFICATION'     =>  date_now(),
                    'REF_COMMAND_CODE'      =>  $old_order[ 'order' ][0][ 'CODE' ],
                    'KEY'                   =>  $key,
                    'REF_COMMAND_PRODUCT'   =>  $command_product_id
                ];

                $this->db->where( 'REF_COMMAND_PRODUCT', $item[ 'id' ] )
                ->insert( store_prefix() . 'nexo_commandes_produits_meta', $meta );
            }

            $stock_flow     =   [
                'REF_ARTICLE_BARCODE'       =>  $item[ 'codebar' ],
                'QUANTITE'                  =>  $item[ 'qte_added' ],
                'UNIT_PRICE'                =>  $item[ 'sale_price' ],
                'TOTAL_PRICE'               =>  ( __floatval($item[ 'qte_added' ]) * __floatval($item[ 'sale_price' ]) ) - $discount_amount,
                'REF_COMMAND_CODE'          =>  $old_order[ 'order' ][0][ 'CODE' ],
                'AUTHOR'                    =>  User::id(),
                'DATE_CREATION'             =>  date_now(),
                'TYPE'                      =>  'sale'
            ];

            /**
             * retrict the operation only for physical and digital items
             * this exclude flash items.
             */
            if ( in_array( @$fresh_item[0][ 'TYPE' ], [ '1', '2' ] ) ) {
                
                // if item is a physical item, than we can consider using before and after quantity
                if( @$fresh_item[0][ 'TYPE' ] === '1' && @$fresh_item[0][ 'STOCK_ENABLED' ] === '1' ) {
                    $stock_flow[ 'BEFORE_QUANTITE' ]    =   $fresh_item[0][ 'QUANTITE_RESTANTE' ];
                    $stock_flow[ 'AFTER_QUANTITE' ]     =   floatval( $fresh_item[0][ 'QUANTITE_RESTANTE' ] ) - floatval( $item[ 'qte_added' ] );
                } else {
                    $stock_flow[ 'BEFORE_QUANTITE' ]    =   $fresh_item[0][ 'QUANTITE_RESTANTE' ];
                    $stock_flow[ 'AFTER_QUANTITE' ]     =   $fresh_item[0][ 'QUANTITE_RESTANTE' ];
                }

                $this->db->insert( store_prefix() . 'nexo_articles_stock_flow', $stock_flow);
            }
        }

        // filter order details
        $order_details          =   $this->events->apply_filters( 'put_order_details', $order_details, $this->put(), $order_id );

        $this->db->where( 'ID', $order_id )
        ->update( store_prefix() . 'nexo_commandes', $order_details);

		// @since 2.8.2
		$metas        =	      $this->put( 'metas' );

		if( $metas ) {

			foreach( $metas as $key => $value ) {

				$meta_data			=	array(
					'REF_ORDER_ID'	=>	$order_id,
					'KEY'			=>	$key,
					'VALUE'			=>	is_array( $value ) ? json_encode( $value ) : $value,
					'AUTHOR'		=>	$author_id,
					'DATE_CREATION'	=>	date_now()
				);

				$this->db->where( 'REF_ORDER_ID', $order_id )
                ->where( 'KEY', $key )
                ->update( store_prefix() . 'nexo_commandes_meta', $meta_data );
			}
		}

		// @since 2.9
		// Save order payment
		$this->load->config( 'rest' );
		$Curl			=	new Curl;
        // $header_key		=	$this->config->item( 'rest_key_name' );
		// $header_value	=	$_SERVER[ 'HTTP_' . $this->config->item( 'rest_key_name' ) ];
		// $Curl->setHeader($this->config->item('rest_key_name'), $_SERVER[ 'HTTP_' . $this->config->item('rest_header_key') ]);

		if( is_array( $this->put( 'payments' ) ) ) {
			foreach( $this->put( 'payments' ) as $payment ) {

				$request    =   Requests::post( site_url( array( 'rest', 'nexo', 'order_payment', $current_order[0][ 'ID' ], store_get_param( '?' ) ) ), [
                    $this->config->item('rest_key_name')    =>  $_SERVER[ 'HTTP_' . $this->config->item('rest_header_key') ]
                ], array(
					'author'		=>	$author_id,
					'date'			=>	date_now(),
					'payment_type'	=>	$payment[ 'namespace' ],
					'amount'		=>	$payment[ 'amount' ],
                    'order_code'	=>	$current_order[0][ 'CODE' ],
                    'ref_id'        =>  intval( @$payment[ 'meta' ][ 'coupon_id' ]),
                    'payment_id'    =>  @$payment[ 'meta' ][ 'payment_id' ] ?? null
                ) );

                /**
                 * parse the response
                 * from the payment request
                 */
                $response       =   json_decode( $request->body, true );

                // @since 3.1
                // if the payment is a coupon, then we'll increase his usage
                if( $payment[ 'namespace' ] == 'coupon' ) {
                    
                    $coupon         =   $this->db->where( 'ID', $payment[ 'meta' ][ 'coupon_id' ] )
                        ->get( store_prefix() . 'nexo_coupons' )
                        ->result_array();

                    $this->db->where( 'ID', $payment[ 'meta' ][ 'coupon_id' ] )
                        ->update( store_prefix() . 'nexo_coupons', [
                            'USAGE_COUNT'   =>  intval( $coupon[0][ 'USAGE_COUNT' ] ) + 1
                        ]);
                    
                    /**
                     * save the coupon on the coupon
                     * usage history and assign it to 
                     * an order.
                     */
                    $this->db->insert( store_prefix() . 'nexo_commandes_coupons', [
                        'REF_COMMAND'   =>  $order_id,
                        'REF_COUPON'    =>  $coupon[0][ 'ID' ],
                        'REF_PAYMENT'   =>  @$response[ 'data' ][ 'payment_id' ]
                    ]);
                }
			}
        }

        /**
         * Add shipping informations
         * @since 3.1
        **/

        if( $this->put( 'shipping' ) ) {
            // fetch ref shipping for the selected customers
            $shipping       =   $this->put( 'shipping' );
            // edit shipping id to ref_shipping
            $shipping[ 'ref_shipping' ]     =   $shipping[ 'id' ];
            $shipping[ 'ref_order' ]        =   $order_id;
            unset( $shipping[ 'id' ] );

            $this->db->where( 'ref_order', $order_id )
                ->update( store_prefix() . 'nexo_commandes_shippings', $shipping );
        }

        /**
         * memory leak
         */
        $current_order    =    $this->db->where( 'ID', $order_id )
            ->get( store_prefix() . 'nexo_commandes')
            ->result_array();

        /**
         * let's check if the orders is complete,
         * in order to make him provide from additionnal
         * offers
         */
        if ( $current_order[0][ 'TYPE' ] === 'nexo_order_comptant' ) {
            if ( $this->put('REF_CLIENT') != $old_order['order'][0][ 'REF_CLIENT' ] ) {
            
                $total_commands         =    intval($client[0][ 'NBR_COMMANDES' ]) + 1;
                $overal_commands        =    intval($client[0][ 'OVERALL_COMMANDES' ]) + 1;
                $total_spend            =   floatval( $client[0][ 'TOTAL_SPEND' ] ) + floatval( $this->put( 'TOTAL' ) );
    
                $this->db->set('NBR_COMMANDES', $total_commands);
                $this->db->set('OVERALL_COMMANDES', $overal_commands);
                $this->db->set( 'TOTAL_SPEND', $total_spend );
    
                // Disable automatic discount
                if ( $this->put('REF_CLIENT') != store_option( 'default_compte_client' ) ) {
    
                    // Verifie si le client doit profiter de la réduction
                    if ($this->put('DISCOUNT_TYPE') != 'disable') {
                        // On définie si en fonction des réglages, l'on peut accorder une réduction au client
                        if ($total_commands >= __floatval($this->put('HMB_DISCOUNT')) - 1 && $client[0][ 'DISCOUNT_ACTIVE' ] == 0) {
                            $this->db->set('DISCOUNT_ACTIVE', 1);
                        } elseif ($total_commands >= $this->put('HMB_DISCOUNT') && $client[0][ 'DISCOUNT_ACTIVE' ] == 1) {
                            $this->db->set('DISCOUNT_ACTIVE', 0); // bénéficiant d'une reduction sur cette commande, la réduction est désactivée
                            $this->db->set('NBR_COMMANDES', 0); // le nombre de commande est également désactivé
                        }
                    }
                }
    
                $this->db->where('ID', $this->put('REF_CLIENT'))
                    ->update( store_prefix() . 'nexo_clients' );
    
                // Le nombre de commande ne peut pas être inférieur à 0;
                if( $old_customer ) {
                    $this->db
                    ->set('NBR_COMMANDES',  intval($old_customer[0][ 'NBR_COMMANDES' ]) == 0 ? 0 : intval($old_customer[0][ 'NBR_COMMANDES' ]) - 1)
                    ->set('OVERALL_COMMANDES',  intval($old_customer[0][ 'OVERALL_COMMANDES' ]) == 0 ? 0 : intval($old_customer[0][ 'OVERALL_COMMANDES' ]) - 1)
                    ->where('ID', $old_order['order'][0][ 'REF_CLIENT' ])
                    ->update( store_prefix() . 'nexo_clients');
                }
            } else {
                // Increase customers purchases
                $query                  =   $this->db->where('ID', $this->put('REF_CLIENT') )->get( store_prefix() . 'nexo_clients');
                $client                 =   $query->result_array();
                $total_commands         =    intval($client[0][ 'NBR_COMMANDES' ]) + 1;
                $overal_commands        =    intval($client[0][ 'OVERALL_COMMANDES' ]) + 1;
                $total_spend            =   floatval( $client[0][ 'TOTAL_SPEND' ] ) + floatval( $this->put( 'TOTAL' ) );
    
                /**
                 * this operation cancel the previous amount and update with
                 * the new amount of the updated order
                 */
                $total_spend            =   (
                    floatval( $client[0][ 'TOTAL_SPEND' ] ) - floatval( $old_order['order' ][0][ 'TOTAL' ] )
                ) + floatval( $old_order['order' ][0][ 'TOTAL' ] );                
    
                $this->db->set('NBR_COMMANDES', $total_commands);
                $this->db->set('OVERALL_COMMANDES', $overal_commands);
                $this->db->set( 'TOTAL_SPEND', $total_spend );
    
                $this->db->where('ID', $this->put('REF_CLIENT'))
                    ->update( store_prefix() . 'nexo_clients');
            }
        }

        /**
         * let's manage the entire reward system over here
         */
        $this->reward_model->handleRewardIfEnabled([
            'total'         =>  $this->put( 'TOTAL' ),
            'customer_id'   =>  $this->put( 'REF_CLIENT' ),
            'order'         =>  $current_order[0]
        ]);

        $data       =   $this->put();
        $response   =   $this->Nexo_Checkout->afterOrderPlaced( compact( 'current_order', 'order_details', 'data' ) );
        extract( $response ); 

        // filter put response
        $this->response( $this->events->apply_filters( 'put_order_response', array(
            'order_id'          =>    $order_id,
            'order_type'        =>    $order_details[ 'TYPE' ],
            'order_code'        =>    $current_order[0][ 'CODE' ]
        ), [
            'order_details'     =>  $order_details,
            'current_order'     =>  $current_order[0]
        ]), 200 );
    }

    /**
     * Get order using dates
     *
	 * @param string order type
	 * @param int register id
     * @return json
    **/

    public function order_by_dates_post($order_type = 'all', $register = null )
    {
		// @since 2.7.5
		if( $register != null ) {
			$this->db->where('REF_REGISTER', $register );
		}

        $this->db->where('DATE_CREATION >=', $this->post('start'));
        $this->db->where('DATE_CREATION <=', $this->post('end'));

        if ( $order_type != 'all') {
            $this->db->where('TYPE', $order_type);
        }

        $query    =    $this->db->get( store_prefix() . 'nexo_commandes' );
        $this->response($query->result(), 200);
    }

    /**
     * Search order code
     * @return json
     */
    public function order_search_post()
    {
        $order		=	$this->db->select( '*,
		' . store_prefix() .'nexo_commandes.ID as ID,
		' . store_prefix() .'nexo_clients.NOM as CLIENT_NAME,
		aauth_users.name as AUTHOR_NAME,
		' . store_prefix() . 'nexo_commandes.DATE_CREATION as DATE_CREATION,
		' )

		->from( store_prefix() . 'nexo_commandes' )

		->join(
			store_prefix() . 'nexo_clients',
			store_prefix() . 'nexo_clients.ID = ' . store_prefix() . 'nexo_commandes.REF_CLIENT',
			'left'
		)

		->join(
			'aauth_users',
			store_prefix() . 'nexo_commandes.AUTHOR = aauth_users.id',
			'left'
		)

		->like( 'CODE', $this->post( 'code' ) )

		->get()->result();

		$this->response( $order, 200 );
    }

	/**
	 * Get Order with his item
	 * @param int order id
	 * @return json
	**/

	public function order_with_item_get( $order_id )
	{
		$order		=	$this->db->select( '*,
		' . store_prefix() .'nexo_commandes.ID as ID,
		' . store_prefix() .'nexo_clients.NOM as CLIENT_NAME,
		aauth_users.name as AUTHOR_NAME,
		' . store_prefix() . 'nexo_commandes.DATE_CREATION as DATE_CREATION,
		' )

		->from( store_prefix() . 'nexo_commandes' )

		->join(
			store_prefix() . 'nexo_clients',
			store_prefix() . 'nexo_clients.ID = ' . store_prefix() . 'nexo_commandes.REF_CLIENT',
			'left'
		)

		->join(
			'aauth_users',
			store_prefix() . 'nexo_commandes.AUTHOR = aauth_users.id',
			'left'
		)

		->where( store_prefix() . 'nexo_commandes.ID', $order_id )

		->get()->result();

		$items		=	$this->db->select( '*, '
        . store_prefix() . 'nexo_commandes_produits.ID as ID' )

		->from( store_prefix() . 'nexo_commandes_produits' )

		->join(
			store_prefix() . 'nexo_articles',
			store_prefix() . 'nexo_articles.CODEBAR = ' . store_prefix() . 'nexo_commandes_produits.REF_PRODUCT_CODEBAR',
			'left'
		)

		->where( store_prefix() . 'nexo_commandes_produits.REF_COMMAND_CODE', $order[0]->CODE )

		->get()->result();

        // load items meta
        foreach( $items as $key => $item ) {
            $metas      =   $this->db->where( store_prefix() . 'nexo_commandes_produits_meta.REF_COMMAND_PRODUCT', $item->ID )
            ->get( store_prefix() . 'nexo_commandes_produits_meta' )->result();

            if( $metas ) {
                $items[ $key ]->metas    =   [];
            }

            foreach( $metas as $meta ) {
                $items[ $key ]->metas[ $meta->KEY ]      =   $meta->VALUE;
            }
        }

        // load shippings
        /** 
         * get shippings linked to that order
         * @since 3.1
        **/

        foreach( ( array ) $order as &$_order ) {
            $shippings   =   $this->db->where( 'ref_order', $_order->ID )
            ->get( store_prefix() . 'nexo_commandes_shippings' )
            ->result_array();

            if( $shippings ) {
                $_order->shipping   =   $shippings[0];
            }
        }

		if( $order && $items ) {
			$this->response( array(
				'order'		=>	$order[0],
				'items'		=>	$items
			), 200 );
		}

		$this->__empty();
	}

    /**
    *
    * Get Order with item made during a time range
    *
    * @param  int order id
    * @return json object
    */

    public function order_with_item_post( $order_id = null )
    {
        $order		=	$this->db->select( '*,
        ' . store_prefix() .'nexo_commandes.ID as ID,
        ' . store_prefix() .'nexo_commandes.DATE_CREATION as DATE_CREATION,
        ' . store_prefix() .'nexo_clients.NOM as CLIENT_NAME,
        aauth_users.name as AUTHOR_NAME,
        ' . store_prefix() . 'nexo_commandes.DATE_CREATION as DATE_CREATION,
        ' )

        ->from( store_prefix() . 'nexo_commandes' )

        ->join(
            store_prefix() . 'nexo_clients',
            store_prefix() . 'nexo_clients.ID = ' . store_prefix() . 'nexo_commandes.REF_CLIENT',
            'left'
        )

        ->join(
            'aauth_users',
            store_prefix() . 'nexo_commandes.AUTHOR = aauth_users.id',
            'left'
        )

        ->join(
            store_prefix() . 'nexo_commandes_produits',
            store_prefix() . 'nexo_commandes_produits.REF_COMMAND_CODE = ' . store_prefix() . 'nexo_commandes.CODE',
            'left'
        )

        ->join(
            store_prefix() . 'nexo_articles',
            store_prefix() . 'nexo_articles.CODEBAR = ' . store_prefix() . 'nexo_commandes_produits.REF_PRODUCT_CODEBAR',
            'left'
        );

        if( $order_id != null ) {
            $this->db->where( store_prefix() . 'nexo_commandes.ID', $order_id );
        }

        if( $this->post( 'start_date' ) && $this->post( 'end_date' ) ) {
            $start_date         =   Carbon::parse( $this->post( 'start_date' ) )->startOfDay()->toDateTimeString();
            $end_date           =   Carbon::parse( $this->post( 'end_date' ) )->endOfDay()->toDateTimeString();

            $this->db->where( store_prefix() . 'nexo_commandes.DATE_CREATION >=', $start_date );
            $this->db->where( store_prefix() . 'nexo_commandes.DATE_CREATION <=', $end_date );
        }

        $result     =   $this->db
        ->get()->result();

        if( $result ) {
            $this->response( $result, 200 );
        }

        $this->__empty();
    }

	/**
	 * Order With Status
	 * @param string order status
	 * @return json
	**/

	public function order_with_status_get( $status )
	{
		$order		=	$this->db->select( '*,
		' . store_prefix() .'nexo_commandes.ID as ID,
		' . store_prefix() .'nexo_clients.NOM as CLIENT_NAME,
		aauth_users.name as AUTHOR_NAME,
		' . store_prefix() . 'nexo_commandes.DATE_CREATION as DATE_CREATION,
		' )

		->from( store_prefix() . 'nexo_commandes' )

		->join(
			store_prefix() . 'nexo_clients',
			store_prefix() . 'nexo_clients.ID = ' . store_prefix() . 'nexo_commandes.REF_CLIENT',
			'left'
		)

		->join(
			'aauth_users',
			store_prefix() . 'nexo_commandes.AUTHOR = aauth_users.id',
			'left'
        )
        
        ->where( store_prefix() . 'nexo_commandes.TYPE', $status )
        
        ->order_by( store_prefix() . 'nexo_commandes.DATE_CREATION', 'DESC' )

		->get()->result();

        // pending review
        // /** 
        //  * get shippings linked to that order
        //  * @since 3.1
        // **/

        // foreach( ( array ) $order as &$_order ) {
        //     $shippings   =   $this->db->where( 'ref_order', $_order->ID )
        //     ->get( store_prefix() . 'nexo_commandes_shippings' )
        //     ->result_array();

        //     if( $shippings ) {
        //         $_order->shipping   =   $shippings[0];
        //     }
        // }

		$this->response( $order, 200 );

		$this->__empty();
	}

    /**
     *  Order with all stock defective and usable
     *  @param int order id
     *  @return json
    **/

    public function order_with_stock_get( $order_code )
    {
        $this->db->select(
            'aauth_users.name as ORDER_CASHIER, ' .
            store_prefix() . 'nexo_articles.DESIGN, ' .
            store_prefix() . 'nexo_commandes.TOTAL as TOTAL,' .
            store_prefix() . 'nexo_commandes.ID as ID,' .
            store_prefix() . 'nexo_commandes.CODE as CODE , ' .            
            store_prefix() . 'nexo_commandes.PAYMENT_TYPE , ' .            
            store_prefix() . 'nexo_commandes.SOMME_PERCU , ' .       
            store_prefix() . 'nexo_commandes.TYPE , ' .            
            store_prefix() . 'nexo_commandes.PAYMENT_TYPE , ' .            
            store_prefix() . 'nexo_commandes.DESCRIPTION , ' .            
            store_prefix() . 'nexo_commandes.DATE_CREATION as DATE , ' .      
            store_prefix() . 'nexo_commandes.DATE_MOD as DATE_MOD , ' .            
            store_prefix() . 'nexo_articles_stock_flow.REF_ARTICLE_BARCODE, ' .
            store_prefix() . 'nexo_articles_stock_flow.UNIT_PRICE as PRIX, ' .
            store_prefix() . 'nexo_articles_stock_flow.QUANTITE as QUANTITE, ' .
            store_prefix() . 'nexo_articles_stock_flow.TYPE as TYPE'
        );

        $this->db->from( store_prefix() . 'nexo_commandes' );

        $this->db->join(
            store_prefix() . 'nexo_articles_stock_flow',
            store_prefix() . 'nexo_articles_stock_flow.REF_COMMAND_CODE = ' .
            store_prefix() . 'nexo_commandes.CODE', 'left'
        );

        $this->db->join(
            'aauth_users',
            'aauth_users.id = ' .
            store_prefix() . 'nexo_commandes.AUTHOR', 'left'
        );

        $this->db->join(
            store_prefix() . 'nexo_articles',
            store_prefix() . 'nexo_articles.CODEBAR = ' .
            store_prefix() . 'nexo_articles_stock_flow.REF_ARTICLE_BARCODE', 'left'
        );

        $this->db->where( store_prefix() . 'nexo_articles_stock_flow.REF_COMMAND_CODE', $order_code );
        $this->db->where_in( store_prefix() . 'nexo_articles_stock_flow.TYPE', [ 'defective', 'usable' ]);
        // $this->db->group_by( store_prefix() . 'nexo_articles_stock_flow.REF_ARTICLE_BARCODE' );

        // $this->db->or_where(
        //     store_prefix() . 'nexo_articles_stock_flow.REF_COMMAND_CODE',
        //     $order_code
        // );

        $order      =   $this->db->get()->result();

        // pending
        // /** 
        //  * get shippings linked to that order
        //  * @since 3.1
        // **/

        // foreach( ( array ) $order as &$_order ) {
        //     $shippings   =   $this->db->where( 'ID', $_order->ID )
        //     ->get( store_prefix() . 'nexo_commandes_shippings' )
        //     ->result();

        //     $_order[ 'shipping' ]   =   $shippings[0];
        // }

        return $this->response(
            $order,
            200
        );
    }

	/**
	 * Order Products
	 * @param string order code
	 * @return json
	**/

	public function order_items_dual_item_post( )
	{
		if( is_array( $this->post( 'orders_code' ) ) ) {

			foreach( $this->post( 'orders_code' ) as $code ) {
				$this->db->or_where( 'REF_COMMAND_CODE', $code );
			}

			$data[ 'order_items' ]	=	$this->db->get( store_prefix() . 'nexo_commandes_produits' )->result();

			$data[ 'items' ]		=	$this->db->select( '*' )
			->from( store_prefix() . 'nexo_commandes_produits' )
			->join( store_prefix() . 'nexo_articles', store_prefix() . 'nexo_articles.CODEBAR = ' . store_prefix() . 'nexo_commandes_produits.REF_PRODUCT_CODEBAR', 'inner' )
			->get()->result();

			$this->response( $data, 200 );
		}
		$this->__empty();
	}

	/**
	 * Proceed Payment
	 * @param int order id
	 * @return json
	**/

	public function order_payment_post( $order_id )
	{
        $order	=	$this->db->where( 'ID', $order_id )->get( store_prefix() . 'nexo_commandes' )->result();
        
        $oldPayment        =   $this->db->where( 'ID', $this->post( 'payment_id' ) )
            ->get( store_prefix() . 'nexo_commandes_paiements' )
            ->result_array();
        
        /**
         * we do symbolically cancel the amount
         * perceived and redo it.
         * This help to prevent false values.
         */
        $somme_percu        =   ! empty( $oldPayment ) ? 
            ( floatval( $order[0]->SOMME_PERCU ) - floatval( $oldPayment[0][ 'MONTANT' ] ) ) + floatval( $this->post( 'amount' ) ) :
            floatval( $order[0]->SOMME_PERCU ) + floatval( $this->post( 'amount' ) ); 
        
		if( $order[0]->TYPE !== 'nexo_order_comptant' ) {

			if( floatval( $order[0]->TOTAL ) <= $somme_percu ) {
				$this->db->where( 'ID', $order_id )->update( store_prefix() . 'nexo_commandes', array(
					'AUTHOR'				=>	$this->post( 'author' ),
					'DATE_MOD'				=>	$this->post( 'date' ),
					'TYPE'					=>	'nexo_order_comptant',
					'SOMME_PERCU'			=>	$somme_percu,
				) );
			} else {
				$this->db->where( 'ID', $order_id )->update( store_prefix() . 'nexo_commandes', array(
					'AUTHOR'				=>	$this->post( 'author' ),
					'DATE_MOD'				=>	$this->post( 'date' ),
					'TYPE'					=>	'nexo_order_advance',
					'SOMME_PERCU'			=>	$somme_percu,
				) );
			}

            if ( empty( $oldPayment ) ) {
                $payment_data   =   array(
                    'REF_COMMAND_CODE'		=>	$this->post( 'order_code' ),
                    'AUTHOR'				=>	$this->post( 'author' ),
                    'DATE_CREATION'			=>	date_now(),
                    'PAYMENT_TYPE'			=>	$this->post( 'payment_type' ),
                    'OPERATION'             =>  'incoming',
                    'MONTANT'				=>	$this->post( 'amount' ),
                    'REF_ID'                =>  $this->post( 'ref_id' ),
                );

                $this->db->insert( store_prefix() . 'nexo_commandes_paiements', $payment_data );

                $insert_id      =   $this->db->insert_id();

            } else {
                $this->db->where( 'ID', $this->post( 'payment_id' ) )
                    ->update( store_prefix() . 'nexo_commandes_paiements', [
                        'MONTANT'           =>  $this->post( 'amount' )
                    ]);

                $insert_id      =   $this->post( 'payment_id' );
            }

			$this->response([
                'status'    =>  'success',
                'message'   =>  __( 'Le paiement a été enregistrée', 'nexo' ),
                'data'      =>  [
                    'payment_id'    =>  $insert_id
                ]
            ]);

		} else {
			$this->__forbidden();
		}
	}

	/**
	 * Get Order Payments
	 * @param int order id
	 * @return json
	**/
	public function order_payment_get( $order_code )
	{
		$this->response(
			$this->db
			->select( '*,aauth_users.name as AUTHOR_NAME' )
			->join( 'aauth_users', 'aauth_users.id = ' . store_prefix() . 'nexo_commandes_paiements.AUTHOR', 'right' )
			->from( store_prefix() . 'nexo_commandes_paiements' )
			->where( 'REF_COMMAND_CODE', $order_code )
			->get()->result(),
			200
		);
	}

	/**
	 * Get order item with their defective stock
	 * @param order code
	**/

	public function order_items_defectives_get( $order_code )
	{
        $this->db->select( '*,
        ' . store_prefix() . 'nexo_commandes_produits.ID as ORDER_ITEM_ID,
        ' . store_prefix() . 'nexo_commandes_produits.QUANTITE as REAL_QUANTITE' )
		->from( store_prefix() . 'nexo_commandes_produits' )
		->join( store_prefix() . 'nexo_articles', store_prefix() . 'nexo_commandes_produits.REF_PRODUCT_CODEBAR = ' . store_prefix() . 'nexo_articles.CODEBAR', 'left' )
		->join( store_prefix() . 'nexo_commandes', store_prefix() . 'nexo_commandes.CODE = ' . store_prefix() . 'nexo_commandes_produits.REF_COMMAND_CODE', 'inner' )
		->where( store_prefix() . 'nexo_commandes.CODE', $order_code );
		// ->where( store_prefix() . 'nexo_articles_defectueux.REF_COMMAND_CODE', $order_code );

		$query	=	$this->db->get();

		$this->response( $query->result(), 200 );
	}

    /**
     *  Sales Details
     *  @param
     *  @return
    **/

    public function sales_detailed_post()
    {
        $startHasTimeWithin         =   preg_match( "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) [0-2]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/", $this->post( 'start_date' ) );
        $endHasTimeWithin           =   preg_match( "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) [0-2]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/", $this->post( 'end_date' ) );
        $startOfDay                 =   ! $startHasTimeWithin ? Carbon::parse( $this->post( 'start_date' ) )->startOfDay()->toDateTimeString() : $this->post( 'start_date' );
        $endOfDay                   =   ! $endHasTimeWithin ? Carbon::parse( $this->post( 'end_date' ) )->endOfDay()->toDateTimeString() : $this->post( 'end_date' );
        $query                      =   $this->db->select( '
            ' . store_prefix() . 'nexo_commandes.ID as ID,
            ' . store_prefix() . 'nexo_commandes.TOTAL as TOTAL,
            ' . store_prefix() . 'nexo_commandes.TOTAL_REFUND as TOTAL_REFUND,
            ' . store_prefix() . 'nexo_commandes.DATE_CREATION as DATE,
            ' . store_prefix() . 'nexo_commandes.CODE as CODE,
            ' . store_prefix() . 'nexo_commandes_produits.QUANTITE,
            ' . store_prefix() . 'nexo_articles.DESIGN as DESIGN,
            ' . store_prefix() . 'nexo_commandes_produits.PRIX as PRIX,
            ' . store_prefix() . 'nexo_commandes.TYPE as TYPE,
            ' . store_prefix() . 'nexo_commandes.REMISE_TYPE as REMISE_TYPE,
            ' . store_prefix() . 'nexo_commandes.REMISE,
            ' . store_prefix() . 'nexo_commandes.REMISE_PERCENT,
            ' . store_prefix() . 'nexo_commandes.PAYMENT_TYPE,
            aauth_users.name as AUTHOR_NAME,
            aauth_users.id as AUTHOR_ID,
        ' )
        ->from( store_prefix() . 'nexo_commandes' )
        ->join(
            store_prefix() . 'nexo_commandes_produits',
            store_prefix() . 'nexo_commandes_produits.REF_COMMAND_CODE = ' . store_prefix() . 'nexo_commandes.CODE'
        )
        ->join(
            'aauth_users',
            'aauth_users.id = ' . store_prefix() . 'nexo_commandes.AUTHOR'
        )
        ->join(
            store_prefix() . 'nexo_articles',
            store_prefix() . 'nexo_articles.CODEBAR = ' . store_prefix() . 'nexo_commandes_produits.REF_PRODUCT_CODEBAR'
        )
        ->where( store_prefix() . 'nexo_commandes.DATE_CREATION >=', $startOfDay )
        ->where( store_prefix() . 'nexo_commandes.DATE_CREATION <=', $endOfDay )
        ->where_in( store_prefix() . 'nexo_commandes.TYPE', [ 'nexo_order_comptant', 'nexo_order_refunded', 'nexo_order_partially_refunded' ])
        ->get();

        $orders     =   $query->result_array();

        /**
         * retreive the payments
         */
        foreach( $orders as &$order ) {
            $order[ 'payments' ]   =   $this->db
            ->where( 'REF_COMMAND_CODE', $order[ 'CODE' ])
            ->get( store_prefix() . 'nexo_commandes_paiements' )
            ->result_array();

            /**
             * refunds
             * @var array
             */
            $order[ 'refunds' ]     =   $this->db->where( 'REF_ORDER', $order[ 'ID' ] )
                ->get( store_prefix() . 'nexo_commandes_refunds' )
                ->result_array();

            foreach( $order[ 'refunds' ] as &$refund ) {
                $refund[ 'products' ]   =   $this->db->where( 'REF_REFUND', $refund[ 'ID' ])
                    ->get( store_prefix() . 'nexo_commandes_refunds_products' )
                    ->result_array();
            }
        }

        $this->response( $orders, 200 );
    }

}
