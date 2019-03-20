<?php
class ApiWooCommerce extends Tendoo_Api
{
    /**
     * Sync Down categories 
     * received form WooCommerce
     * @return json
     */
    public function syncDownCategories()
    {
        $this->db->truncate( 'nexo_categories' );
        $categories     =   $this->post( 'categories' );
        $batchCategories    =   [];

        foreach( $categories as $cat ) {
            $batchCategories[]  =   [
                'ID'                =>  $cat[ 'cat_ID' ],
                'NOM'               =>  $cat[ 'cat_name' ],
                'PARENT_REF_ID'     =>  $cat[ 'parent' ],
                'DESCRIPTION'       =>  $cat[ 'description' ],
                'AUTHOR'            =>  $this->post( 'author' ),
                'DATE_CREATION'     =>  date_now(),
                'DATE_MOD'          =>  date_now()
            ];
        }

        $this->db->insert_batch( 'nexo_categories', $batchCategories );

        return $this->response([
            'type'      =>  'success',
            'message'   =>  __( 'L\'importation s\'est correctement déroulée', 'nexo' )
        ]);
    }

    /**
     * Sync Down Order coming 
     * from WooCommerce
     * @return json
     */
    public function syncDownSingleOrder()
    {
        $this->load->model( 'Nexo_Checkout' );
        $this->load->module_model( 'nexo', 'NexoCustomersModel' );
        $this->load->module_model( 'nexo', 'NexoItems' );

        /**
         * it seems like the data aren't well transfered
         * as an array. So it's transfered as a json, which need 
         * to be decoded.
         */
        $data           =   json_decode( $this->post( 'data' ), true );
        $order          =   $data[ 'order' ];
        $customer       =   $data[ 'customer' ];
        $rawProducts    =   $data[ 'products' ];

        file_put_contents( 'sync-down-order.json', $this->post( 'data' ) );

        /**
         * Check first if we have a similar customer
         */
        $customerCreationResponse       =   $this->NexoCustomersModel->create([
            'name'                      =>  @$customer[ 'first_name' ],
            'surname'                   =>  @$customer[ 'last_name' ],
            'email'                     =>  @$customer[ 'email' ],

            'billing_name'              =>  @$customer[ 'billing' ][ 'first_name' ],
            'billing_surname'           =>  @$customer[ 'billing' ][ 'last_name' ],
            'billing_address_1'         =>  @$customer[ 'billing' ][ 'address_1' ],
            'billing_address_2'         =>  @$customer[ 'billing' ][ 'address_2' ],
            'billing_city'              =>  @$customer[ 'billing' ][ 'city' ],
            'billing_pobox'             =>  @$customer[ 'billing' ][ 'postcode' ],
            'billing_country'           =>  @$customer[ 'billing' ][ 'country' ],
            'billing_state'             =>  @$customer[ 'billing' ][ 'state' ],
            'billing_phone'             =>  @$customer[ 'billing' ][ 'phone' ],
            'billing_email'             =>  @$customer[ 'billing' ][ 'email' ],

            'shipping_name'             =>  @$customer[ 'shipping' ][ 'first_name' ],
            'shipping_surname'          =>  @$customer[ 'shipping' ][ 'last_name' ],
            'shipping_address_1'        =>  @$customer[ 'shipping' ][ 'address_1' ],
            'shipping_address_2'        =>  @$customer[ 'shipping' ][ 'address_2' ],
            'shipping_city'             =>  @$customer[ 'shipping' ][ 'city' ],
            'shipping_pobox'            =>  @$customer[ 'shipping' ][ 'postcode' ],
            'shipping_country'          =>  @$customer[ 'shipping' ][ 'country' ],
            'shipping_state'            =>  @$customer[ 'shipping' ][ 'state' ],
            'shipping_phone'            =>  @$customer[ 'shipping' ][ 'phone' ],
            'shipping_email'            =>  @$customer[ 'shipping' ][ 'email' ],
        ]);

        $customer_id    =   $customerCreationResponse[ 'customer' ][ 'ID' ];

        /**
         * Lopping the items and treat the meta 
         * data
         */
        $products       =   [];
        foreach( $rawProducts as $product ) {
            /**
             * Let's find if the item exist otherwise
             * use it as a quick item
             */
            $savedProduct   =   $this->NexoItems->getUsingSKU( $product[ 'sku' ] );
            $isFlash        =   empty( $savedProduct );

            /**
             * build product metas
             */
            $productMetas   =   [];
            if ( isset( $product[ 'meta_data' ] ) ) {
                foreach( $product[ 'meta_data' ] as $meta ) {
                    $productMetas[ $meta[ 'key' ] ] =   $meta[ 'value' ];
                }
            }

            /**
             * The item might need to have extra data.
             * So it should be available under a filter
             */
            $filtredProduct         =   $this->events->apply_filters( 'woocommerce_post_item_data', [
                'product'       =>  [
                    'codebar'           =>  @$savedProduct[ 'CODEBAR' ] ?: $product[ 'sku' ],
                    'qte_added'         =>  $product[ 'quantity' ],
                    'inline'            =>  intval( $isFlash ),
                    'sale_price'        =>  floatval( $product[ 'subtotal' ] ) / floatval( $product[ 'quantity' ] ),
                    'discount_amount'   =>  0,
                    'discount_type'     =>  'flat',
                    'discount_percent'  =>  0,
                    'name'              =>  $product[ 'name' ],
                    'alternative_name'  =>  '',
                    'metas'             =>  $productMetas,
                    'stock_enabled'     =>  $savedProduct[ 'STOCK_ENABLED' ]
                ],
                'raw'           =>  $product
            ]);

            $products[]             =   $filtredProduct[ 'product' ];
        }

        /**
         * Handle Order metas
         */
        $metas      =   [];
        if( isset( $order[ 'meta_data' ] ) ) {
            foreach( $order[ 'meta_data' ] as $meta ) {
                $metas[ $meta[ 'key' ] ]    =   $meta[ 'value' ];
            }
        }

        /**
         * Handle the payment
         */
        $payments           =   [];

        /**
         * If the payment is a Cash On Delivery order
         * for unsupported payment. We'll marke the order as
         * quote order.
         */
        switch( $order[ 'payment_method' ] ) {
            case 'cod': 
                $payments       =   [
                    [
                        'namespace'     =>  'cod',
                        'amount'        =>  0 // otherwise the order will be marked as paid
                    ]
                ];
            break;
            case 'cheque':
                $payments       =   [
                    [
                        'namespace'     =>  $order[ 'payment_method' ],
                        'amount'        =>  $order[ 'total' ]
                    ]
                ];
            break;
            case 'bacs':
                $payments       =   [
                    [
                        'namespace'     =>  'bank',
                        'amount'        =>  $order[ 'total' ]
                    ]
                ];
            break;
                $payments       =   [
                    [
                        'namespace'     =>  'unknow',
                        'amount'        =>  $order[ 'total' ]
                    ]
                ];
            default: 
            break;
        }


        /**
         * Save an order
         */
        $orderData      =   $this->events->apply_filters( 'woocommerce_post_order_data', [
            'order'     =>  [
                'shipping'              =>  [
                    'id'                =>  $customerCreationResponse[ 'customer' ][ 'shipping' ][ 'id' ],
                    'name'              =>  $order[ 'billing' ][ 'first_name' ],
                    'surname'           =>  $order[ 'billing' ][ 'last_name' ],
                    'address_1'         =>  $order[ 'billing' ][ 'address_1' ],
                    'address_2'         =>  $order[ 'billing' ][ 'address_2' ],
                    'city'              =>  $order[ 'billing' ][ 'city' ],
                    'country'           =>  $order[ 'billing' ][ 'state' ],
                    'pobox'             =>  $order[ 'billing' ][ 'postcode' ],
                    'state'             =>  $order[ 'billing' ][ 'state' ],
                    'enterprise'        =>  $order[ 'billing' ][ 'company' ],
                    'title'             =>  __( 'Commande Web', 'nexo' ),
                    'price'             =>  $order[ 'shipping_total' ],
                    'email'             =>  $order[ 'billing' ][ 'email' ],
                    'phone'             =>  $order[ 'billing' ][ 'phone' ],
                ],
                'TOTAL'                 =>  $order[ 'total' ],
                'RISTOURNE'             =>  0,
                'REMISE'                =>  $order[ 'discount_total' ],
                'REMISE_TYPE'           =>  '',
                'REMISE_PERCENT'        =>  0,
                'RABAIS'                =>  0,
                'HMB_DISCOUNT'          =>  0,
                'GROUP_DISCOUNT'        =>  0,
                'SOMME_PERCU'           =>  0,
                'DISCOUNT_TYPE'         =>  '',
                'PAYMENT_TYPE'          =>  $payments[0][ 'namespace' ],
                'TVA'                   =>  0,
                'DESCRIPTION'           =>  '',
                'TITRE'                 =>  __( 'Online Order', 'nexo' ),
                'SHIPPING_AMOUNT'       =>  $order[ 'shipping_total' ],
                'REF_SHIPPING_ADDRESS'  =>  0,
                'REF_TAX'               =>  0,
                'REF_CLIENT'            =>  $customer_id,
                'REF_REGISTER'          =>  0, // need to define the default register used to place online orders
                'TYPE'                  =>  '',
                'ITEMS'                 =>  $products,
                'payments'              =>  $payments,
                'metas'                 =>  $metas,
                'REGISTER_ID'           =>  0, // register are enabled, what is the url which should be used for online orders
            ],
            'raw'               =>  [
                'order'         =>  $order,
                'products'      =>  $products,
                'customer'      =>  $customer
            ]
        ]);

        $response   =  $this->Nexo_Checkout->postOrder( $orderData[ 'order' ], User::id() );

        return $this->response( $response );
    }

    /**
     * Sync Down Customers
     * from WooCommerce
     * @return json
     */
    public function syncDownCustomers()
    {
        $this->load->module_model( 'nexo', 'NexoCustomersModel', 'customerModel' );
        
        $customers  =   $this->post( 'customers' );
        array_walk( $customers, function( &$customer ) {

            /**
             * infor type : shipping & billing
             */
            foreach([ 'shipping', 'billing' ] as $type ) {

                $keyMap     =   [
                    $type . '_first_name'       =>  $type . '_name',
                    $type . '_last_name'        =>  $type . '_surname',
                    $type . '_postcode'         =>  $type . '_pobox',
                    $type . '_company'          =>  $type . '_enterprise',
                ];
    
                foreach( $customer as $key => $value ) {
                    if ( in_array( $key, [ 'shipping_method' ] ) ) {
                        unset( $customer[ $key ] );
                    } else if ( in_array( $key, array_keys( $keyMap ) ) ) {
                        $customer[ $keyMap[ $key ] ]    =   $customer[ $key ];
                        unset( $customer[ $key ] );
                    }
                }
            }
        });

        /**
         * start creating customers
         */
        $response   =   [];
        foreach( ( array ) $customers as $customer ) {
            $response[]   =   $this->customerModel->create( $customer );
        }

        return $this->response([
            'status'    =>  'success',
            'message'   =>  __( 'Le processus de création de clients s\'est achevée', 'nexo' ),
            'reponses'  =>  $response
        ]);
    }
}