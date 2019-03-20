<?php
class NexoProvidersController extends CI_Model
{
    public function crud_header()
    {
        if( User::cannot( 'nexo.view.providers' ) ) {
            return nexo_access_denied();
        }

		/**
		 * This feature is not more accessible on main site when
		 * multistore is enabled
		**/
		
		if( ( multistore_enabled() && ! is_multistore() ) && $this->events->add_filter( 'force_show_inventory', false ) == false ) {
			return show_error( __( 'Cette fonctionnalité a été désactivée', 'nexo' ) );
		}
        
        $crud = new grocery_CRUD();
        $crud->set_subject(__('Fournisseurs', 'nexo'));
        $crud->set_theme('bootstrap');
        // $crud->set_theme( 'bootstrap' );
        $crud->set_table($this->db->dbprefix( store_prefix() . 'nexo_fournisseurs'));
		
		// If Multi store is enabled
		// @since 2.8		
        $fields					=	array( 'NOM', 'BP', 'TEL', 'EMAIL', 'DESCRIPTION', 'DATE_CREATION', 'DATE_MOD' );
        $columns                =   [ 'NOM', 'BP', 'TEL', 'EMAIL', 'DESCRIPTION', 'AUTHOR', 'DATE_CREATION', 'DATE_MOD' ];

        if( store_option( 'enable_providers_account', 'no' ) == 'yes' ) {
            array_splice( $columns, 1, 0, 'PAYABLE' );

            $crud->add_action( __( 'Historique', 'nexo' ), '', dashboard_url([ 'providers_history' ]) . '/', 'fa fa-line-chart' );
            $crud->add_action( __( 'Payer le fournisseur', 'nexo' ), '', dashboard_url([ 'sup-expenses' ]) . '/', 'fa fa-money' );
        }

        /**
         * Callback to support date formating
         * @since 3.12.8
         */
        $crud->callback_column( 'DATE_CREATION', function( $date ) {
            $datetime   =    new DateTime( $date ); 
            return $datetime->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
        });
        $crud->callback_column( 'DATE_MOD', function( $date ) {
            $datetime   =    new DateTime( $date ); 
            return $datetime->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
        });
		
		$crud->columns( $columns );
        $crud->fields( $fields );
        
        $crud->display_as('NOM', __('Nom du fournisseur', 'nexo'));
        $crud->display_as('PAYABLE', __('Somme due', 'nexo'));
        $crud->display_as('EMAIL', __('Email du fournisseur', 'nexo'));
        $crud->display_as('BP', __('BP du fournisseur', 'nexo'));
        $crud->display_as('TEL', __('Tel du fournisseur', 'nexo'));
        $crud->display_as('DESCRIPTION', __('Description du fournisseur', 'nexo'));
        $crud->display_as('DATE_CREATION', __('Crée le', 'nexo'));
        $crud->display_as('DATE_MOD', __('Modifié le', 'nexo'));
        $crud->display_as('AUTHOR', __('Auteur', 'nexo'));
        $crud->set_relation('AUTHOR', 'aauth_users', 'name');

        $crud->change_field_type( 'DATE_CREATION', 'invisible' );
        $crud->change_field_type( 'DATE_MOD', 'invisible' );

        $crud->callback_before_insert(function( $data ) {
            $data[ 'DATE_CREATION' ]    =   date_now();
            $data[ 'DATE_MOD' ]         =   date_now();
            $data[ 'AUTHOR' ]           =   User::id();
            return $data;
        });
        $crud->callback_before_update(function( $data ) {
            $data[ 'DATE_MOD' ]             =   date_now();
            $data[ 'AUTHOR' ]               =   User::id();
            return $data;
        });

        $crud->callback_column( 'PAYABLE', function( $price ){
            get_instance()->load->model( 'Nexo_Misc' ); return get_instance()->Nexo_Misc->cmoney_format( $price, true );
        });
        
        // XSS Cleaner
        $this->events->add_filter('grocery_callback_insert', array( $this->grocerycrudcleaner, 'xss_clean' ));
        $this->events->add_filter('grocery_callback_update', array( $this->grocerycrudcleaner, 'xss_clean' ));
        
        $crud->required_fields('NOM');
        
        $crud->set_rules('EMAIL', 'Email', 'valid_email');
        
        // $crud->columns('customerName','phone','addressLine1','creditLimit');

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

    public function history( $provider_id, $page = 0 )
    {
        $this->data               =   [];
        
        $provider       =   $this->db->where( 'ID', $provider_id )
        ->get( store_prefix() . 'nexo_fournisseurs' )
        ->result_array();

        if( ! $provider ) {
            return show_error( __( 'Impossible de récupérer le fournisseur.', 'nexo' ) );
        }

        // operation type 
        $this->data[ 'operation' ]      =   [
            'payment'           =>  __( 'Paiement', 'nexo' ),
            'stock_purchase'    =>  __( 'Livraison de produits', 'nexo' )
        ];

        $this->data[ 'provider' ]       =   $provider[0];
        $this->load->library("pagination");
        $config["base_url"] = dashboard_url([ 'fournisseurs', 'history', $provider_id ]);
        //EDIT THIS (to get a count of number of rows. Might have to add in a criteria (category etc)
        $config["total_rows"] = 
        $this->db
        ->select( '*' )
        ->from( store_prefix() . 'nexo_fournisseurs_history' )
        ->join( store_prefix() . 'nexo_premium_factures', store_prefix() . 'nexo_premium_factures.ID = ' . store_prefix() . 'nexo_fournisseurs_history.REF_INVOICE', 'left' )
        ->join( store_prefix() . 'nexo_arrivages', store_prefix() . 'nexo_arrivages.ID = ' . store_prefix() . 'nexo_fournisseurs_history.REF_SUPPLY', 'left' )
        ->where( store_prefix() . 'nexo_fournisseurs_history.REF_PROVIDER', $provider_id )        
        ->get()
        ->num_rows();
        //EDIT THIS
        // $config["uri_segment"] = 3;
        //EDIT THIS:
        $config["per_page"] = 20;
        $choice = $config["total_rows"] / $config["per_page"];
        $config["num_links"] = round($choice);
        $config['use_page_numbers'] = true; // use page numbers, or use the current row number (limit offset)
        // $page = ($this->uri->segment($config["uri_segment"] )) ? $this->uri->segment($config["uri_segment"] ) : 0;
        //EDIT THIS:
        $page = $page-1;
		if ($page<0) { 
			$page = 0;
        }

        $from = intval( $page ) * $config["per_page"];
        $this->data["results"] = $this->db
        ->select( '*,
        ' . store_prefix() . 'nexo_fournisseurs_history.DATE_CREATION as DATE_CREATION,
        ' . store_prefix() . 'nexo_premium_factures.ID as INVOICE_ID,
        ' . store_prefix() . 'nexo_arrivages.ID as SUPPLY_ID' )
        ->from( store_prefix() . 'nexo_fournisseurs_history' )
        ->join( 'aauth_users', 'aauth_users.id = ' . store_prefix()  .'nexo_fournisseurs_history.AUTHOR' )
        ->join( store_prefix() . 'nexo_premium_factures', store_prefix() . 'nexo_premium_factures.ID = ' . store_prefix() . 'nexo_fournisseurs_history.REF_INVOICE', 'left' )
        ->join( store_prefix() . 'nexo_arrivages', store_prefix() . 'nexo_arrivages.ID = ' . store_prefix() . 'nexo_fournisseurs_history.REF_SUPPLY', 'left' )
        ->limit( $config["per_page"], $from )
        ->where( store_prefix() . 'nexo_fournisseurs_history.REF_PROVIDER', $provider_id )
        ->order_by( store_prefix() . 'nexo_fournisseurs_history.DATE_CREATION', 'desc' )
        ->get()
        ->result_array();
        // styling/html stuff
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul><!--pagination-->';
        $config['first_link'] = '&laquo; First';
        $config['first_tag_open'] = '<li class="prev page">';
        $config['first_tag_close'] = '</li>' . "\n";
        $config['last_link'] = 'Last &raquo;';
        $config['last_tag_open'] = '<li class="next page">';
        $config['last_tag_close'] = '</li>' . "\n";
        $config['next_link'] = 'Next &rarr;';
        $config['next_tag_open'] = '<li class="next page">';
        $config['next_tag_close'] = '</li>' . "\n";
        $config['prev_link'] = '&larr; Previous';
        $config['prev_tag_open'] = '<li class="prev page">';
        $config['prev_tag_close'] = '</li>' . "\n";
        $config['cur_tag_open'] = '<li class="active"><a href="">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page">';
        $config['num_tag_close'] = '</li>' . "\n";
        $this->pagination->initialize($config);
        $this->data["pagination"] = $this->pagination->create_links();

        $this->events->add_action( 'dashboard_footer', function(){
            get_instance()->load->module_view( 'nexo', 'providers.history-script' );
        });
        
        $this->Gui->set_title( store_title( sprintf( __( '%s : Historique d\'approvisionnement', 'nexo' ), $provider[0][ 'NOM' ] ) ) );

        return $this->load->module_view( 'nexo', 'providers.history-gui', $this->data, true );
    }
    
    public function lists($page = 'index', $id = null)
    {
		global $PageNow;
		$PageNow			=	'nexo/fournisseurs/list';
		
        if ($page == 'index') {
            $this->Gui->set_title( store_title( __('Liste des fournisseurs', 'nexo' ) ) );
        } elseif ($page == 'delete') {
            nexo_permission_check('nexo.delete.providers');
            
            // Checks whether an item is in use before delete
            nexo_availability_check($id, array(
                array( 'col'    =>    'FOURNISSEUR_REF_ID', 'table'    =>    store_prefix() . 'nexo_arrivages' )
            ));
        } else {
            $this->Gui->set_title( store_title( __( 'Ajouter un nouveau fournisseur', 'nexo') ) );
        }
        
        $data[ 'crud_content' ]    =    $this->crud_header();
        $_var1                    =    'fournisseurs';
        return $this->load->view('../modules/nexo/views/' . $_var1 . '-list.php', $data, true );
    }
    
    public function add()
    {
		global $PageNow;
		$PageNow			=	'nexo/fournisseurs/add';
		
        if( User::cannot( 'nexo.create.providers' ) ) {
            return nexo_access_denied();
        }
        
        $data[ 'crud_content' ]    =    $this->crud_header();
        $_var1                    =    'fournisseurs';
        $this->Gui->set_title( store_title( __('Ajouter un nouveau fournisseur', 'nexo' ) ) );
        return $this->load->view('../modules/nexo/views/' . $_var1 . '-list.php', $data, true );
    }
    
    public function defaults()
    {
        return $this->lists();
    }
}