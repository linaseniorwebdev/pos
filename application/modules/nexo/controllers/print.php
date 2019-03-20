<?php
use Dompdf\Dompdf;

class NexoPrintController extends CI_Model
{
    public function defaults()
    {
        show_404();
    }

    public function order_receipt($order_id = null)
    {
        if ($order_id != null) {
            $this->cache        =    new CI_Cache(array( 'adapter' => 'file', 'backup' => 'file', 'key_prefix'    =>    'nexo_order_' . store_prefix() ));

            if ($order_cache = $this->cache->get($order_id) && @$_GET[ 'refresh' ] != 'true') {
                echo $this->cache->get($order_id);
                return;
            }

			$this->load->library('parser');
            $this->load->model('Nexo_Checkout');
            $this->load->model('Nexo_Misc');

            global $Options;

            $data                		=   array();
            $data[ 'order' ]    		=   $this->Nexo_Checkout->get_order_products($order_id, true);
            $data[ 'cache' ]    		=   $this->cache;
            $data[ 'shipping' ]         =   $this->db->where( 'ref_order', $order_id )->get( store_prefix() . 'nexo_commandes_shippings' )->result_array();
            $data[ 'tax' ]              =   $this->Nexo_Misc->get_taxes( $data[ 'order' ][ 'order' ][0][ 'REF_TAX' ] );
            $allowed_order_for_print	=	$this->events->apply_filters( 'allowed_order_for_print', array( 'nexo_order_comptant' ) );
	

            // Allow only cash order to be printed
            // if ( ! in_array( $data[ 'order' ]['order'][0][ 'TYPE' ], $allowed_order_for_print ) ) {
            //     redirect(array( 'dashboard', 'nexo', 'orders', '?notice=print_disabled' ));
            // }

            if (count($data[ 'order' ]) == 0) {
                return show_error(sprintf(__('Impossible d\'afficher le ticket de caisse. Cette commande ne possède aucun article &mdash; <a href="%s">Retour en arrière</a>', 'nexo'), $_SERVER['HTTP_REFERER']));
            }

			// @since 2.7.9
            $data[ 'template' ]						=	array();
            $dateCreation                           =   new DateTime( $data[ 'order' ][ 'order' ][0][ 'DATE_CREATION' ] );
            $data[ 'template' ][ 'order_date' ]		=	$dateCreation->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
            $dateModification                       =   new DateTime( $data[ 'order' ][ 'order' ][0][ 'DATE_MOD' ] );
            $data[ 'template' ][ 'order_updated' ]  =	$dateModification->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
			$data[ 'template' ][ 'order_code' ]		=	$data[ 'order' ][ 'order' ][0][ 'CODE' ];
            $data[ 'template' ][ 'order_id' ]       =   $data[ 'order' ][ 'order' ][0][ 'ORDER_ID' ];
			$data[ 'template' ][ 'order_status' ]	=	$this->Nexo_Checkout->get_order_type($data[ 'order' ][ 'order' ][0][ 'TYPE' ]);
            $data[ 'template' ][ 'order_note' ]     =   $data[ 'order' ][ 'order' ][0][ 'DESCRIPTION' ];

			$data[ 'template' ][ 'order_cashier' ]	=	User::pseudo( $data[ 'order' ][ 'order' ][0][ 'AUTHOR' ] );
			$data[ 'template' ][ 'shop_name' ]		=	@$Options[ store_prefix() . 'site_name' ];
			$data[ 'template' ][ 'shop_pobox' ]		=	@$Options[ store_prefix() . 'nexo_shop_pobox' ];
			$data[ 'template' ][ 'shop_fax' ]		=	@$Options[ store_prefix() . 'nexo_shop_fax' ];
			$data[ 'template' ][ 'shop_email' ]     =	@$Options[ store_prefix() . 'nexo_shop_email' ];
			$data[ 'template' ][ 'shop_street' ]    =	@$Options[ store_prefix() . 'nexo_shop_street' ];
			$data[ 'template' ][ 'shop_phone' ]     =	@$Options[ store_prefix() . 'nexo_shop_phone' ];
            $data[ 'template' ][ 'customer_name' ]  =   $data[ 'order' ][ 'order' ][0][ 'customer_name' ];
            $data[ 'template' ][ 'customer_phone' ]  =   $data[ 'order' ][ 'order' ][0][ 'customer_phone' ];

            $data[ 'template' ][ 'delivery_address_1' ]     =   @$data[ 'shipping' ][0][ 'address_1' ];
            $data[ 'template' ][ 'delivery_address_2' ]     =   @$data[ 'shipping' ][0][ 'address_2' ];
            $data[ 'template' ][ 'city' ]               =   @$data[ 'shipping' ][0][ 'city' ];
            $data[ 'template' ][ 'country' ]            =   @$data[ 'shipping' ][0][ 'country' ];
            $data[ 'template' ][ 'name' ]               =   @$data[ 'shipping' ][0][ 'name' ];
            $data[ 'template' ][ 'phone' ]              =   @$data[ 'shipping' ][0][ 'phone' ];
            $data[ 'template' ][ 'surname' ]            =   @$data[ 'shipping' ][0][ 'surname' ];
            $data[ 'template' ][ 'state' ]              =   @$data[ 'shipping' ][0][ 'surname' ];
            $data[ 'template' ][ 'delivery_cost' ]      =   @$data[ 'shipping' ][0][ 'price' ];

            $filtered   =   $this->events->apply_filters( 'nexo_filter_receipt_template', [
                'template'          =>      $data[ 'template' ],
                'order'             =>      $data[ 'order' ][ 'order' ][0],
                'items'             =>      $data[ 'order' ][ 'products' ]
            ]);

            $data[ 'template' ]             =   $filtered[ 'template' ];
            $theme                          =	@$Options[ store_prefix() . 'nexo_receipt_theme' ] ? @$Options[ store_prefix() . 'nexo_receipt_theme' ] : 'default';
            $path                           =   '../modules/nexo/views/receipts/' . $theme . '.php';

            $this->load->view(
                $this->events->apply_filters( 'nexo_receipt_theme_path', $path ),
                $data,
                $theme
            );

        } else {
            die(__('Cette commande est introuvable.', 'nexo'));
        }
    }

    public function order_refund( $order_id = null )
    {
        // if ($order_cache = $this->cache->get($order_id) && @$_GET[ 'refresh' ] != 'true') {
        //     echo $this->cache->get($order_id);
        //     return;
        // }

        $this->load->library('parser');
        $this->load->model('Nexo_Checkout');

        global $Options;

        $data                		=   array();
        // $data[ 'order' ]    		=   $this->Nexo_Checkout->get_order_products($order_id, true);
        // $data[ 'stock' ]            =   $this->Nexo_Checkout->get_order_with_item_stock( $order_id );
        // $data[ 'cache' ]    		=   $this->cache;

        // if (count($data[ 'order' ]) == 0) {
        //     die(sprintf(__('Impossible d\'afficher le reçu de remboursement. Cette commande ne possède aucun article &mdash; <a href="%s">Retour en arrière</a>', 'nexo'), $_SERVER['HTTP_REFERER']));
        // }

        // @since 2.7.9
        $data[ 'template' ]						=	array();
        $data[ 'template' ][ 'order_date' ]		=	':orderDate'; // mdate( '%d/%m/%Y %g:%i %a', strtotime($data[ 'order' ][ 'order' ][0][ 'DATE_CREATION' ]));
        $data[ 'template' ][ 'order_updated' ]  =   ':orderUpdated'; // just to show the date when the order has been update
        $data[ 'template' ][ 'order_code' ]		=	':orderCode'; // $data[ 'order' ][ 'order' ][0][ 'CODE' ];
        $data[ 'template' ][ 'order_id' ]       =   ':orderId'; // $data[ 'order' ][ 'order' ][0][ 'ID' ];
        $data[ 'template' ][ 'order_status' ]	=	':orderStatus'; // $this->Nexo_Checkout->get_order_type($data[ 'order' ][ 'order' ][0][ 'TYPE' ]);
        $data[ 'template' ][ 'order_note' ]     =   ':orderNote'; // $data[ 'order' ][ 'order' ][0][ 'DESCRIPTION' ];            
        $data[ 'template' ][ 'order_cashier' ]	=	':orderCashier'; // User::pseudo( $data[ 'order' ][ 'order' ][0][ 'AUTHOR' ] );

        $data[ 'template' ][ 'shop_name' ]		=	@$Options[ store_prefix() . 'site_name' ];
        $data[ 'template' ][ 'shop_pobox' ]		=	@$Options[ store_prefix() . 'nexo_shop_pobox' ];
        $data[ 'template' ][ 'shop_fax' ]		=	@$Options[ store_prefix() . 'nexo_shop_fax' ];
        $data[ 'template' ][ 'shop_email' ]		=	@$Options[ store_prefix() . 'nexo_shop_email' ];
        $data[ 'template' ][ 'shop_street' ]    =	@$Options[ store_prefix() . 'nexo_shop_street' ];
        $data[ 'template' ][ 'shop_phone' ]	    =	@$Options[ store_prefix() . 'nexo_shop_phone' ];

        $theme                                  =	@$Options[ store_prefix() . 'nexo_refund_theme' ] ? @$Options[ store_prefix() . 'nexo_refund_theme' ] : 'default';

        $path   =   '../modules/nexo/views/refund/' . $theme . '.php';

        $this->load->view(
            $this->events->apply_filters( 'nexo_refund_theme_path', $path ),
            $data,
            $theme
        );
    }

    /**
     * Gestion des impressions des étiquettes des produits
    **/

    public function shipping_item_codebar($shipping_id = null)
    {
        if ($shipping_id  == null) {
            show_error(__('Arrivage non définie.', 'nexo'));
        }

        $this->cache        =    new CI_Cache(array('adapter' => 'file', 'backup' => 'file', 'key_prefix'    =>    'nexo_products_labels_' . store_prefix() ));

        if ($products_labels = $this->cache->get($shipping_id) && @$_GET[ 'refresh' ] != 'true') {
            echo $this->cache->get( $shipping_id );
            return;
        }

        $this->load->model('Nexo_Products');
        $this->load->model('Nexo_Shipping');

        global $Options;
        $pp_row                    =    ! empty($Options[ store_prefix() . 'nexo_products_labels' ]) ? @$Options[ store_prefix() . 'nexo_products_labels' ] : 4;

        $data                    =    array();
        $data[ 'shipping_id' ]    =    $shipping_id;
        $data[ 'pp_row'    ]        =    $pp_row;
        $data[ 'cache' ]    =    $this->cache;

        if (isset($_GET[ 'products_ids' ])) {
            $get        =    str_replace('%2C', ',', $_GET[ 'products_ids' ]);
            $ids        =    explode(',', $get);
            $products    =    array();
            foreach ($ids as $id) {
                // $unique_product        =    $this->Nexo_Products->get( store_prefix() . 'nexo_articles', $id, 'ID');
                $unique_product             =   $this->db->select( '*' )
                ->from( store_prefix() . 'nexo_arrivages' )
                ->join( store_prefix() . 'nexo_articles_stock_flow', store_prefix() . 'nexo_articles_stock_flow.REF_SHIPPING = ' . store_prefix() . 'nexo_arrivages.ID' )
                ->join( store_prefix() . 'nexo_fournisseurs', store_prefix() . 'nexo_fournisseurs.ID = ' . store_prefix() . 'nexo_articles_stock_flow.REF_PROVIDER' )
                ->join( store_prefix() . 'nexo_articles', store_prefix() . 'nexo_articles.CODEBAR = ' . store_prefix() . 'nexo_articles_stock_flow.REF_ARTICLE_BARCODE' )
                ->where( store_prefix() . 'nexo_arrivages.ID', $delivery_id )
                ->get()->result_array();
                
                // Si le produit existe
                if (count($unique_product) > 0) {
                    $products[]            =    $unique_product[0];
                }
            }
            // var_dump( $products );
            $data[ 'products' ]        =    $products;
        } else {
            $data[ 'products' ]        =    $this->Nexo_Products->get_products_by_shipping($shipping_id);
        }

        $this->load->view('../modules/nexo/views/products-labels/default.php', $data);
    }

    /**
     *  Return a PDF document with current order receipt
     *  @param int order id
     *  @return PDF document
    **/

    public function order_pdf( $order_id )
    {
        ob_start();
        $this->order_receipt( $order_id );
        $content    =   ob_get_clean();
        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml( $content );

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();

    }

    /**
     * Order Invoice
     * @param int order id
     * @return view
     */
    public function invoice( $order_id ) 
    {
        $this->load->library('parser');
        $this->load->model('Nexo_Checkout');
        $this->load->module_model( 'nexo', 'Nexo_Orders_Model', 'orderModel' );

        global $Options;

        $data                		=   array();
        $data[ 'order' ]    		=   $this->Nexo_Checkout->get_order_products($order_id, true);
        $data[ 'refunds' ]          =   $this->orderModel->order_refunds( $order_id );
        $data[ 'tax' ]              =   $this->Nexo_Misc->get_taxes( $data[ 'order' ][ 'order' ][0][ 'REF_TAX' ] );
        
        /**
         * We need to make sure that
         * the order use the customer shipping 
         * information or not.
         */
        $data[ 'shipping' ]         =   $this->Nexo_Checkout->getOrderShippingInformations( $order_id );
        $data[ 'billing' ]          =   $this->db->where( 'ref_client', $data[ 'order' ][ 'order' ][0][ 'REF_CLIENT' ] )->where( 'type', 'billing' )->get( store_prefix() . 'nexo_clients_address' )->result_array();

        $allowed_order_for_print	=	$this->events->apply_filters( 'allowed_order_for_print', array( 'nexo_order_comptant' ) );

        // Allow only cash order to be printed
        // if ( ! in_array( $data[ 'order' ]['order'][0][ 'TYPE' ], $allowed_order_for_print ) ) {
        //     redirect(array( 'dashboard', 'nexo', 'orders', '?notice=print_disabled' ));
        // }

        if (count($data[ 'order' ]) == 0) {
            return show_error(sprintf(__('Impossible d\'afficher la facture. Cette commande ne possède aucun article &mdash; <a href="%s">Retour en arrière</a>', 'nexo'), $_SERVER['HTTP_REFERER']));
        }

        $this->events->add_action( 'dashboard_footer', function() use ( $data ) {
            get_instance()->load->module_view( 'nexo', 'invoices.default-script', $data );
        });

        $this->Gui->set_title( store_title( __( 'Facture', 'nexo' ) ) );
        return $this->load->module_view( 'nexo', 'invoices.default', $data, true );
    }

    /**
     * Print Result
     * @param int order id
     * @return view
     */
    public function printResult( $order_id )
    {
        $this->load->library('parser');
        $this->load->model('Nexo_Checkout');
        $this->load->module_model( 'nexo', 'Nexo_Orders_Model', 'orderModel' );

        $data                		            =   [];
        
        $data[ 'order' ]    		            =   $this->Nexo_Checkout->get_order_products($order_id, true);
        $data[ 'cache' ]    		            =   $this->cache;
        $data[ 'shipping' ]                     =   $this->db->where( 'ref_order', $order_id )->get( store_prefix() . 'nexo_commandes_shippings' )->result_array();
        $data[ 'tax' ]                          =   $this->Nexo_Misc->get_taxes( $data[ 'order' ][ 'order' ][0][ 'REF_TAX' ] );

        $data[ 'template' ]						=	[];
        $orderDate                              =   new DateTime( $data[ 'order' ][ 'order' ][0][ 'DATE_CREATION' ] );
        $data[ 'template' ][ 'order_date' ]		=	$orderDate->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
        $orderUpdated                           =   new DateTime( $data[ 'order' ][ 'order' ][0][ 'DATE_MOD' ] );
        $data[ 'template' ][ 'order_updated' ]  =	$orderUpdated->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
        
        $data[ 'template' ][ 'order_code' ]		=	$data[ 'order' ][ 'order' ][0][ 'CODE' ];
        $data[ 'template' ][ 'order_id' ]       =   $data[ 'order' ][ 'order' ][0][ 'ORDER_ID' ];
        $data[ 'template' ][ 'order_status' ]	=	$this->Nexo_Checkout->get_order_type( $data[ 'order' ][ 'order' ][0][ 'TYPE' ] );
        $data[ 'template' ][ 'order_note' ]     =   $data[ 'order' ][ 'order' ][0][ 'DESCRIPTION' ];

        $data[ 'template' ][ 'order_cashier' ]	=	User::pseudo( $data[ 'order' ][ 'order' ][0][ 'AUTHOR' ] );
        $data[ 'template' ][ 'shop_name' ]		=	store_option( 'site_name' );
        $data[ 'template' ][ 'shop_pobox' ]		=	store_option( 'nexo_shop_pobox' );
        $data[ 'template' ][ 'shop_fax' ]		=	store_option( 'nexo_shop_fax' );
        $data[ 'template' ][ 'shop_email' ]     =	store_option( 'nexo_shop_email' );
        $data[ 'template' ][ 'shop_street' ]    =	store_option( 'nexo_shop_street' );
        $data[ 'template' ][ 'shop_phone' ]     =	store_option( 'nexo_shop_phone' );
        $data[ 'template' ][ 'customer_name' ]  =   $data[ 'order' ][ 'order' ][0][ 'customer_name' ];
        $data[ 'template' ][ 'customer_phone' ]  =   $data[ 'order' ][ 'order' ][0][ 'customer_phone' ];

        $data[ 'template' ][ 'delivery_address_1' ]     =   @$data[ 'shipping' ][0][ 'address_1' ];
        $data[ 'template' ][ 'delivery_address_2' ]     =   @$data[ 'shipping' ][0][ 'address_2' ];
        $data[ 'template' ][ 'city' ]               =   @$data[ 'shipping' ][0][ 'city' ];
        $data[ 'template' ][ 'country' ]            =   @$data[ 'shipping' ][0][ 'country' ];
        $data[ 'template' ][ 'name' ]               =   @$data[ 'shipping' ][0][ 'name' ];
        $data[ 'template' ][ 'surname' ]            =   @$data[ 'shipping' ][0][ 'surname' ];
        $data[ 'template' ][ 'state' ]              =   @$data[ 'shipping' ][0][ 'surname' ];
        $data[ 'template' ][ 'delivery_cost' ]      =   @$data[ 'shipping' ][0][ 'price' ];

        /**
         * Get order metas
         */
        $freshOrder     =   $data[ 'order' ][ 'order' ][0];
        $freshOrder[ 'metas' ]  =   $this->Nexo_Checkout->getOrderMetas( $order_id );

        /**
         * allow modification of data 
         * used on the receipts
         */
        $filtered   =   $this->events->apply_filters( 'nexo_filter_receipt_template', [
            'template'          =>      $data[ 'template' ],
            'order'             =>      $freshOrder,
            'items'             =>      $data[ 'order' ][ 'products' ],
            'tax'               =>      $data[ 'tax' ],
            'shipping'          =>      $data[ 'shipping' ]
        ]);

        $allowed_order_for_print	    =	$this->events->apply_filters( 'allowed_order_for_print', array( 'nexo_order_comptant' ) );

        return $this->load->view( 
            '../modules/' . $this->events->apply_filters( 'nps_receipt_path', 'nexo/views/receipts/nps/basic' ), 
            $filtered, 
            true 
        );
    }

    /**
     * return a refund receipt of a spific order
     * @param int refund id
     * @return html
     */
    public function refundReceipt( $refund_id )
    {
        $this->load->library( 'parser' );
        $this->load->model( 'Nexo_Misc' );
        $this->load->module_model( 'nexo', 'Nexo_Orders_Model', 'orderModel' );

        $data                                   =   [];
        $data[ 'refund' ]                       =   $this->orderModel->get_refund( $refund_id );

        if ( $data[ 'refund' ] ) {

            $data[ 'template' ]						=	[];
            $orderDate                              =   new DateTime( $data[ 'refund' ][ 'DATE_CREATION' ] );
            $data[ 'template' ][ 'order_date' ]		=	$orderDate->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
            $data[ 'template' ][ 'shop_name' ]		=	store_option( 'site_name' );
            $data[ 'template' ][ 'shop_pobox' ]		=	store_option( 'nexo_shop_pobox' );
            $data[ 'template' ][ 'shop_fax' ]		=	store_option( 'nexo_shop_fax' );
            $data[ 'template' ][ 'shop_email' ]     =	store_option( 'nexo_shop_email' );
            $data[ 'template' ][ 'shop_street' ]    =	store_option( 'nexo_shop_street' );
            $data[ 'template' ][ 'shop_phone' ]     =	store_option( 'nexo_shop_phone' );   
            $data[ 'template' ][ 'refund_author' ]  =   $data[ 'refund' ][ 'author' ][ 'name' ];
            $refundDate                             =   new DateTime( $data[ 'refund' ][ 'DATE_CREATION' ] );
            $data[ 'template' ][ 'refund_date' ]    =   $refundDate->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
            $data[ 'template' ][ 'refund_type' ]    =   $data[ 'refund' ][ 'TYPE' ] === 'withstock' ? __( 'Avec retour de stock', 'nexo' )  : __( 'Sans retour de stock', 'nexo' );
    
            $this->load->module_view( 'nexo', 'receipts.nps.refund', $data );
        }
    }
}