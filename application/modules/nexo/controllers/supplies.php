<?php
class NexoSuppliesController extends CI_Model
{
    public function crud_header()
    {
        if( ! User::can( 'nexo.view.supplies' ) ) {
            return nexo_access_denied();
        }

		/**
		 * This feature is not more accessible on main site when
		 * multistore is enabled
		**/

		if( ( multistore_enabled() && ! is_multistore() ) && $this->events->apply_filters( 'force_show_inventory', false ) == false ) {
            return show_error( __( 'Cette fonctionnalité a été désactivée.' ) );
		}

        $crud = new grocery_CRUD();
        $crud->set_theme('bootstrap');
        $crud->set_subject( __('Approvisionnements', 'nexo') );
		$crud->set_table( $this->db->dbprefix( store_prefix() . 'nexo_arrivages' ) );

        $crud->callback_column( 'VALUE', function( $price ){
            get_instance()->load->model( 'Nexo_Misc' );
            return get_instance()->Nexo_Misc->cmoney_format( $price, true );
        });

        /**
         * Callback to support date formating
         * @since 3.12.8
         */
        $crud->callback_column( 'DATE_CREATION', function( $date ) {
            $datetime   =    new DateTime( $date ); 
            return $datetime->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
        });

		// fields
		$fields			=	array( 'TITRE', 'REF_PROVIDER', 'DESCRIPTION', 'AUTHOR', 'DATE_CREATION', 'DATE_MOD' );
        $crud->columns( 'TITRE', 'REF_PROVIDER', 'VALUE', 'ITEMS', 'AUTHOR', 'DATE_CREATION' );
        $crud->fields( $fields );

        $crud->order_by('DATE_CREATION', 'asc');
        $crud->display_as('AUTHOR', __('Auteur', 'nexo'));
        $crud->display_as('TITRE', __('Nom de l\'arrivage', 'nexo'));
        $crud->display_as('DATE_CREATION', __('Crée le', 'nexo'));
        $crud->display_as('VALUE', __('Valeur', 'nexo'));
        $crud->display_as('ITEMS', __('Produits Inclus', 'nexo'));
        $crud->display_as('REF_PROVIDER', __('Fournisseur', 'nexo'));
        $crud->display_as('DESCRIPTION', __('Description', 'nexo'));
        $crud->display_as('FOURNISSEUR_REF_ID', __('Fournisseur', 'nexo'));

        $crud->change_field_type( 'AUTHOR', 'invisible' );
        $crud->change_field_type( 'DATE_CREATION', 'invisible' );
        $crud->change_field_type( 'DATE_MOD', 'invisible' );

        $crud->field_description( 'REF_PROVIDER', __( 'Permet de déterminer quel est le fournisseur de l\'ensemble de l\'approvisionnement.', 'nexo' ) );

        $this->events->add_filter( 'grocery_header_buttons', function( $actions ) {
            $actions[]      =   [
                'text'      =>  __( 'Faire un approvisionnement', 'nexo' ),
                'url'       =>  dashboard_url([ 'supplies', 'stock' ])  
            ];

            return $actions;
        });

        /**
         * Get the right provided according to the supplies
         */
        $this->load->module_model( 'nexo', 'Nexo_Stores_Model', 'store_model' );
        $this->load->module_model( 'nexo', 'NexoProvidersModel', 'providers_model' );

        $providers_model    =   $this->providers_model;
        $store_model        =   $this->store_model;

        $crud->callback_column( 'REF_PROVIDER', function( $provider, $row ) use ( $providers_model, $store_model ){
            if ( $row->PROVIDER_TYPE === 'store' ) {
                switch( $provider ) {
                    case '0':
                        return __( 'Entrepôt Principale', 'nexo' );
                    break;
                    default:
                        $store  =   $store_model->get( $provider );
                        return @$store[ 'NAME' ] ?: __( 'Boutique Inconnue', 'nexo' );
                    break;
                }
            } else {
                $provider   =   $providers_model->get( $provider );
                return @$provider[ 'NOM' ] ?: __( 'Fournisseur Inconnu', 'nexo' );
            }
        });

        // $crud->callback_column( 'PROVIDER_TYPE', function( $data ) {
        //     switch( $data) {
        //         case 'store': 
        //             return __( 'Boutique', 'nexo' );
        //         break;
        //         case 'provider': 
        //             return __( 'Fournisseur', 'nexo' );
        //         break;
        //         default: 
        //             return __( 'Fournisseur', 'nexo' );
        //         break;
        //     }
        // });

        $crud->set_relation('AUTHOR', 'aauth_users', 'name');
        $crud->set_relation('REF_PROVIDER', store_prefix() . 'nexo_fournisseurs', 'NOM' );
        $crud->callback_before_delete([ $this, '__delete_supplies' ]);
        
        $crud->callback_before_insert( function( $data ) {
            $data[ 'DATE_CREATION' ]    =   date_now();
            $data[ 'DATE_MOD' ]         =   date_now();
            $data[ 'AUTHOR' ]           =   User::id();
            return $data;
        });
        $crud->callback_before_update( function( $data ) {
            $data[ 'DATE_MOD' ]         =   date_now();
            $data[ 'AUTHOR' ]           =   User::id();
            return $data;
        });

        // Liste des produits
        $crud->add_action(__('Liste des produits', 'nexo'), '', site_url(array( 'dashboard', store_slug(), 'nexo', 'supplies', 'items' )) . '/', 'fa fa-list-ol');
        $crud->add_action(__('Etiquettes des articles', 'nexo'), '', dashboard_url([ 'supplies', 'labels',]) . '/', 'fa fa-tags');
        $crud->add_action(__('Facture de l\'arrivage', 'nexo'), '', dashboard_url([ 'supplies', 'invoice' ]) . '/', 'fa fa-file');
        $crud->add_action(__('Rafraichir', 'nexo'), '', dashboard_url([ 'supplies', 'refresh' ]) . '/', 'fa fa-money');
        // $crud->add_action(__('Valeur détaillée de l\'arrivage', 'nexo'), '', site_url(array( 'dashboard', store_slug(), 'nexo', 'supplies', 'detailed-worth' )) . '/', 'fa fa-file');

        $this->events->add_filter('grocery_callback_insert', array( $this->grocerycrudcleaner, 'xss_clean' ));
        $this->events->add_filter('grocery_callback_update', array( $this->grocerycrudcleaner, 'xss_clean' ));

        $crud->required_fields( 'TITRE', 'REF_PROVIDER' );

        $crud->unset_jquery();
        $output = $crud->render();

        foreach ($output->js_files as $files) {
            $this->enqueue->js(substr($files, 0, -3), '');
        }
        foreach ($output->css_files as $files) {
            $this->enqueue->css(substr($files, 0, -4), '');
        }
        return $output;
    }

    /**
     * Delete Supplies
     * Update all product stock
     * @param int current supplies
     * @return void
     */
    public function __delete_supplies( $supply_id ) 
    {
        $this->load->model( 'Nexo_Products','items' );
        // get all supplies made with that supplies
        $supplies   =   $this->db->where( 'REF_SHIPPING', $supply_id )
        ->get( store_prefix() . 'nexo_articles_stock_flow' )
        ->result_array();

        // -> update remaining quantity
        // -> delete supply entry
        foreach( $supplies as $supply ) {
            $fresh_item             =   $this->items->get_single( $supply[ 'REF_ARTICLE_BARCODE' ], 'CODEBAR' );
            $updated_quantity       =   floatval( $fresh_item[ 'QUANTITE_RESTANTE' ] ) - floatval( $supply[ 'QUANTITE' ] );
            
            // Update Remaining quantity
            $this->items->update_single( $fresh_item[ 'ID' ],[
                'QUANTITE_RESTANTE' =>  $updated_quantity
            ]);

            // Delete Stock Flow for this item
            $this->db->where( 'ID', $supply[ 'ID' ])
            ->delete( store_prefix() . 'nexo_articles_stock_flow' );

            // We might reduce supplier debt
        }
    }

    public function lists($page = 'index', $id = null)
    {
		global $PageNow;
		$PageNow			=	'nexo/arrivages/list';

        if ($page == 'index') {
            $this->Gui->set_title( store_title( __('Liste des approvisonnements', 'nexo')) );
        } elseif ($page == 'delete') { // Check Deletion permission

            nexo_permission_check( 'nexo.delete.supplies' );

            // Checks whether an item is in use before delete
            nexo_availability_check($id, array(
                array( 'col'    =>    'REF_SHIPPING', 'table'    =>    store_prefix() . 'nexo_articles' )
            ) );
        } 

        $data[ 'crud_content' ]    =    $this->crud_header();
        $_var1    =    'arrivages';
        $this->Gui->set_title( store_title( __( 'Liste des approvisionnements', 'nexo' ) ) );
        return $this->load->view('../modules/nexo/views/' . $_var1 . '-list.php', $data, true );
    }

    public function add()
    {
        if( User::cannot( 'nexo.create.supplies' ) ) {
            return nexo_access_denied();
        }
        
		global $PageNow;
		$PageNow			=	'nexo/arrivages/add';

        $data[ 'crud_content' ]    =    $this->crud_header();
        $_var1    =    'arrivages';
        $this->Gui->set_title( store_title( __( 'Ajouter une nouvelle livraison', 'nexo') ) );
        return $this->load->view('../modules/nexo/views/' . $_var1 . '-list.php', $data, true );
    }

    public function defaults()
    {
        return $this->lists();
	}

    /** 
     * Delivery Invoice
     * @return void
    **/

    public function delivery_invoice( $delivery_id )
    {
        global $Options;
        $this->db->select( '*,' .
        store_prefix() . 'nexo_arrivages.REF_PROVIDER as DELIVERY_PROVIDER,' .
        store_prefix() . 'nexo_arrivages.PROVIDER_TYPE as DELIVERY_PROVIDER_TYPE,' .
        store_prefix() . 'nexo_articles_stock_flow.REF_PROVIDER as STOCK_FLOW_PROVIDER' .
        '' )
            ->from( store_prefix() . 'nexo_arrivages' )
            ->join( store_prefix() . 'nexo_articles_stock_flow', store_prefix() . 'nexo_articles_stock_flow.REF_SHIPPING = ' . store_prefix() . 'nexo_arrivages.ID' )
            ->join( store_prefix() . 'nexo_articles', store_prefix() . 'nexo_articles.CODEBAR = ' . store_prefix() . 'nexo_articles_stock_flow.REF_ARTICLE_BARCODE' )
            ->where( store_prefix() . 'nexo_arrivages.ID', $delivery_id );
        
        if( @$_GET[ 'provider_id' ] != null ) {
            $this->db->where( store_prefix() . 'nexo_articles_stock_flow.REF_PROVIDER', $_GET[ 'provider_id' ] );
        }

        $items      =   $this->db->get()->result_array();

        // var_dump( $items );die;

        $this->load->library('parser');

        $data               =   [];
        $data[ 'items' ]    =   $items;
        $data[ 'template' ]						=	array();
        $data[ 'template' ][ 'shop_name' ]		=	@$Options[ store_prefix() . 'site_name' ];
        $data[ 'template' ][ 'shop_pobox' ]		=	@$Options[ store_prefix() . 'nexo_shop_pobox' ];
        $data[ 'template' ][ 'shop_fax' ]		=	@$Options[ store_prefix() . 'nexo_shop_fax' ];
        $data[ 'template' ][ 'shop_email' ]     =	@$Options[ store_prefix() . 'nexo_shop_email' ];
        $data[ 'template' ][ 'shop_street' ]    =	@$Options[ store_prefix() . 'nexo_shop_street' ];
        $data[ 'template' ][ 'shop_phone' ]     =	@$Options[ store_prefix() . 'nexo_shop_phone' ];

        return $this->load->module_view( 'nexo', 'deliveries.invoice', $data, true );
    }

    /**
     * Delivery Items CRUD
     * @return object
    **/

    public function delivery_items_crud( $delivery_id )
    {
        if( User::cannot( 'nexo.view.supplies') ) {
            return nexo_access_denied();
        }

		/**
		 * This feature is not more accessible on main site when
		 * multistore is enabled
		**/

		if( ( multistore_enabled() && ! is_multistore() ) && $this->events->apply_filters( 'force_show_inventory', false ) == false ) {
			redirect( array( 'dashboard', 'feature-disabled' ) );
		}

        $crud = new grocery_CRUD();
        $crud->set_theme('bootstrap');
        $crud->set_subject( __('Produits de l\'approvisionnement', 'nexo') );
		$crud->set_table( $this->db->dbprefix( store_prefix() . 'nexo_articles_stock_flow' ) );
        $crud->set_relation( 'REF_PROVIDER', store_prefix() . 'nexo_fournisseurs', 'NOM');
        $crud->set_relation( 'REF_SHIPPING', store_prefix() . 'nexo_arrivages', 'TITRE');
        $crud->set_relation( 'AUTHOR', 'aauth_users', 'name' );
        
        $crud->set_primary_key( 'CODEBAR', store_prefix() . 'nexo_articles' );
        $crud->set_relation( 'REF_ARTICLE_BARCODE', store_prefix() . 'nexo_articles', 'DESIGN');

        if( @$_GET[ 'provider_id' ] ) {
            $crud->where( store_prefix() . 'nexo_articles_stock_flow.REF_PROVIDER', $_GET[ 'provider_id' ] );
        }
        
        $crud->where( 
            '(' .
            store_prefix() . 'nexo_articles_stock_flow.TYPE = "supply" or ' .
            store_prefix() . 'nexo_articles_stock_flow.TYPE = "import"' .  
            ')' . 
            ' AND ' . store_prefix() . 'nexo_articles_stock_flow.REF_SHIPPING = ' . $delivery_id
        );
        // $crud->where_in( store_prefix() . 'nexo_articles_stock_flow.TYPE', 'import' );
        // $crud->where( store_prefix() . 'nexo_articles_stock_flow.REF_SHIPPING', $delivery_id );
        // $crud->or_where( store_prefix() . 'nexo_articles_stock_flow.TYPE', 'transfert_canceled' );
        // $crud->or_where( store_prefix() . 'nexo_articles_stock_flow.TYPE', 'transfert_rejected' );

        $crud->add_action( __('Historique d\'approvisionnement', 'nexo'), '', '', 'fa fa-eye', [ $this, 'supply_link' ]);

        $crud->columns( 'REF_ARTICLE_BARCODE', 'TYPE', 'BEFORE_QUANTITE', 'QUANTITE', 'AFTER_QUANTITE', 'UNIT_PRICE', 'REF_PROVIDER', 'DATE_CREATION', 'AUTHOR' );

        $crud->callback_column( 'TYPE', function( $type ){
            $config     =   get_instance()->config->item( 'stock-operation' );
            return $config[ $type ];
        });

        $crud->callback_before_update([ $this, '__before_update_history' ]);

        $crud->fields( 'QUANTITE', 'UNIT_PRICE', 'TOTAL_PRICE', 'BEFORE_QUANTITE', 'AFTER_QUANTITE' );

        $crud->callback_column( 'UNIT_PRICE', function( $type ){
            get_instance()->load->model( 'Nexo_Misc' );
            return get_instance()->Nexo_Misc->cmoney_format( $type );
        });

        $crud->unset_add();
        $crud->field_type( 'TOTAL_PRICE', 'hidden' );
        $crud->field_type( 'AFTER_QUANTITE', 'hidden' );
        $crud->field_type( 'BEFORE_QUANTITE', 'hidden' );
        // $crud->unset_edit();

        $crud->display_as('REF_ARTICLE_BARCODE', __('Produit', 'nexo'));
        $crud->display_as('REF_SHIPPING', __('Approvisionnement', 'nexo'));
        $crud->display_as('QUANTITE', __('Quantité', 'nexo'));
        $crud->display_as('UNIT_PRICE', __('Prix Unitaire', 'nexo'));
        $crud->display_as('REF_PROVIDER', __('Fournisseur', 'nexo'));
        $crud->display_as('DATE_CREATION', __('Crée le', 'nexo'));
        $crud->display_as('AUTHOR', __('Auteur', 'nexo'));
        $crud->display_as('BEFORE_QUANTITE', __('Avant', 'nexo'));
        $crud->display_as('AFTER_QUANTITE', __('Après', 'nexo'));
        $crud->display_as('TYPE', __('Opération', 'nexo'));

        $crud->unset_jquery();
        $output = $crud->render();

        foreach ($output->js_files as $files) {
            $this->enqueue->js(substr($files, 0, -3), '');
        }
        
        foreach ($output->css_files as $files) {
            $this->enqueue->css(substr($files, 0, -4), '');
        }
        return $output;
    }

    /**
     * Before Update history
    **/

    public function __before_update_history( $post, $index )
    {
        $history        =   $this->db->where( 'ID', $index )
        ->get( store_prefix() . 'nexo_articles_stock_flow' )
        ->result_array();

        if( $history ) {
            $items       =   $this->db
            ->where( 'REF_SHIPPING', $history[0][ 'REF_SHIPPING' ] )
            ->get( store_prefix() . 'nexo_articles_stock_flow' )
            ->result_array();

            $total_amount       =   0;
            $total_quantity     =   0;
            $current_item       =   [];
            foreach( $items as $item ) {
                if( $item[ 'ID' ] != $index ) {
                    if( $item[ 'UNIT_PRICE' ] != '0' && $item[ 'TOTAL_PRICE' ] != '0' ) {
                        $total              =   floatval( $item[ 'UNIT_PRICE' ]) * floatval( $item[ 'QUANTITE' ] );
                        $total_amount       +=  $total;
                        $total_quantity     +=  floatval( $item[ 'QUANTITE' ]);
                    }
                } else {
                    $current_item   =   $item;
                }
            }

            // update current supply total
            $item_details       =   $this->db->where( 'CODEBAR', $history[0][ 'REF_ARTICLE_BARCODE' ])
            ->get( store_prefix() . 'nexo_articles' )
            ->result_array();

            // remove previous remaning quantity
            $quantity               =   0;
            
            if( floatval( $item_details[0][ 'QUANTITE_RESTANTE' ] > 0 ) && floatval( $item_details[0][ 'QUANTITE_RESTANTE' ] ) - floatval( $history[0][ 'QUANTITE' ] ) >= 0 ) {
                $quantity           =   floatval( $item_details[0][ 'QUANTITE_RESTANTE' ] ) - floatval( $history[0][ 'QUANTITE' ] );
            }

            // new quantity
            $quantity           +=  floatval( $post[ 'QUANTITE' ] );

            // update new remaning quantity
            $this->db->where( 'CODEBAR', $history[0][ 'REF_ARTICLE_BARCODE' ] )
            ->update( store_prefix() . 'nexo_articles', [
                'QUANTITE_RESTANTE'     =>  $quantity
            ]);

            /**
             * Let's update the after field
             */
            $post[ 'AFTER_QUANTITE' ]    =   $post[ 'QUANTITE' ]   +   floatval( $post[ 'BEFORE_QUANTITE' ] );

            $this->db->where( 'ID', $history[0][ 'REF_SHIPPING' ] )->update( store_prefix() . 'nexo_arrivages', [
                'VALUE'         =>  $total_amount + ( floatval( $post[ 'UNIT_PRICE' ] ) * floatval( $post[ 'QUANTITE' ] ) ),
                'ITEMS'         =>  $total_quantity + floatval( $post[ 'QUANTITE' ] )
            ]);

            $this->events->do_action_ref_array( 'update_supply_history', [ $post, $index ]);
        }
        return $post;
    }

    /** 
     * Supply LInk
     * @param int primary key
     * @param array row obbject
     * @return string
    **/

    public function supply_link( $primary_key, $row )
    {
        return site_url(array( 'dashboard', store_slug(), 'nexo', 'items', 'history' )) . '/' . $row->REF_ARTICLE_BARCODE;
    }

    /**
     * Delivery Items
     * @return void
    **/

    public function delivery_items( $delivery_id, $page = 'index', $id = 0 )
    {
        // only supply, import and transfert can be edited
        if( $page == 'edit' ) {
            $stock  =   $this->db->where( 'ID', $id )->get( store_prefix() . 'nexo_articles_stock_flow' )
            ->result_array();

            if( ! in_array( $stock[0][ 'TYPE' ], $this->events->apply_filters( 'editable_stock_type', [ 'supply', 'import' ] ) ) ) {
                show_error( __( 'Vous ne pouvez pas modifier cet element', 'nexo' ) );
            }
        }

        $crud       =   $this->delivery_items_crud( $delivery_id );
        $this->Gui->set_title( store_title( __( 'Produits de l\'approvisionnement', 'nexo' ) ) );
        return $this->load->module_view( 'nexo', 'deliveries.supply-item-gui', compact( 'crud' ), true );
    }

    /**
     * Detailed Worth Invoice
     * @return void
     */
    public function detailed_worth( $supply_id )
    {
        $this->Gui->set_title( store_title( __( 'Valeur détaillé du rapport', 'nexo' ) ) );
        return $this->load->module_view( 'nexo', 'deliveries.detailed-worth-gui', null, true );
    }

    /**
     * Create Delivery Title
     * @param void
     * @return json response
     */
    public function createDeliveryTitle() 
    {
        
    }

    /**
     * refresh a specific delivery
     * @return void
     */
    public function refresh( $shipping_id )
    {
        $this->load->library( 'user_agent' );
        $this->load->module_model( 'nexo', 'NexoStockTaking' );
        $result     =   $this->NexoStockTaking->refresh_stock_taking( $shipping_id );
        return redirect( $this->agent->referrer() . '?notice=done' );
    }

}