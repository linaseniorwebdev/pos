<?php
// Load Carbon Library Namespace
use Carbon\Carbon;

class Nexo_Checkout extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create random Code
     *
     * @param Int length
     * @return String
    **/

    public function random_code($length = 6)
    {
        $allCode    =    $this->options->get( store_prefix() . 'order_code');
        /**
         * Count product to increase length
        **/
        do {
            // abcdefghijklmnopqrstuvwxyz
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
        } while (in_array($randomString, force_array($allCode)));

        $allCode[]    =    $randomString;
        $this->options->set( store_prefix() . 'order_code', $allCode);

        return $randomString;
    }

    /**
     * Generate Code For Orders
     * @param int length
     * @param int order_id
     * @return string;
     */
    public function shuffle_code( $order_id = null )
    {
        /**
         * Let's check if the order code is selected as generator
         */
        if ( store_option( 'nexo_code_type', 'order_code' ) == 'date_code' ) {
            $date           =   Carbon::parse( date_now() ) ;
            $order          =   $this->db
                ->where( 'DATE_CREATION >=', $date->startOfDay()->toDateTimeString() )
                ->where( 'DATE_CREATION <=', $date->endOfDay()->toDateTimeString() )
                ->get( store_prefix() . 'nexo_commandes' );
            $total_orders   =   $order->num_rows();
            $code           =    substr( $date->year, 2 ) . sprintf("%02d", $date->month ) . sprintf("%02d", $date->day ) . '-' . sprintf( "%03d", $total_orders );
            return $code;
        } else {
            $length         =   6;
            $Options        =    $this->options->get();
            $orders_code    =   force_array(@$Options[ 'order_code' ]);
            $orders_code    =   array_filter( $orders_code, function( $value ) {
                return strlen( $value ) > 0;
            });
            /**
             * Count product to increase length
            **/
            do {
                // abcdefghijklmnopqrstuvwxyz
                $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < $length; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }
            } while (in_array($randomString, force_array($orders_code)));

            $orders_code[]    =    $randomString;
            $this->options->set('order_code', $orders_code);
    
            return $randomString;
        }
    }

    /**
     * Delete order code
     * @param order code
     * @return void
     */
    public function deleteOrderCode( $orderCode )
    {
        $codes      =   $this->options->get( 'order_code' );
        $newCodes   =   array_filter( $codes, function( $code ) use ( $orderCode ) {
            return $code != $orderCode;
        });
        $this->options->set( 'order_code', $newCodes );
    }

    /**
     * Command delete
     *
     * @param Array
     * @return Array
    **/

    public function commandes_delete($post)
    {
        if (class_exists('User')) {
            // Protecting
            if (! User::can('nexo.delete.orders')) {
                return nexo_access_denied();
            }
        }

        // Remove product from this cart
        $query    =    $this->db
        ->where('ID', $post)
        ->get( store_prefix() . 'nexo_commandes');

        $command    =    $query->result_array();

        if( ! $command ) {
            return false;
        }

        // Récupère les produits vendu
        $query    =    $this->db
        ->where('REF_COMMAND_CODE', $command[0][ 'CODE' ])
        ->get( store_prefix() . 'nexo_commandes_produits');

        $produits        =    $query->result_array();

        $products_data    =    array();
        // parcours les produits disponibles pour les regrouper
        foreach ($produits as $product) {
            $products_data[ $product[ 'REF_PRODUCT_CODEBAR' ] ] =    [
                'QUANTITY'              =>  floatval($product[ 'QUANTITE' ]),
                'COMMAND_PRODUCT_ID'    =>  $product[ 'ID' ],
                'INLINE'                =>  $product[ 'INLINE' ]
            ];
        }

        // retirer le décompte des commandes passées par le client
        $query        =    $this->db->where('ID', $command[0][ 'REF_CLIENT' ])->get( store_prefix() . 'nexo_clients');
        $client        =    $query->result_array();

        $this->db->where('ID', $command[0][ 'REF_CLIENT' ])->update('nexo_clients', array(
            'NBR_COMMANDES'        =>    (floatval($client[0][ 'NBR_COMMANDES' ]) - 1) < 0 ? 0 : floatval($client[0][ 'NBR_COMMANDES' ]) - 1,
            'OVERALL_COMMANDES'    =>    (floatval($client[0][ 'OVERALL_COMMANDES' ]) - 1) < 0 ? 0 : floatval($client[0][ 'OVERALL_COMMANDES' ]) - 1,
        ));

        // Parcours des produits pour restaurer les quantités vendues
        foreach ( $products_data as $codebar => $data ) {
            // Quantité actuelle
            $query      =    $this->db->where('CODEBAR', $codebar)->get( store_prefix() . 'nexo_articles');
            $article    =    $query->result_array();

            // Restoring quantities don't work for inline item
            if ( $article ) {
                if( $data[ 'INLINE' ] != '1' ) {
                    // Cumul et restauration des quantités
                    $this->db->where('CODEBAR', $codebar)->update( store_prefix() . 'nexo_articles', array(
                        'QUANTITE_VENDU'        =>        floatval($article[0][ 'QUANTITE_VENDU' ]) - $data[ 'QUANTITY' ],
                        'QUANTITE_RESTANTE'     =>        floatval($article[0][ 'QUANTITE_RESTANTE' ]) + $data[ 'QUANTITY' ],
                    ));
                }
            }

            // Suppresison des meta des produits
            $this->db->where( 'ID', $data[ 'COMMAND_PRODUCT_ID' ] )->delete( store_prefix() . 'nexo_commandes_produits_meta' );
        }

        // retire les produits vendu du panier de cette commande et les renvoies au stock
        $this->db->where('REF_COMMAND_CODE', $command[0][ 'CODE' ])->delete( store_prefix() . 'nexo_commandes_produits');

		// @since 2.9 supprime les paiements
		$this->db->where('REF_COMMAND_CODE', $command[0][ 'CODE' ])->delete( store_prefix() . 'nexo_commandes_paiements');

		// Delete order meta
		$this->db->where( 'REF_ORDER_ID', $command[0][ 'ID' ] )->delete( store_prefix() . 'nexo_commandes_meta' );

        // delete order ocde
        $this->deleteOrderCode( $command[0][ 'CODE' ] );

        // New Action
        $this->events->do_action('nexo_delete_order', $post);

        return $post;
    }

    /**
     * Create Permission
     * @deprecated
     * @return Void
    **/

    public function create_permissions()
    {
        // ..
    }

    /**
     * Delete Permission
     *
     * @return Void
    **/

    public function delete_permissions()
    {
        $this->aauth        =    $this->users->auth;

        /**
         * Denied Permissions
        **/

        // Shop Manager
        // Orders
        $this->aauth->deny_group('shop_manager', 'create_shop_orders');
        $this->aauth->deny_group('shop_manager', 'edit_shop_orders');
        $this->aauth->deny_group('shop_manager', 'delete_shop_orders');

        // Customers
        $this->aauth->deny_group('shop_manager', 'create_shop_customers');
        $this->aauth->deny_group('shop_manager', 'delete_shop_customers');
        $this->aauth->deny_group('shop_manager', 'edit_shop_customers');

        // Customers Groups
        $this->aauth->deny_group('shop_manager', 'create_shop_customers_groups');
        $this->aauth->deny_group('shop_manager', 'delete_shop_customers_groups');
        $this->aauth->deny_group('shop_manager', 'edit_shop_customers_groups');

        // Shop items
        $this->aauth->deny_group('shop_manager', 'create_shop_items');
        $this->aauth->deny_group('shop_manager', 'edit_shop_items');
        $this->aauth->deny_group('shop_manager', 'delete_shop_items');


        // Shop categories
        $this->aauth->deny_group('shop_manager', 'create_shop_categories');
        $this->aauth->deny_group('shop_manager', 'edit_shop_categories');
        $this->aauth->deny_group('shop_manager', 'delete_shop_categories');

        // Shop Radius
        $this->aauth->deny_group('shop_manager', 'create_shop_radius');
        $this->aauth->deny_group('shop_manager', 'edit_shop_radius');
        $this->aauth->deny_group('shop_manager', 'delete_shop_radius');

        // Shop Shipping
        $this->aauth->deny_group('shop_manager', 'create_shop_shipping');
        $this->aauth->deny_group('shop_manager', 'edit_shop_shipping');
        $this->aauth->deny_group('shop_manager', 'delete_shop_shipping');

        // Shop Provider
        $this->aauth->deny_group('shop_manager', 'create_shop_providers');
        $this->aauth->deny_group('shop_manager', 'edit_shop_providers');
        $this->aauth->deny_group('shop_manager', 'delete_shop_providers');

        // Shop purchase invoice
        $this->aauth->deny_group('shop_manager', 'create_shop_purchases_invoices');
        $this->aauth->deny_group('shop_manager', 'edit_shop_purchases_invoices');
        $this->aauth->deny_group('shop_manager', 'delete_shop_purchases_invoices');

        // Shop Backup
        $this->aauth->deny_group('shop_manager', 'create_shop_backup');
        $this->aauth->deny_group('shop_manager', 'edit_shop_backup');
        $this->aauth->deny_group('shop_manager', 'delete_shop_backup');

        // Shop Track User Activity
        $this->aauth->deny_group('shop_manager', 'read_shop_user_tracker');
        $this->aauth->deny_group('shop_manager', 'delete_shop_user_tracker');

        // Update Profile
        $this->aauth->deny_group('shop_manager', 'edit_profile');

        // Read Reports
        $this->aauth->deny_group('shop_manager', 'read_shop_reports');

		// Shop Backup
		// @since 2.7.5
        $this->aauth->deny_group('shop_manager', 'create_shop_registers');
        $this->aauth->deny_group('shop_manager', 'edit_shop_registers');
        $this->aauth->deny_group('shop_manager', 'delete_shop_registers');

		// @since 2.8.0
        $this->aauth->deny_group('shop_manager', 'create_shop');
        $this->aauth->deny_group('shop_manager', 'edit_shop');
        $this->aauth->deny_group('shop_manager', 'delete_shop');
		$this->aauth->deny_group('shop_manager', 'enter_shop');

        //@since 3.0.20
        $this->aauth->deny_group('shop_manager', 'create_item_stock');
        $this->aauth->deny_group('shop_manager', 'edit_item_stock');
        $this->aauth->deny_group('shop_manager', 'delete_item_stock');

        // Master
        // Orders
        $this->aauth->deny_group('master', 'create_shop_orders');
        $this->aauth->deny_group('master', 'edit_shop_orders');
        $this->aauth->deny_group('master', 'delete_shop_orders');

        // Customers
        $this->aauth->deny_group('master', 'create_shop_customers');
        $this->aauth->deny_group('master', 'delete_shop_customers');
        $this->aauth->deny_group('master', 'edit_shop_customers');

        // Customers Groups
        $this->aauth->deny_group('master', 'create_shop_customers_groups');
        $this->aauth->deny_group('master', 'delete_shop_customers_groups');
        $this->aauth->deny_group('master', 'edit_shop_customers_groups');

        // Shop items
        $this->aauth->deny_group('master', 'create_shop_items');
        $this->aauth->deny_group('master', 'edit_shop_items');
        $this->aauth->deny_group('master', 'delete_shop_items');

        // Shop categories
        $this->aauth->deny_group('master', 'create_shop_categories');
        $this->aauth->deny_group('master', 'edit_shop_categories');
        $this->aauth->deny_group('master', 'delete_shop_categories');

        // Shop Radius
        $this->aauth->deny_group('master', 'create_shop_radius');
        $this->aauth->deny_group('master', 'edit_shop_radius');
        $this->aauth->deny_group('master', 'delete_shop_radius');

        // Shop Shipping
        $this->aauth->deny_group('master', 'create_shop_shipping');
        $this->aauth->deny_group('master', 'edit_shop_shipping');
        $this->aauth->deny_group('master', 'delete_shop_shipping');

        // Shop Provider
        $this->aauth->deny_group('master', 'create_shop_providers');
        $this->aauth->deny_group('master', 'edit_shop_providers');
        $this->aauth->deny_group('master', 'delete_shop_providers');

        // Shop purchase invoice
        $this->aauth->deny_group('master', 'create_shop_purchases_invoices');
        $this->aauth->deny_group('master', 'edit_shop_purchases_invoices');
        $this->aauth->deny_group('master', 'delete_shop_purchases_invoices');

        // Shop Backup
        $this->aauth->deny_group('master', 'create_shop_backup');
        $this->aauth->deny_group('master', 'edit_shop_backup');
        $this->aauth->deny_group('master', 'delete_shop_backup');

        // Shop Track User Activity
        $this->aauth->deny_group('master', 'read_shop_user_tracker');
        $this->aauth->deny_group('master', 'delete_shop_user_tracker');

        // Read Reports
        $this->aauth->deny_group('master', 'read_shop_reports');

		// Shop Permissions
		// @since 2.8.0
        $this->aauth->deny_group('shop_manager', 'create_shop');
        $this->aauth->deny_group('shop_manager', 'edit_shop');
        $this->aauth->deny_group('shop_manager', 'delete_shop');
		$this->aauth->deny_group('shop_manager', 'enter_shop');

		// Shop Backup
		// @since 2.7.5
        $this->aauth->deny_group('master', 'create_shop_registers');
        $this->aauth->deny_group('master', 'edit_shop_registers');
        $this->aauth->deny_group('master', 'delete_shop_registers');

        //@since 3.0.20
        $this->aauth->deny_group('master', 'create_item_stock');
        $this->aauth->deny_group('master', 'edit_item_stock');
        $this->aauth->deny_group('master', 'delete_item_stock');

        // Denied Permissions for Shop Test
        // Orders
        $this->aauth->deny_group('shop_tester', 'create_shop_orders');
        $this->aauth->deny_group('shop_tester', 'edit_shop_orders');

        // Customers
        $this->aauth->deny_group('shop_tester', 'create_shop_customers');
        $this->aauth->deny_group('shop_tester', 'edit_shop_customers');

        // Customers Groups
        $this->aauth->deny_group('shop_tester', 'create_shop_customers_groups');
        $this->aauth->deny_group('shop_tester', 'edit_shop_customers_groups');

        // Shop items
        $this->aauth->deny_group('shop_tester', 'create_shop_items');
        $this->aauth->deny_group('shop_tester', 'edit_shop_items');

        // Shop categories
        $this->aauth->deny_group('shop_tester', 'create_shop_categories');
        $this->aauth->deny_group('shop_tester', 'edit_shop_categories');

        // Shop Radius
        $this->aauth->deny_group('shop_tester', 'create_shop_radius');
        $this->aauth->deny_group('shop_tester', 'edit_shop_radius');

        // Shop Shipping
        $this->aauth->deny_group('shop_tester', 'create_shop_shipping');
        $this->aauth->deny_group('shop_tester', 'edit_shop_shipping');

        // Shop Provider
        $this->aauth->deny_group('shop_tester', 'create_shop_providers');
        $this->aauth->deny_group('shop_tester', 'edit_shop_providers');

        // Shop purchase invoice
        $this->aauth->deny_group('shop_tester', 'create_shop_purchases_invoices');
        $this->aauth->deny_group('shop_tester', 'edit_shop_purchases_invoices');

        // Shop Backup
        $this->aauth->deny_group('shop_tester', 'create_shop_backup');
        $this->aauth->deny_group('shop_tester', 'edit_shop_backup');

        // Shop Track User Activity
        $this->aauth->deny_group('shop_tester', 'read_shop_user_tracker');

        // Read Reports
        $this->aauth->deny_group('shop_tester', 'read_shop_reports');

		// Shop Backup
		// @since 2.7.5
        $this->aauth->deny_group('shop_tester', 'create_shop_registers');
        $this->aauth->deny_group('shop_tester', 'edit_shop_registers');

        // Update Profile
        // $this->aauth->deny_group('shop_tester', 'edit_profile');
		// @since 2.8.0
        $this->aauth->deny_group('shop_tester', 'create_shop');
        $this->aauth->deny_group('shop_tester', 'edit_shop');
		$this->aauth->deny_group('shop_tester', 'enter_shop');
        
        //@since 3.0.20
        $this->aauth->deny_group('shop_tester', 'create_item_stock');
        $this->aauth->deny_group('shop_tester', 'edit_item_stock');
        $this->aauth->deny_group('shop_tester', 'delete_item_stock');

        // For Cashier
        // Orders
        $this->aauth->deny_group('shop_cashier', 'create_shop_orders');
        $this->aauth->deny_group('shop_cashier', 'edit_shop_orders');
        $this->aauth->deny_group('shop_cashier', 'delete_shop_orders');

        // Customers
        $this->aauth->deny_group('shop_cashier', 'create_shop_customers');
        $this->aauth->deny_group('shop_cashier', 'delete_shop_customers');
        $this->aauth->deny_group('shop_cashier', 'edit_shop_customers');

        // Customers Groups
        $this->aauth->deny_group('shop_cashier', 'create_shop_customers_groups');
        $this->aauth->deny_group('shop_cashier', 'delete_shop_customers_groups');
        $this->aauth->deny_group('shop_cashier', 'edit_shop_customers_groups');

        // Update Profile
        $this->aauth->deny_group('shop_cashier', 'edit_profile');

        // Delete Custom Groups
        $this->aauth->delete_group('shop_cashier');
        $this->aauth->delete_group('shop_manager');
        $this->aauth->delete_group('shop_tester');

		// Store
		$this->aauth->deny_group('shop_tester', 'enter_shop');
    }

    /**
     * Get Order
     *
     * @return array
    **/

    public function get_order($order_id = null)
    {
        if ($order_id != null && ! is_array($order_id)) {
            $this->db->where('ID', $order_id);
        } elseif (is_array($order_id)) {
            foreach ($order_id as $mark => $value) {
                $this->db->where($mark, $value);
            }
        }

        $query    =    $this->db->get( store_prefix() . 'nexo_commandes');

        if ($query->result_array()) {
            return $query->result_array();
        }
        return false;
    }

    /**
     * Get Order with metas
     * @param int order id
     * @return array
    **/

    public function get_order_with_metas( $order_id = null )
    {
        $orders      =   $this->get_order( $order_id );

        foreach( $orders as &$order ) {
            $metas       =   $this->db->where( 'REF_ORDER_ID', $order_id )->get( store_prefix() . 'nexo_commandes_meta' )
            ->result_array();
            
            if( $metas ) {
                foreach( $metas as $meta ) {
                    if( empty( @$order[ 'METAS' ] ) ) {
                        $order[ 'METAS' ]   =   [];
                    }
                    
                    $order[ 'METAS' ][ $meta[ 'KEY' ] ]     =   $meta[ 'VALUE' ];
                }
            }
        }

        return $orders;
    }

    /**
     * Get order products
     *
     * @param Int order id
     * @param Bool return all
     * @deprecated
    **/

    public function get_order_products( $order_id, $return_all = false)
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

                if( $metas ) {
                    $sub_data[ $key ][ 'metas' ]    =   [];
                }

                foreach( $metas as $meta ) {
                    $sub_data[ $key ][ 'metas' ][ $meta->KEY ]      =   $meta->VALUE;
                }
            }

            if ($sub_data) {
                if ($return_all) {
                    return array(
                        'order'        =>    $data,
                        'products'    =>    $sub_data
                    );
                }
                return $sub_query->result_array();
            }

            return [
                'order'     =>  $data,
                'products'  =>  $sub_data
            ];
        }
        return false;
    }

    /**
     * Get order type
     *
     * @param Int
     * @return String order type
    **/

    public function get_order_type($order_type)
    {
        $order_types    =    $this->config->item('nexo_order_types');
        return $order_types[ $order_type ];
    }

    /**
     * Proceed order
     * complete an order
     * @param int order id
     * @return bool
    **/

    public function proceed_order($order_id)
    {
        $order    =    $this->Nexo_Checkout->get_order($order_id);

        if ($order) {
            if ($order[0][ 'TYPE' ] == 'nexo_order_advance') {
                $this->db->where('ID', $order_id)->update( store_prefix() . 'nexo_commandes', array(
                    'SOMME_PERCU'    =>    $order[0][ 'TOTAL' ],
                    'TYPE'            =>    'nexo_order_comptant'
                ));
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

	/**
	 * Check Registers
	 * @since 2.7.5
	 * @param int register int
	 * @return string (open, closed, disabled, not_found)
	**/

	public function register_status( $id )
	{
		$result	=	$this->db->where( 'ID', $id )->get( store_prefix() . 'nexo_registers' )->result_array();

		if( @$result[0] != null ) {
			return $result[0][ 'STATUS' ];
		}
		return 'not_found';
    }
    
    /**
     * Get All registers
     * @return array of registers
     */
    public function get_registers()
    {
        return $this->db->get( store_prefix() . 'nexo_registers' )->result_array();
    }

    /**
     * Set reigster idle time
     * @return void
     */
    public function set_idle( $status, $id )
    {
        $this->db->insert( store_prefix() . 'nexo_registers_activities', [
            'AUTHOR'    =>  User::id(),
            'TYPE'      =>  $status,
            'BALANCE'   =>  0,
            'DATE_CREATION'     =>  date_now(),
            'REF_REGISTER'  =>  $id
        ]);
    }

	/**
	 * Get Register
	 * @param int register id
	 * @return array
	**/

	public function get_register( $id )
	{
		return $this->db->where( 'ID', $id )->get( store_prefix() . 'nexo_registers' )->result_array();
	}

	/**
	 * Connect User to a Register
	 * @return void
	**/

	public function connect_user( $register_id, $user_id )
	{
		$this->db->where( 'ID', $register_id )->update( store_prefix() . 'nexo_registers', array(
			'USED_BY'	=>	$user_id
		) );
	}

	/**
	 * Has user logged in
	 * @return bool
	**/

	public function has_user( $register_id )
	{
		$result		=	$this->db->where( 'ID', $register_id )->get( store_prefix() . 'nexo_registers' )->result_array();

		if( $result ) {
			return $result[0][ 'USED_BY' ] == '0' ? false : true;
		}
		return false;
	}

	/**
	 * Disconnect User
	 *
	 * @param int register id
	 * @return void
	**/

	public function disconnect_user( $register_id )
	{
		$result		=	$this->db->where( 'ID', $register_id )->update( store_prefix() . 'nexo_registers', array(
			'USED_BY'		=>		0
		) );
	}

    /**
     *  Get Order with item stock
     *  @param int order id
     *  @return array
    **/

    public function get_order_with_item_stock( $order_id )
    {
        $data           =   $this->db->where( 'ID', $order_id )->get( store_prefix() . 'nexo_commandes' )->result_array();

        $order_code     =   $data[0][ 'CODE' ];

        $this->db->select(
            store_prefix() . 'nexo_articles.DESIGN, ' .
            store_prefix() . 'nexo_commandes_produits.PRIX, ' .
            store_prefix() . 'nexo_commandes.CODE, ' .
            store_prefix() . 'nexo_articles_stock_flow.QUANTITE as QUANTITE, ' .
            store_prefix() . 'nexo_articles_stock_flow.TYPE as TYPE'
        );

        $this->db->from( store_prefix() . 'nexo_articles_stock_flow' );

        $this->db->join(
            store_prefix() . 'nexo_commandes_produits',
            store_prefix() . 'nexo_commandes_produits.REF_COMMAND_CODE = ' .
            store_prefix() . 'nexo_articles_stock_flow.REF_COMMAND_CODE', 'left'
        );

        $this->db->join(
            store_prefix() . 'nexo_articles',
            store_prefix() . 'nexo_articles.CODEBAR = ' .
            store_prefix() . 'nexo_commandes_produits.REF_PRODUCT_CODEBAR', 'left'
        );

        $this->db->join(
            store_prefix() . 'nexo_commandes',
            store_prefix() . 'nexo_commandes.CODE = ' .
            store_prefix() . 'nexo_articles_stock_flow.REF_COMMAND_CODE', 'left'
        );

        $this->db->where( store_prefix() . 'nexo_articles_stock_flow.REF_COMMAND_CODE', $order_code );

        return $this->db->get()->result_array();
    }

    /**
     * Post Order
     * @param array of order items
     * @return array
     */
    public function postOrder( $data, $author_id )
    {
        $this->load->module_model( 'nexo', 'NexoRewardSystemModel', 'reward_model' );
        
        $shipping                   =       ( array ) $data[ 'shipping' ];
        $order_details              =       array(
            'RISTOURNE'             =>      $data[ 'RISTOURNE' ],
            'REMISE'                =>      $data[ 'REMISE' ],
            // @since 2.9.6
            'REMISE_PERCENT'        =>      $data[ 'REMISE_PERCENT' ],
            'REMISE_TYPE'           =>      $data[ 'REMISE_TYPE' ],
            'CODE'                  =>      date( 's' ) . date( 'i' ) . rand( 0, 100 ),
            'RABAIS'                =>      $data[ 'RABAIS' ],
            'GROUP_DISCOUNT'        =>      $data[ 'GROUP_DISCOUNT' ],
            'TOTAL'                 =>      $data[ 'TOTAL'],
            'AUTHOR'                =>      $author_id,
            'PAYMENT_TYPE'          =>      $data[ 'PAYMENT_TYPE' ],
            'REF_CLIENT'            =>      $data[ 'REF_CLIENT' ],
            'TVA'                   =>      $data[ 'TVA' ],
            'DATE_CREATION'         =>      date_now(),
            'DATE_MOD'              =>      date_now(), // @since 3.8.0
			'DESCRIPTION'			=>      $data[ 'DESCRIPTION' ],
			'REF_REGISTER'		    =>      $data[ 'REGISTER_ID' ],
			// @since 2.7.10
			'TITRE'                 =>      $data[ 'TITRE' ] != null ? $data[ 'TITRE' ] : '',
            // @since 3.1
            'SHIPPING_AMOUNT'       =>      floatval( @$shipping[ 'price' ] ),
            'REF_SHIPPING_ADDRESS'  =>      @$shipping[ 'id' ],
            'REF_TAX'               =>      $data[ 'REF_TAX' ],
            'STATUS'                =>      isset( $data[ 'STATUS' ] ) ? $data[ 'STATUS' ] : 'processing'
        );

        // Increase customers purchases
        $query                        	=    $this->db->where( 'ID', $data[ 'REF_CLIENT' ] )->get( store_prefix() . 'nexo_clients');
        $customer_result                =    $query->result_array();

        // only if customer has been found
        if( empty( $customer_result ) ) {
            return array(
                'message'   =>  __( 'Impossible d\'identifier le client. Veuillez choisir un client ou définir un client par défaut.', 'nexo' ),
                'status'    =>  'failed'
            );
        }

        // filter order details
        $order_details          =   $this->events->apply_filters( 'post_order_details', $order_details, $data );

        /**
         * Saving order, in order to get the code in advance.
         */
        $this->db->insert( store_prefix() . 'nexo_commandes', $order_details);

        $order_id   =   $this->db->insert_id();

        /**
         * Update Order Code
         */
        $order_details[ 'CODE' ]    =   $this->shuffle_code( $order_id );        
        $this->db->where( 'ID', $order_id )->update( store_prefix() . 'nexo_commandes', [
            'CODE'  =>  $order_details[ 'CODE' ]
        ]);

        /**
         * Item structure
         * array( ID, QUANTITY_ADDED, BARCODE, PRICE, QTE_SOLD, LEFT_QTE, STOCK_ENABLED );
        **/

        foreach ( $data[ 'ITEMS' ] as $item ) {
            // let the user filter items
            $item   =   $this->events->apply_filters( 'post_order_filter_item', $item );

            $fresh_item       =   $this->db->where( 'CODEBAR', $item[ 'codebar' ] )
            ->get( store_prefix() . 'nexo_articles' )
            ->result_array();

            /**
             * deplete included items
             * only works for grouped items
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
                            'REF_COMMAND_CODE'          =>  $order_details[ 'CODE' ],
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
             * stock enable is not checked for inline items
            **/
            
			if( intval( $item[ 'stock_enabled' ] ) === 1 && ! in_array( $item[ 'inline' ], [ 'true', '1' ]) && $fresh_item[0][ 'TYPE' ] != '3' ) {

                $item_stock_data                       =   [];
                $item_stock_data[ 'QUANTITE_VENDU' ]   =   intval( $fresh_item[0][ 'QUANTITE_VENDU' ]) + intval($item[ 'qte_added' ]);

                // if item type belongs to type which allow to decrease remaning quantity
                if( in_array( intval( $fresh_item[0][ 'TYPE' ] ), $this->events->apply_filters( 'treat_as_sold_quantity', [ 1 ] ) ) ) {
                    $item_stock_data[ 'QUANTITE_RESTANTE' ]   =   intval($fresh_item[0][ 'QUANTITE_RESTANTE' ]) - intval($item[ 'qte_added' ]);
                }

                // update item stock
                $this->db->where('CODEBAR', $item[ 'codebar' ])
                ->update( store_prefix() . 'nexo_articles', $item_stock_data );
			}

            $discount_amount_unique     =   0;
            $discount_amount_total      =   0;
			// Adding to order product
			if( $item[ 'discount_type' ] == 'percentage' && $item[ 'discount_percent' ] != '0' ) {
                $discount_amount_unique            =   __floatval( __floatval($item[ 'sale_price']) * floatval( $item[ 'discount_percent' ] ) / 100 );
				$discount_amount_total		=	__floatval( $discount_amount_unique * __floatval($item[ 'qte_added']) );
			} elseif( $item[ 'discount_type' ] == 'flat' ) {
                $discount_amount_unique            =   ( __floatval( $item[ 'discount_amount' ] ) );
				$discount_amount_total		=	( $discount_amount_unique * floatval( $item[ 'qte_added' ] ) );
			} 

			$item_data		=	array(
				'REF_PRODUCT_CODEBAR'       =>    $item[ 'codebar' ],
				'REF_COMMAND_CODE'          =>    $order_details[ 'CODE' ],
                'QUANTITE'                  =>    $item[ 'qte_added' ],
                'PRIX_BRUT'                 =>    $item[ 'sale_price' ],
				'PRIX'                      =>    floatval( $item[ 'sale_price' ] ) - $discount_amount_unique,
				'PRIX_TOTAL'                =>    ( __floatval($item[ 'qte_added' ]) * __floatval($item[ 'sale_price' ]) ) - $discount_amount_total,
				'PRIX_BRUT_TOTAL'           =>    ( __floatval($item[ 'qte_added' ]) * __floatval($item[ 'sale_price' ]) ),
				// @since 2.9.0
				'DISCOUNT_TYPE'			    =>	$item[ 'discount_type'],
				'DISCOUNT_AMOUNT'		    =>	$item[ 'discount_amount'],
				'DISCOUNT_PERCENT'		    =>	$item[ 'discount_percent'],
                // @since 3.1
                'NAME'                      =>  $item[ 'name' ],
                'INLINE'                    =>  $item[ 'inline' ],
                'ALTERNATIVE_NAME'          =>  @$item[ 'alternative_name' ] ? $item[ 'alternative_name' ] : ''// @sicne 3.11.8
            );
            
            $item_data                      =     $this->events->apply_filters_ref_array( 'post_order_item', [$item_data, $item] );

			$this->db->insert( store_prefix() . 'nexo_commandes_produits', $item_data );

            // getcommande product id
            $insert_id = $this->db->insert_id();

            // Saving item metas
            $meta_array         =   array();
            foreach( ( array ) @$item[ 'metas' ] as $key => $value ) {
                $meta_array[]     =   [
                    'REF_COMMAND_PRODUCT'   =>  $insert_id,
                    'REF_COMMAND_CODE'      =>  $order_details[ 'CODE' ],
                    'KEY'                   =>  $key,
                    'VALUE'                 =>  is_array( $value ) ? json_encode( $value ) : $value,
                    'DATE_CREATION'         =>  date_now()
                ];
            }

            // If item has metas, we just save it
            if( $meta_array ) {
                $this->db->insert_batch( store_prefix() . 'nexo_commandes_produits_meta', $meta_array );
            }

            // Add history for this item on stock flow
            $stock_flow     =   [
                'REF_ARTICLE_BARCODE'       =>  $item[ 'codebar' ],
                'QUANTITE'                  =>  $item[ 'qte_added' ],
                'UNIT_PRICE'                =>  $item[ 'sale_price' ],
                'TOTAL_PRICE'               =>  ( __floatval($item[ 'qte_added' ]) * __floatval($item[ 'sale_price' ]) ) - $discount_amount_total,
                'REF_COMMAND_CODE'          =>  $order_details[ 'CODE' ],
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

        $current_order    =    $this->db->where( 'CODE', $order_details[ 'CODE' ])
            ->get( store_prefix() . 'nexo_commandes')
            ->result_array();

		/**
         * Saving order metas
         * @since 2.8.2
         */
        $metas					=	@$data[ 'metas' ];

		if( $metas ) {

			foreach( $metas as $key => $value ) {
				$meta_data		=	array(
					'REF_ORDER_ID'	=>	$order_id,
					'KEY'			=>	$key,
					'VALUE'			=>	is_array( $value ) ? json_encode( $value ) : $value,
					'AUTHOR'		=>	$author_id,
					'DATE_CREATION'	=>	date_now()
				);

				$this->db->insert( store_prefix() . 'nexo_commandes_meta', $meta_data );
			}

		}

		// @since 2.9
		// Save order payment
		$this->load->config( 'rest' );

		if( is_array( @$data[ 'payments' ] ) ) {
            foreach( $data[ 'payments' ] as $payment ) {

				$request    =   Requests::post( site_url( array( 'rest', 'nexo', 'order_payment', $order_id, store_get_param( '?' ) ) ), [
                    $this->config->item('rest_key_name')    =>  $_SERVER[ 'HTTP_' . $this->config->item('rest_header_key') ]
                ], array(
					'author'		=>	$author_id,
					'date'			=>	date_now(),
					'payment_type'	=>	$payment[ 'namespace' ],
					'amount'		=>	round( floatval( $payment[ 'amount' ] ), 2 ),
                    'order_code'	=>	$current_order[0][ 'CODE' ],
                    'ref_id'        =>  intval( @$payment[ 'meta' ][ 'coupon_id' ] ),
                    'payment_id'    =>  @$payment[ 'meta' ][ 'payment_id' ]
                ) );

                /**
                 * parse the response
                 * from the payment request
                 */
                $response       =   json_decode( $request->body, true );

                tendoo_debug( $request->body, 'order-payments-' . $current_order[0][ 'CODE' ] . '.json');
                tendoo_debug([
                    $this->config->item('rest_key_name')    =>  $_SERVER[ 'HTTP_' . $this->config->item('rest_header_key') ]
                ], 'order-logs-' . $current_order[0][ 'CODE' ] . '.json' );

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
        if( @$data[ 'shipping' ] ) {
            // fetch ref shipping for the selected customers
            $shipping       =   $data[ 'shipping' ];
            // edit shipping id to ref_shipping
            $shipping[ 'ref_shipping' ]     =   $shipping[ 'id' ];
            $shipping[ 'ref_order' ]        =   $current_order[0][ 'ID' ];
            unset( $shipping[ 'id' ] );

            $this->db->insert( store_prefix() . 'nexo_commandes_shippings', $shipping );
        }

        /**
         * memory leak
         */
        $current_order    =    $this->db->where( 'CODE', $order_details[ 'CODE' ])
            ->get( store_prefix() . 'nexo_commandes')
            ->result_array();

        /**
         * let's check if the orders is complete,
         * in order to make him provide from additionnal
         * offers
         */
        if ( $current_order[0][ 'TYPE' ] === 'nexo_order_comptant' ) {
            $total_commands                	=   intval( $customer_result[0][ 'NBR_COMMANDES' ]) + 1;
            $overal_commands            	=   intval( $customer_result[0][ 'OVERALL_COMMANDES' ]) + 1;
            $total_spend                    =   floatval( $customer_result[0][ 'TOTAL_SPEND' ] ) + floatval( $data[ 'TOTAL' ] );

            $this->db->set('NBR_COMMANDES', $total_commands);
            $this->db->set('OVERALL_COMMANDES', $overal_commands);
            $this->db->set( 'TOTAL_SPEND', $total_spend );

            // Disable automatic discount
            if ( $data[ 'REF_CLIENT' ] != store_option( 'default_compte_client' ) ) {

                // Verifie si le client doit profiter de la réduction
                if ($data[ 'DISCOUNT_TYPE' ] != 'disable') {
                    // On définie si en fonction des réglages, l'on peut accorder une réduction au client
                    if ($total_commands >= __floatval( $data[ 'HMB_DISCOUNT' ] ) - 1 && $customer_result[0][ 'DISCOUNT_ACTIVE' ] == 0) {
                        $this->db->set('DISCOUNT_ACTIVE', 1);
                    } elseif ($total_commands >= $data[ 'HMB_DISCOUNT' ] && $customer_result[0][ 'DISCOUNT_ACTIVE' ] == 1) {
                        $this->db->set('DISCOUNT_ACTIVE', 0); // bénéficiant d'une reduction sur cette commande, la réduction est désactivée
                        $this->db->set('NBR_COMMANDES', 1); // le nombre de commande est également désactivé
                    }
                }
            }
            // fin désactivation réduction auto pour le client par défaut
            $this->db->where( 'ID', $data[ 'REF_CLIENT' ] )
                ->update( store_prefix() . 'nexo_clients' );
        }
        
        /**
         * let's manage the entire reward system over here
         */
        $this->reward_model->handleRewardIfEnabled([
            'total'         =>  $data[ 'TOTAL' ],
            'customer_id'   =>  $data[ 'REF_CLIENT' ],
            'order'         =>  $current_order[0]
        ]);

        $response   =   $this->afterOrderPlaced( compact( 'order_details', 'current_order', 'data' ) );

        extract( $response );
        
        return $this->events->apply_filters( 'after_submit_order', compact( 'order_details', 'current_order', 'data' ) );
    }

    public function afterOrderPlaced( $post_data )
    {
        extract( $post_data );
        /**
         * expose
         * -> order_details
         * -> current_order
         * -> data
         */

        $updated_details                =       [];
        $fresh_order                    =       $current_order[0];

        if ( floatval( $fresh_order[ 'SOMME_PERCU' ] ) >= floatval( $fresh_order[ 'TOTAL' ] ) ) {
            $updated_details[ 'TYPE' ]    =    'nexo_order_comptant'; // Comptant
        } elseif ( floatval( $fresh_order[ 'SOMME_PERCU' ] ) == 0) {
            $updated_details[ 'TYPE' ]    =   'nexo_order_devis'; // Devis
        } elseif ( floatval( $fresh_order[ 'SOMME_PERCU' ] ) < floatval($fresh_order[ 'TOTAL' ]) && floatval( $fresh_order[ 'SOMME_PERCU' ] ) > 0) {
            $updated_details[ 'TYPE' ]    =    'nexo_order_advance'; // Avance
        }

        /**
         * order aging
         * set expiration date for current order
         * @since 3.9.0
        **/

        if( 
            (
                in_array( $updated_details[ 'TYPE' ], [ 'nexo_order_devis' ] ) && 
                store_option( 'expiring_order_type' ) == 'quote'
            ) ||
            (
                in_array( $updated_details[ 'TYPE' ], [ 'nexo_order_devis', 'nexo_order_advance'] ) && 
                store_option( 'expiring_order_type' ) == 'both'
            ) ||
            (
                in_array( $updated_details[ 'TYPE' ], [ 'nexo_order_advance'] ) && 
                store_option( 'expiring_order_type' ) == 'incomplete'
            )
        ) { 
            // if order aging is defined
            if( store_option( 'enable_order_aging', 'no' ) == 'yes' ) {
                // if date is not defined, the we'll just use the current date
                $updated_details[ 'EXPIRATION_DATE' ]         =   Carbon::parse( date_now() )
                ->addDays( store_option( 'expiration_time', 0 ) )->toDateTimeString();
            }
        }

        $updated_details    =   $this->events->apply_filters( 'after_order_placed_details', $updated_details, $data );

        $this->db->where( 'ID', $fresh_order[ 'ID' ] )
            ->update( store_prefix() . 'nexo_commandes', $updated_details );
        
        $order_details     =   array_merge( $order_details, $updated_details );

        return compact( 'order_details', 'current_order', 'data' );
    }

    /**
     * Get the order shipping informations
     * @param int order id
     * @return array
     */
    public function getOrderShippingInformations( $order_id )
    {
        $order  =   $this->get_order_products($order_id, true);
        $orderShipping      =   $this->db->where( 'ref_order', $order[ 'order' ][0][ 'ID' ] )
            ->where( 'ref_shipping', 0 )
            ->get( store_prefix() . 'nexo_commandes_shippings' )
            ->result_array();
        
        /**
         * Order is not empty
         * @return void
         */
        if( ! empty( $orderShipping ) ) {
            return $orderShipping;
        } else {
            return $this->db->where( 'ref_client', $order[ 'order' ][0][ 'REF_CLIENT' ] )
                ->where( 'type', 'shipping' )
                ->get( store_prefix() . 'nexo_clients_address' )
                ->result_array();
        }
    }

    /**
     * Get order meta data
     * @param int order id
     * @return array
     */
    public function getOrderMetas( $order_id )
    {
        $metas      =   $this->db->where( 'REF_ORDER_ID', $order_id )
            ->get( store_prefix() . 'nexo_commandes_meta' )
            ->result_array();
        
        $result     =   [];

        foreach( $metas as $meta ) {
            $result[ $meta[ 'KEY' ] ]   =   $meta[ 'VALUE' ];
        }

        return $result;
    }
}
