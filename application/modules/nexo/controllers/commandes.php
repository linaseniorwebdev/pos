<?php
class NexoOrdersController extends CI_Model
{
    public function crud_header()
    {
        if( ! User::can( 'nexo.view.orders' ) ) {
            return nexo_access_denied();
        }

		/**
		 * This feature is not more accessible on main site when
		 * multistore is enabled
		**/

		if( multistore_enabled() && ! is_multistore() ) {
			return show_error( __( 'Cette fonctionnalité a été désactivée', 'nexo' ) );
		}

        global $Options;

        $this->load->model('Nexo_Checkout');
        $this->load->model('Nexo_Misc');
        $this->load->module_config( 'nexo', 'nexo');

        $crud = new grocery_CRUD();
        $crud->set_theme('bootstrap');
        $crud->set_subject(__('Vente', 'nexo'));
        $crud->set_table($this->db->dbprefix( store_prefix() . 'nexo_commandes'));

		/**
		 * Hide register Cols when register option is disabled
		 * @since 2.7.7
		**/

		$cols       	=    array( 'CODE', 'REF_REGISTER', 'TITRE', 'REF_CLIENT', 'TOTAL', 'PAYMENT_TYPE', 'TYPE', 'STATUS', 'DATE_CREATION', 'AUTHOR' );
		$edit_link		=	site_url(array( 'rest', 'nexo', 'registers' )) . '/';
		$edit_class		=	'select_register';

		if( in_array( @$Options[ store_prefix() .'nexo_enable_registers' ], array( null, 'non' ) ) ){
            // better way to remove register :\
            foreach( $cols as $index => $col ) {
                if( $col == 'REF_REGISTER' ) {
                    unset( $cols[ $index ] );
                }
            }
            
			$edit_link		=	site_url( array( 'dashboard',  store_slug(), 'nexo', 'use', 'register', 'default' ) ) . '/';
			$edit_class		=	'';
		}

        // add filter for table columns
        $cols           =   $this->events->apply_filters( 'nexo_commandes_columns', $cols );

        if ( in_array( @$Options[ store_prefix() .'nexo_vat_type' ],  [ 'fixed', 'variable' ]) ) {
            array_splice($cols, 5, 0, 'TVA');
        }

        $crud->order_by( 'ID', 'desc' );

		$crud->unset_edit();
        $crud->columns($cols);

        // Add custom Actions
        $crud->add_action(
			__( 'Reçu de caisse', 'nexo'),
			'',
			site_url(array( 'dashboard', store_slug(), 'nexo', 'orders', 'receipt' )) . '/',
			'fa fa-file'
        );

        $crud->add_action(
			__( 'Facture', 'nexo' ),
			'',
			site_url(array( 'dashboard', store_slug(), 'nexo', 'orders', 'invoice' )) . '/',
			'fa fa-file'
        );
        
        $crud->callback_column( 'TOTAL', function( $price ){
            get_instance()->load->model( 'Nexo_Misc' );
            return get_instance()->Nexo_Misc->cmoney_format( $price, true );
        });

        $crud->callback_column( 'PAYMENT_TYPE', function( $type ){
            $types  =   get_instance()->config->item( 'nexo_all_payment_types' );
            if( ! empty( $type ) ) {
                return isset( $types[ $type ] ) ? $types[ $type ] : __( 'Inconnu', 'nexo' );
            }
            return __( 'Non défini', 'nexo' );
        });

        $crud->callback_column( 'STATUS', function( $status ) {
            $orderStatuses  =   get_instance()->config->item( 'nexo_orders_status' );
            return @$orderStatuses[ $status ] ?: __( 'Indéfini', 'nexo' );
        });

        /**
         * Callback to support date formating
         * @since 3.12.8
         */
        $crud->callback_column( 'DATE_CREATION', function( $date ) {
            $datetime   =    new DateTime( $date ); 
            return $datetime->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
        });
        $crud->callback_column( 'DATE_MODIFICATION', function( $date ) {
            $datetime   =    new DateTime( $date ); 
            return $datetime->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
        });

        $crud->callback_column( 'TITRE', function( $titre ) {
            if( empty( $titre ) ) {
                return __( 'Non Défini', 'nexo' );
            }
            return $titre;
        });

        $crud->display_as('CODE', __('Code', 'nexo'));
        $crud->display_as('REF_CLIENT', __('Client', 'nexo'));
        $crud->display_as('REMISE', __('Remise Expresse', 'nexo'));
        $crud->display_as('SOMME_PERCU', __('Somme perçu', 'nexo'));
        $crud->display_as('AUTHOR', __('Par', 'nexo'));
        $crud->display_as('PAYMENT_TYPE', __('Paiement', 'nexo'));
        $crud->display_as('TYPE', __( 'Paiement', 'nexo'));
        $crud->display_as('STATUS', __( 'Etat', 'nexo'));
        $crud->display_as('TITRE', __('Titre', 'nexo'));
        $crud->display_as('TVA', __('TVA', 'nexo'));
        $crud->display_as('DATE_CREATION', __('Date', 'nexo'));
        $crud->display_as('DATE_MOD', __('Date de modification', 'nexo'));
        $crud->display_as('TOTAL', __('Total', 'nexo'));
		$crud->display_as( 'REF_REGISTER', __( 'Caisse', 'nexo' ) );

		// $crud->order_by('DATE_CREATION', 'desc');

        $crud->field_type('TYPE', 'dropdown', $this->config->item('nexo_order_types'));
        $crud->field_type('PAYMENT_TYPE', 'dropdown', $this->config->item('nexo_all_payment_types'));

		$crud->set_relation('REF_CLIENT', store_prefix() . 'nexo_clients', 'NOM');
		$crud->set_relation('REF_REGISTER', store_prefix() . 'nexo_registers', 'NAME');
		$crud->set_relation('AUTHOR', 'aauth_users', 'name');

        $crud->change_field_type('RABAIS', 'invisible');
        $crud->change_field_type('RISTOURNE', 'invisible');
        $crud->change_field_type('CODE', 'invisible');
        $crud->change_field_type('TOTAL', 'invisible');
        $crud->change_field_type('DATE_CREATION', 'invisible');
        $crud->change_field_type('DATE_MOD', 'invisible');
        $crud->change_field_type('AUTHOR', 'invisible');
        $crud->change_field_type('DISCOUNT_TYPE', 'invisible');
        $crud->change_field_type('TVA', 'invisible');

		$crud->unset_add();

        // XSS Cleaner
        $this->events->add_filter('grocery_callback_insert', array( $this->grocerycrudcleaner, 'xss_clean' ));
        $this->events->add_filter('grocery_callback_update', array( $this->grocerycrudcleaner, 'xss_clean' ));
        // Filter Class
        $this->events->add_filter('grocery_crud_list_item_class', array( $this, 'filter_grocery_list_item_class' ), 10, 2);
        $this->events->add_filter('grocery_filter_edit_button', array( $this, 'filter_edit_button' ), 10, 4);
        $this->events->add_filter('grocery_filter_actions', array( $this, 'filter_grocery_actions' ), 10);

        // Run special commande on current crud object
        $crud   =   $this->events->apply_filters( 'nexo_commandes_loaded', $crud );

		$this->events->add_filter( 'grocery_row_actions_output', function( $filter, $row ) use ( $edit_link ) {

            // only order allowed for print are displayed 
            $query  =   $this->db->where( 'ID', $row->ID )->get( store_prefix() . 'nexo_commandes' )
            ->result();

			$filter     .= '<li><a href="javascript:void(0)" class="fa fa-plus order-details" data-order-id="' . $row->ID . '"> ' . __( 'Options', 'nexo' ) . '</a></li>';
            return $filter;
		}, 10, 2 );

        $crud->callback_before_delete(array( $this->Nexo_Checkout, 'commandes_delete' ));
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
     * Delete order
     * @param int order id
     * @return json
     */
    public function deleteOrder( $order_id ) 
    {
        /**
         * @since 3.0.13
        **/
        nexo_permission_check( 'nexo.delete.orders' );

        $data[ 'crud_content' ]    	=    $this->crud_header();
    }

    public function lists($page = 'home', $id = null)
    {
        global $NexoEditScreen, $NexoAddScreen, $Options, $PageNow;

        $NexoEditScreen    	= 	( bool ) preg_match('#dashboard\/nexo/commandes\/lists\/edit#', uri_string());
        $NexoAddScreen    	= 	( bool ) preg_match('#dashboard\/nexo/commandes\/lists\/add#', uri_string());
		$PageNow			=	'nexo/commandes/list';

        // @remove
        $this->events->add_action('dashboard_header', function () use ($NexoAddScreen, $NexoEditScreen) {
            /**
             * We Want to make sure that nothing appear before checkout load
            **/
            if ($NexoAddScreen || $NexoEditScreen) {
                ?>
            <style type="text/css">
			#meta-produits, .content-wrapper .content, .content-header {
				display:none;
			}
			</style>
			<?php

            }
        });

        // Load Sale List Script
        $this->events->add_action( 'dashboard_footer', function() {
            get_instance()->load->module_view( 'nexo', 'footer/sales-list' );
        });

        // Change add url
        // @remove
        $this->events->add_filter('grocery_add_url', function ($url) {
            return site_url(array( 'dashboard', 'nexo', 'commandes', 'lists', 'v2_checkout' ));
        });

        $data[ 'crud_content' ]    	=    $this->crud_header();
        $_var1    					=    'commandes';

        $this->Gui->set_title( store_title( __('Liste des commandes', 'nexo') ) );
        $this->load->view('../modules/nexo/views/' . $_var1 . '-list.php', $data);
    }

    public function defaults()
    {
        $this->lists();
    }

    /**
     * filter_grocery_list_item_class
     *
     * @param string
     * @param object Row Item
     * @return string
    **/

    public function filter_grocery_list_item_class($class, $row)
    {
        $Advance            =    'nexo_order_advance';
        $Cash               =   'nexo_order_comptant';
        $Estimate           =   'nexo_order_devis';

        $nexo_order_types   =    array_flip( $this->config->item( 'nexo_order_types') );

        if (@$nexo_order_types[ $row->TYPE ]    == $Advance) {
            return 'info';
        } elseif (@$nexo_order_types[ $row->TYPE ] == $Cash) {
            return 'success';
        } elseif (@$nexo_order_types[ $row->TYPE ] == $Estimate) {
            return 'warning';
        } else {
			//@since 2.7.1
			// Let custom class for unknow order type
            return $this->events->apply_filters_ref_array( 'order_list_class', array( $class, $row ) );
        }
    }

    /**
     * Filter Edit button
     * Hide edit button for cash orders
    **/

    public function filter_edit_button($string, $row, $edit_text, $subject)
    {
        $Advance        =    'nexo_order_advance';
        $Cash            =   'nexo_order_comptant';
        $Estimate        =   'nexo_order_devis';

        $nexo_order_types    =    array_flip($this->config->item('nexo_order_types'));

        if (in_array( @$nexo_order_types[ $row->TYPE ], $this->events->apply_filters( 'order_type_locked', array( $Cash ) ) ) ) {
            return;
        } elseif (in_array(@$nexo_order_types[ $row->TYPE ], $this->events->apply_filters( 'order_editable', array( $Estimate ) ) ) ) {
            ob_start();
            ?>
            <a href='<?php echo site_url(array( 'dashboard', store_slug(), 'nexo', 'commandes', 'lists', 'v2_checkout', $row->ID ));
            ?>' title='<?php echo $edit_text?> <?php echo $subject?>'>
                <span class='edit-icon fa fa-edit btn-default btn'></span>
            </a>
            <?php
            return ob_get_clean();
        } elseif ( in_array( @$nexo_order_types[ $row->TYPE ], $this->events->apply_filters( 'order_only_payable', array( $Advance ) ) ) ) {
            ob_start();
            ?>
            <a href='<?php echo site_url(array( 'dashboard', store_slug(), 'nexo', 'commandes', 'proceed', $row->ID ));
            ?>' title='<?php _e('Payer une commande', 'nexo');
            ?>'>
                <span class='edit-icon fa fa-money btn-success btn'></span>
            </a>
            <?php
            return ob_get_clean();
        }

        return $string;
    }

    /**
     * Filter Grocery Actions
     * Allow printing only on Complete orders
     * @param Array grocery actions
     * @return Array
    **/
    public function filter_grocery_actions( $data )
    {
        $grocery_actions_obj        =   $data[0];
        $actions                    =   $data[1];
        $row                        =   $data[2];
        // return $grocery_actions_obj;
        foreach ($actions as $key => $action) {
            $order_type        =    array_flip($this->config->item('nexo_order_types'));

            if ( ! in_array( @$order_type[ $row->TYPE ], $this->events->apply_filters( 'allowed_order_for_print', [ 'nexo_order_comptant' ] ) ) && $action->css_class == 'btn btn-info fa fa-file') {
                unset($grocery_actions_obj[ $key ]);
            }
			// // Hide edit for complete order
			// if ( ! in_array( @$order_type[ $row->TYPE ], $this->events->apply_filters( 'order_editable', [ 'nexo_order_comptant' ] ) ) && trim( $action->css_class ) == 'btn btn-default fa fa-edit' ) {
            //     unset($grocery_actions_obj[ $key ]);
            // }
        }

        return [ $grocery_actions_obj, $actions, $row ];
    }
}