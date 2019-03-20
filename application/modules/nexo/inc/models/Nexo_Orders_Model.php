<?php
class Nexo_Orders_Model extends Tendoo_Module
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($order_id = null)
    {
        $this->db->select( '*,' . 
            'aauth_users.name as AUTHOR_NAME'
        )
        ->from( store_prefix() . 'nexo_commandes' )
        ->join( 'aauth_users', 'aauth_users.id = ' . store_prefix() . 'nexo_commandes.AUTHOR' );

        if ($order_id != null && ! is_array($order_id)) {
            $this->db->where( store_prefix() . 'nexo_commandes.ID', $order_id);
        } elseif (is_array($order_id)) {
            foreach ($order_id as $mark => $value) {
                $this->db->where($mark, $value);
            }
        }

        $query    =    $this->db->get();

        if ($query->result_array()) {
            $result         =   $query->result_array();
            return $result[0];
        }
        return false;
    }

    /**
     * get orders by cashier from 
     * a time range if provided
     * @param int cashier id
     * @param string from datetime
     * @param string to datetime
     * @return array of orders
     */
    public function getByCashiers( $cashierId, $from, $to ) 
    {
        $this->db->select( '*,' . 
            'aauth_users.name as AUTHOR_NAME'
        )
        ->from( store_prefix() . 'nexo_commandes' )
        ->join( 'aauth_users', 'aauth_users.id = ' . store_prefix() . 'nexo_commandes.AUTHOR' )
        ->where( 'AUTHOR', $cashierId )
        ->where( 'DATE_CREATION >=', $from )
        ->where( 'DATE_CREATION <=', $to );

        $query    =    $this->db->get();

        return $query->result_array();
    }

    /**
     * Get order items
     * @param int order id
     * @return array
     */
    public function getOrderItems( $order_id )
    {
        $query    =    $this->db
        ->where( store_prefix() . 'nexo_commandes.ID', $order_id)
        ->select( '*,
        ' . store_prefix() . 'nexo_commandes.DATE_CREATION as DATE_CREATION,
        ' . store_prefix() . 'nexo_commandes.DATE_MOD as DATE_MOD,
        ' . store_prefix() . 'nexo_commandes.SHIPPING_AMOUNT as SHIPPING_AMOUNT,
        ' . store_prefix() . 'nexo_clients.NOM as customer_name,
        ' . store_prefix() . 'nexo_clients.TEL as customer_phone,
        ' . store_prefix() . 'nexo_commandes.ID as ORDER_ID,
        ' . store_prefix() . 'nexo_commandes.ID as ID,
        ' . store_prefix() . 'nexo_commandes.DESCRIPTION as DESCRIPTION,
        ' . store_prefix() . 'nexo_commandes.AUTHOR as AUTHOR,
        aauth_users.name as author_name,
        aauth_users.name as AUTHOR_NAME,' )
        ->from( store_prefix() . 'nexo_commandes' )
        ->join( store_prefix() . 'nexo_clients', store_prefix() . 'nexo_commandes.REF_CLIENT = ' . store_prefix() . 'nexo_clients.ID' )
        ->join( 'aauth_users', 'aauth_users.id = ' . store_prefix() . 'nexo_commandes.AUTHOR' )
        ->get();

        if ($query->result_array()) {
            $data            =    $query->result_array();
            // var_dump( $query->result_array() );die;
            $sub_query        =    $this->db
            ->select('*,
            ' . store_prefix() . 'nexo_commandes_produits.ID as ITEM_ID,
			' . store_prefix() . 'nexo_commandes_produits.QUANTITE as QTE_ADDED,
			' . store_prefix() . 'nexo_commandes_produits.NAME as DESIGN,
			' . store_prefix() . 'nexo_commandes_produits.PRIX as PRIX_DE_VENTE,
			' . store_prefix() . 'nexo_commandes_produits.PRIX as PRIX_DE_VENTE_TTC,
			' . store_prefix() . 'nexo_commandes_produits.PRIX as PRIX,
			' . store_prefix() . 'nexo_articles.DESIGN as ORIGINAL_NAME' )
            ->from( store_prefix() . 'nexo_commandes')
            ->join( store_prefix() . 'nexo_commandes_produits', store_prefix() . 'nexo_commandes.CODE = ' . store_prefix() . 'nexo_commandes_produits.REF_COMMAND_CODE', 'inner')
            ->join( store_prefix() . 'nexo_articles', store_prefix() . 'nexo_articles.CODEBAR = ' . store_prefix() . 'nexo_commandes_produits.REF_PRODUCT_CODEBAR', 'left')
            ->where( 'REF_COMMAND_CODE', $data[0][ 'CODE' ])
            ->get();

            $sub_data    = $sub_query->result_array();

            // load items meta
            foreach( $sub_data as $key => $item ) {
                $metas      =   $this->db->where( store_prefix() . 'nexo_commandes_produits_meta.REF_COMMAND_PRODUCT', $item[ 'ITEM_ID' ] )
                ->get( store_prefix() . 'nexo_commandes_produits_meta' )->result();

                if ( $metas ) {
                    $sub_data[ $key ][ 'metas' ]    =   [];
                }

                foreach( $metas as $meta ) {
                    $sub_data[ $key ][ 'metas' ][ $meta->KEY ]      =   $meta->VALUE;
                }
            }

            return $sub_data;
        }
        return [];
    }

    /**
     * Proceed to payment
     * @param int order id
     * @param int amount
     * @param string payment namespace
     * @return json
     */
    public function addPayment( $order_id, $amount, $namespace )
    {
        $this->load->module_model( 'nexo', 'NexoLogModel', 'history' );

        $order  =   $this->get( $order_id );
        
        if ( $order ) {
            if ( $order[ 'TYPE' ] !== 'nexo_order_comptant' ) {
                if ( in_array( $namespace, array_keys( $this->config->item( 'nexo_payments_types' ) ) ) ) {
                    $this->db->insert( store_prefix() . 'nexo_commandes_paiements', [
                        'REF_COMMAND_CODE'  =>  $order[ 'CODE' ],
                        'MONTANT'           =>  $amount,
                        'AUTHOR'            =>  User::id(),
                        'PAYMENT_TYPE'      =>  $namespace,
                        'DATE_CREATION'     =>  date_now(),
                        'OPERATION'         =>  'incoming'
                    ]);

                    $totalTendered      =   floatval( $order[ 'SOMME_PERCU' ] ) + floatval( $amount );

                    /**
                     * Detect the right payment status
                     * for the order according to the payment made
                     */
                    switch( $totalTendered ) {
                        case $totalTendered >= floatval( $order[ 'TOTAL' ] ): 
                            $orderPaymentStatus     =   'nexo_order_comptant';
                        break;
                        case ( $totalTendered < floatval( $order[ 'TOTAL' ] ) && $totalTendered > 0 ): 
                            $orderPaymentStatus     =   'nexo_order_advance';
                        break;
                        default: 
                            $orderPaymentStatus     =   'nexo_order_devis';
                        break;
                    }

                    $data   =   [
                        'DATE_MOD'      =>  date_now(),
                        'SOMME_PERCU'   =>  $totalTendered,
                        'TYPE'          =>  $orderPaymentStatus
                    ];

                    /**
                     * if the order has changed his status from whatever to "nexo_order_comptant", the 
                     * status of the order should change from hold or ongoing to complete
                     */
                    if ( $orderPaymentStatus === 'nexo_order_comptant' ) {
                        $data[ 'STATUS' ]   =   'completed';
                    }

                    $this->db->where( 'ID', $order[ 'ID' ] )->update( store_prefix() . 'nexo_commandes', $data );
                    
                    $this->history->log( 
                        __( 'Paiement d\'une commande', 'nexo' ),
                        sprintf( 
                            __( 'La commande ayant pour code <strong>%s</strong> a reçu un nouveau paiement : <strong>%s</strong>, effectué par <strong>%s</strong>', 'nexo' ), 
                            $order[ 'CODE' ],
                            $amount . ' &mdash; ' . $namespace,
                            User::pseudo()
                        )
                    );

                    return [
                        'status'    =>  'success',
                        'message'   =>  __( 'Le paiement a été correctement ajouté', 'nexo' )
                    ];
                }
                return [
                    'status'    =>  'failed',
                    'message'   =>  __( 'Impossible d\'utiliser un moyen de paiement non enregistré !', 'nexo' )
                ];
            }

            return [
                'status'    =>  'failed',
                'message'   =>  __( 'Impossible d\'effectuer le paiement pour une commande complète !', 'nexo' )
            ];
        }

        return $this->__notFoundOrder();
    }

    /**
     * return an array of payment made 
     * for an order
     * @param int order id
     * @param array of payments made
     */
    public function getPayments( $order_id )
    {
        $order  =   $this->get( $order_id );

        if ( $order ) {
            $payments   =   $this->db->where( 'REF_COMMAND_CODE', $order[ 'CODE' ] )
                ->order_by( 'ID', 'desc' )
                ->get( store_prefix() . 'nexo_commandes_paiements' )
                ->result_array();

            foreach( $payments as $index => $payment ) {
                $author     =   $this->db->where( 'id', $payment[ 'AUTHOR' ] )
                    ->get( 'aauth_users' )
                    ->result_array();
                
                $payments[ $index ][ 'author' ]     =   @$author[0];
            }

            return $payments;
        }

        return $this->__notFoundOrder();
    }

    /**
     * Return an Async Response
     * if an order has not been found
     * @return array
     */
    public function __notFoundOrder()
    {
        return [
            'status'    =>  'failed',
            'message'   =>  __( 'Impossible de retrouver la commande demandée', 'nexo' )
        ];
    }

    /**
     * Refund an order using the 
     * provided data
     * @param int order id
     * @param int amount
     * @param string type
     * @return json
     */
    public function refund( $data ) 
    {
        /**
         * Exposes
         * $order_id, $total, $sub_total, $type, $description, $payment_type, $products, $refund_shipping_fees
         */
        extract( $data );

        $this->load->module_model( 'nexo', 'NexoLogModel', 'history' );

        $order  =   $this->get( $order_id );
        
        if ( $order ) {
            if ( in_array( $order[ 'TYPE' ],  [ 'nexo_order_comptant', 'nexo_order_advance', 'nexo_order_partially_refunded' ] ) ) {
                
                $result     =   $this->registerRefundTransaction( $data, $order );

                $this->history->log( 
                    __( 'Remboursement', 'nexo' ),
                    sprintf( 
                        __( 'La commande ayant pour code <strong>%s</strong> a été remboursé : <strong>%s</strong>, effectué par <strong>%s</strong>', 'nexo' ), 
                        $order[ 'CODE' ],
                        $total,
                        User::pseudo()
                    )
                );

                if ( $type === 'withstock' ) {
                    $refund_id  =   $result[ 'refund_id' ];
                    $this->__handleProductStock( compact( 'products', 'order', 'refund_id', 'refund_shipping_fees' ) );
                }

                return $this->__success( __( 'Le remboursement a correctement été effectuée', 'nexo' ) );
            }
            return $this->__fail( __( 'Le remboursement ne peut être fait que pour des commandes ayant reçu un paiement.', 'nexo' ) );
        }
        return $this->__notFoundOrder();
    }

    /**
     * Register refund transaction
     * @param data
     * @return array
     */
    public function registerRefundTransaction( $data, $order )
    {
        /**
         * Exposes
         * $order_id, $total, $sub_total, $type, $description, $payment_type, $products, $refund_shipping_fees
         */
        extract( $data );

        $this->db->insert( store_prefix() . 'nexo_commandes_refunds', [
            'TOTAL'             =>  $total,
            'SUB_TOTAL'         =>  $sub_total ?: $total,
            'DATE_CREATION'     =>  date_now(),
            'AUTHOR'            =>  User::id(),
            'REF_ORDER'         =>  $order_id,
            'TYPE'              =>  $type,
            'PAYMENT_TYPE'      =>  $payment_type,
            'SHIPPING'          =>  $refund_shipping_fees ? $order[ 'SHIPPING_AMOUNT' ] : 0,
            'DESCRIPTION'       =>  $description
        ]);  
        
        $refund_id  =   $this->db->insert_id();
        
        $this->db->insert( store_prefix() . 'nexo_commandes_paiements', [
            'REF_COMMAND_CODE'  =>  $order[ 'CODE' ],
            'MONTANT'           =>  $total,
            'OPERATION'         =>  'outcoming',
            'PAYMENT_TYPE'      =>  $payment_type,
            'AUTHOR'            =>  User::id(),
            'DATE_CREATION'     =>  date_now(),
        ]);

        $last_order_payment_id      =   $this->db->insert_id();

        $newTotal  =   floatval( $order[ 'TOTAL' ] ) - floatval( $total );

        $this->db->where( 'ID', $order[ 'ID' ] )->update( store_prefix() . 'nexo_commandes', [
            'DATE_MOD'  =>  date_now(),
            'TOTAL'     =>  $newTotal,
            'TYPE'      =>  $newTotal <= 0 ? 'nexo_order_refunded' : 'nexo_order_partially_refunded'
        ]);

        
        /**
         * calculate the refund amount
         */
        $this->recalculateOrderRefund( $order_id );

        return [
            'status'                =>  'success',
            'message'               =>  __( 'La transaction de remboursement a été enregistrée', 'nexo' ),
            'refund_id'             =>  $refund_id,
            'last_order_payment_id' =>  $last_order_payment_id
        ];
    }

    /**
     * handle the way order product
     * are handled regarding the refund
     * @return void
     */
    private function __handleProductStock( $data )
    {
        $this->load->module_model( 'nexo', 'NexoProducts', 'productModel' );
        
        extract( $data );
        /**
         * expose
         * 'products', 'order', 'refund_id', 'refund_shipping_fees
         */

        $order_items   =   $this->getOrderItems( $order[ 'ID' ] );
        
        foreach ( $order_items as $item ) {
            array_walk( $products, function( $_item ) use ( $item, $order, $refund_id, $data ) {
                if ( $_item[ 'ITEM_ID' ] === $item[ 'ITEM_ID' ] ) {
                    $finalResult    =   floatval( $item[ 'QUANTITE' ] ) - floatval( $_item[ 'refund_quantity' ] );

                    /**
                     * we should remove the item 
                     * from the order and recalculate the order
                     */
                    if ( $finalResult == 0 ) {
                        $this->removeItemFromOrder( $order[ 'ID' ], $item[ 'ITEM_ID' ], $data );
                    } else {
                        $this->setOrderItemQuantity( $order[ 'ID' ], $item[ 'ITEM_ID' ], $finalResult, $data );
                    }

                    $total_price    =   floatval( $_item[ 'PRIX' ] ) * floatval( $_item[ 'refund_quantity' ] );
    
                    $this->db->insert( store_prefix() . 'nexo_commandes_refunds_products', [
                        'REF_ITEM'      =>  $_item[ 'ID' ],
                        'NAME'          =>  $_item[ 'NAME' ],
                        'STATUS'        =>  $_item[ 'refund_state' ],
                        'REF_REFUND'    =>  $refund_id,
                        'DATE_CREATION' =>  date_now(),
                        'AUTHOR'        =>  User::id(),
                        'PRICE'         =>  $_item[ 'PRIX' ],
                        'QUANTITY'      =>  $_item[ 'refund_quantity' ],
                        'TOTAL_PRICE'   =>  $total_price
                    ]);

                    $this->productModel->addStockFlow( $_item[ 'ID' ], [
                        'TYPE'              =>  'usable',
                        'QUANTITE'          =>  $_item[ 'refund_quantity' ],
                        'REF_COMMAND_CODE'  =>  $order[ 'CODE' ],
                        'UNIT_PRICE'        =>  $_item[ 'PRIX' ],
                        'TOTAL_PRICE'       =>  $total_price,
                        'AUTHOR'            =>  User::id(),
                        'DATE_CREATION'     =>  date_now(),
                    ]);

                    /**
                     * if a stock is defective, there will be a stock return and
                     * the item will be removed from the stock and 
                     * marked as defective
                     */
                    if ( $_item[ 'refund_state' ] === 'defective' ) {
                        $this->productModel->addStockFlow( $_item[ 'ID' ], [
                            'TYPE'              =>  'defective',
                            'QUANTITE'          =>  $_item[ 'refund_quantity' ],
                            'REF_COMMAND_CODE'  =>  $order[ 'CODE' ],
                            'UNIT_PRICE'        =>  $_item[ 'PRIX' ],
                            'TOTAL_PRICE'       =>  $total_price,
                            'AUTHOR'            =>  User::id(),
                            'DATE_CREATION'     =>  date_now(),
                        ]);
                    }                   
                }
            });
        }
    }

    /**
     * Set order item quantity
     * @param int order id
     * @param int item id
     * @param int quantity
     * @param array extra post details
     * @return array
     */
    public function setOrderItemQuantity( $order_id, $item_id, $quantity, $data )
    {
        $this->db->where( 'ID', $item_id )
            ->update( store_prefix() . 'nexo_commandes_produits', [
                'QUANTITE'  =>  $quantity
            ]);

        $data[ 'is-refund' ]    =   true;

        return $this->recalculateOrder( $order_id, $data );
    }

    /**
     * remove item from order
     * @param int order id
     * @param int item id
     * @param array extra post details
     * @return array
     */
    public function removeItemFromOrder( $order_id, $item_id, $data )
    {
        $this->db->where( 'ID', $item_id )
            ->delete( store_prefix() . 'nexo_commandes_produits' );
        $this->db->where( 'REF_COMMAND_PRODUCT', $item_id )
            ->delete( store_prefix() . 'nexo_commandes_produits_meta' );
        
        $data[ 'is-refund' ]    =   true;
            
        return $this->recalculateOrder( $order_id, $data );
    }

    /**
     * Calculate Refund total
     * @param int order id
     * @return void
     */
    public function recalculateOrderRefund( $order_id )
    {
        $refunds    =   $this->order_refunds( $order_id );
        if ( $refunds ) {
            $totalRefund    =   0;
            array_map( function( $refund ) use ( &$totalRefund ) {
                $totalRefund    +=   floatval( $refund[ 'TOTAL' ] );
            }, $refunds );

            $this->db->where( 'ID', $order_id )
                ->update( store_prefix() . 'nexo_commandes', [
                    'TOTAL_REFUND'  =>  $totalRefund
                ]);
        }
    }

    /**
     * Get order VAT
     * @param array order
     * @param int number
     * @return int
     */
    private function __getOrderVat( $order, $total )
    {
        $vatOption  =   store_option( 'nexo_vat_type', 'disabled' );

        if ( $vatOption !== 'disabled' ) {
            if ( $vatOption === 'fixed' && store_option( 'nexo_vat_percent', 0 ) !== 0 ) {
                $operation_value    =   ( 
                    $total * floatval( store_option( 'nexo_vat_percent', 0 ) )
                );

                return $operation_value / 100;
            } else {
                $tax        =   $this->getOrderTax( $order[ 'REF_TAX' ] );
                if ( $tax ) {
                    return ( $total * floatval( $tax[0][ 'RATE' ] ) ) / 100;
                }
            }
        }
        return 0;
    }

    /**
     * Get Order TAx
     * @param int tax id
     * @return array
     */
    public function getOrderTax( $tax_id )
    {
        $tax    =   $this->db->where( 'ID', $tax_id )
            ->get( store_prefix() . 'nexo_taxes' )
            ->result_array();
        return $tax;
    }

    /**
     * Get order discount
     * @param array order
     * @param int total
     * @return int
     */
    private function __getOrderDiscount( $order, $total )
    {
        if ( $order[ 'REMISE_TYPE' ] === 'percent' && $order[ 'REMISE_PERCENT' ] !== '0' ) {
            return ( $total * $order[ 'REMISE_PERCENT' ] ) / 100;
        }
        return $order[ 'REMISE' ];
    }

    /**
     * Get orders values
     * @param int order id
     * @return array
     */
    public function getOrderValues( $order_id )
    {
        $items  =   $this->getOrderItems( $order_id );
        $order  =   $this->get( $order_id );
        $total  =   0;

        foreach ( $items as $item ) {
            $total  +=  floatval( $item[ 'PRIX_TOTAL' ] );
        }

        $total      -=  $totalDiscount  =   $this->__getOrderDiscount( $order, $total );
        $total      +=  $totalVat       =   $this->__getOrderVat( $order, $total );
        $total      +=  $totalShipping  =   $this->__getOrderShippingFees( $order, false );

        return compact( 'total', 'totalDiscount', 'totalShipping', 'totalVat' );
    }

    /**
     * Calculate ORder
     * @param int order id
     * @return array
     */
    public function calculateOrder( $order_id ) 
    {
        $details    =   $this->getOrdersValues( $order_id );
        
        extract( $details );

        $order      =   $this->get( $order_id );

        switch( $total ) {
            case $total > floaval( $order[ 'SOMME_PERCU' ]) && floaval( $order[ 'SOMME_PERCU' ]) > 0 : 
                $type   =   'nexo_order_advanced';
            break;
            case $total > floaval( $order[ 'SOMME_PERCU' ]) && floaval( $order[ 'SOMME_PERCU' ]) === 0 : 
                $type   =   'nexo_order_devis';
            break;
            case $total <= floaval( $order[ 'SOMME_PERCU' ]) && floaval( $order[ 'SOMME_PERCU' ]) > 0 : 
                $type   =   'nexo_order_complete';
            break;
        }

        $this->db->where( 'ID', $order_id )
            ->update( store_prefix() . 'nexo_commandes', [
                'TOTAL'             =>  $total,
                'TVA'               =>  $totalVat,
                'REMISE'            =>  $totalDiscount,
                'SHIPPING_AMOUNT'   =>  $totalShipping,
                'DATE_MOD'          =>  date_now(),
                'TYPE'              =>  $type
            ]);
    }

    /**
     * Recalculate an order
     * @param int order id
     * @return array
     */
    public function recalculateOrder( $order_id, $data = [] )
    {
        $items  =   $this->getOrderItems( $order_id );
        $order  =   $this->get( $order_id );
        $total  =   0;

        /**
         * calculating order sub total
         */
        foreach ( $items as $item ) {
            $total  +=  floatval( $item[ 'PRIX_TOTAL' ] );
        }

        $total      -=  $this->__getOrderDiscount( $order, $total );
        $total      +=  ( $vat    =   $this->__getOrderVat( $order, $total ) );
        $total      +=  $this->__getOrderShippingFees( $order, floatval( @$data[ 'refund_shipping_fees' ] ) );

        $orderData  =   [
            'TOTAL'     =>  $total,
            'TVA'       =>  $vat,
            'DATE_MOD'  =>  date_now()
        ];

        /**
         * let's reassign the type
         * to make sure there is no wrong
         * type assignation
         */
        if ( @$data[ 'is-refund' ] === true ) {
            $orderData[ 'TYPE' ]    =   $total > 0 ? 'nexo_order_partially_refunded' : 'nexo_order_refunded';
        } else {
            if ( $total <= floatval( $order[ 'SOMME_PERCU' ] ) ) {
                $orderData[ 'TYPE' ]    =   'nexo_order_comptant';
            } else if ( floatval( $order[ 'SOMME_PERCU' ] ) > 0 ) {
                $orderData[ 'TYPE' ]    =   'nexo_order_advance';
            } else {
                $orderData[ 'TYPE' ]    =   'nexo_order_devis';
            }
        }

        $this->db->where( 'ID', $order_id )
            ->update( store_prefix() . 'nexo_commandes', $orderData );
        
        return [
            'status'    =>  'success',
            'message'   =>  __( 'La commande a été mise à jour', 'nexo' )
        ];
    }

    /**
     * Get Shipping amount if it's 
     * refunded or not
     * @param array order
     * @return int
     */
    private function __getOrderShippingFees( $order, $refundShippingFees )
    {
        if ( ! $refundShippingFees ) {
            return $order[ 'SHIPPING_AMOUNT' ];
        }

        $this->db->where( 'ID', $order[ 'ID' ] )
            ->update( store_prefix() . 'nexo_commandes', [
                'SHIPPING_AMOUNT'  =>  0
            ]);

        return 0;
    }

    /**
     * Helper : return an Async response
     * with a failed shape
     * @param string message to use
     * @return array of AyncResponse
     */
    private function __fail( $message )
    {
        return [
            'status'    =>  'failed',
            'message'   =>  $message
        ];
    }
    
    /**
     * Helper : return an Async response
     * with a success shape
     * @param string message to use
     * @return array of AyncResponse
     */
    private function __success( $message )
    {
        return [
            'status'    =>  'success',
            'message'   =>  $message
        ];
    }

    /**
     * get order refund
     * @param int order id
     * @return array
     */
    public function order_refunds( $order_id )
    {
        $refunds    =   $this->db->where( 'REF_ORDER', $order_id )
            ->order_by( 'ID', 'desc' )
            ->get( store_prefix() . 'nexo_commandes_refunds' )
            ->result_array();
        
        foreach( $refunds as $index => $refund ) {
            $refunds_items  =   $this->db->where( 'REF_REFUND', $refund[ 'ID' ] )
                ->get( store_prefix() . 'nexo_commandes_refunds_products' )
                ->result_array();

            /**
             * add refunded items
             * to the query
             */
            $refunds[ $index ][ 'items' ]   =   $refunds_items;

            /**
             * get author of the refund
             */
            $author     =   $this->db->where( 'id', $refund[ 'AUTHOR' ] )
                ->get( 'aauth_users' )
                ->result_array();

            $refunds[ $index ][ 'author' ]  =   $author[0];
        }

        return $refunds;
    }

    /**
     * Get specific refund
     * @param int refund id
     * @return array
     */
    public function get_refund( $refund_id )
    {
        $refunds    =   $this->db->where( 'ID', $refund_id )
            ->get( store_prefix() . 'nexo_commandes_refunds' )
            ->result_array();

        if ( count( $refunds ) === 0 ) {
            return [];
        }
        
        foreach( $refunds as $index => $refund ) {
            $refunds_items  =   $this->db->where( 'REF_REFUND', $refund[ 'ID' ] )
                ->get( store_prefix() . 'nexo_commandes_refunds_products' )
                ->result_array();

            /**
             * add refunded items
             * to the query
             */
            $refunds[ $index ][ 'items' ]   =   $refunds_items;

            /**
             * get author of the refund
             */
            $author     =   $this->db->where( 'id', $refund[ 'AUTHOR' ] )
                ->get( 'aauth_users' )
                ->result_array();

            $refunds[ $index ][ 'author' ]  =   $author[0];
        }        

        return $refunds[0];
    }
}