<?php
class NexoCustomersController extends CI_Model
{

    public function crud_header()
    {
        if (
            ! User::can('nexo.create.customers')  &&
            ! User::can('nexo.edit.customers') &&
            ! User::can('nexo.delete.customers') && 
            ! User::can('nexo.view.customers')
        ) {
            return show_error( __( 'Vous n\'avez pas accès à cette fonctionnalité.', 'nexo' ) );
        }

		/**
		 * This feature is not more accessible on main site when
		 * multistore is enabled
		**/

		if( multistore_enabled() && ! is_multistore() ) {
			return show_error( __( 'Cette fonctionnalité a été désactivée', 'nexo' ) );
		}

        $crud = new grocery_CRUD();
        $crud->set_subject(__('Clients', 'nexo'));
        $crud->set_table($this->db->dbprefix( store_prefix() . 'nexo_clients'));

		// If Multi store is enabled
		// @since 2.8
		$fields					=	array(
            'REF_GROUP',
            'NOM',
            'PRENOM',
            'EMAIL',
            'TEL',
            'CITY',
            'STATE',
            'COUNTRY',
            'POST_CODE',
            'COMPANY_NAME',
            'DATE_NAISSANCE',
            'AVATAR',
            'ADRESSE',
            'DESCRIPTION',
            'AUTHOR',
            'DATE_CREATION',
            'DATE_MOD'
		);

        $fields             =   $this->events->apply_filters( 'nexo_clients_fields', $fields );
        $customer_columns   =   $this->events->apply_filters( 'nexo_clients_columns', [ 'NOM', 'EMAIL', 'TEL', 'OVERALL_COMMANDES', 'TOTAL_SPEND', 'REF_GROUP', 'AUTHOR', 'DATE_CREATION', 'DATE_MOD' ] );

        if ( store_option( 'nexo_enable_reward_system', 'no' ) === 'yes' ) {
            array_splice( $customer_columns, 5, 0, 'REWARD_POINT_COUNT' );
        }

		$crud->set_theme('bootstrap');
        $crud->columns( $customer_columns );
        $crud->fields( $fields );

        $crud->display_as('NOM', __('Nom', 'nexo'));
        $crud->display_as('EMAIL', __('Email', 'nexo'));
        $crud->display_as('OVERALL_COMMANDES', __('Achats effectués', 'nexo'));
        $crud->display_as('NBR_COMMANDES', __('Nbr Commandes (sess courante)', 'nexo'));
        $crud->display_as('TEL', __('Téléphone', 'nexo'));
        $crud->display_as('PRENOM', __('Prénom', 'nexo'));
        $crud->display_as('DATE_NAISSANCE', __('Date de naissance', 'nexo'));
        $crud->display_as('ADRESSE', __('Adresse', 'nexo'));
        $crud->display_as('TOTAL_SPEND', __('Dépense effectué', 'nexo'));
        $crud->display_as('REWARD_POINT_COUNT', __('Total Points', 'nexo'));
        $crud->display_as('LAST_ORDER', __('Dernière commande', 'nexo'));
        $crud->display_as('AVATAR', __('Avatar', 'nexo'));
        $crud->display_as('STATE', __('Pays', 'nexo'));
        $crud->display_as('CITY', __('Ville', 'nexo'));
        $crud->display_as('POST_CODE', __('Code postale', 'nexo'));
        $crud->display_as('COUNTRY', __('Continent', 'nexo'));
        $crud->display_as('DATE_CREATION', __('Crée', 'nexo'));
        $crud->display_as('DATE_MOD', __('Modifié le', 'nexo'));
        $crud->display_as('AUTHOR', __('Par', 'nexo'));
        $crud->display_as('DESCRIPTION', __('Description', 'nexo'));
        $crud->display_as('REF_GROUP', __('Groupe', 'nexo'));
        $crud->display_as( 'COMPANY_NAME', __( 'Nom de la compagnie', 'nexo' ) );

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

        $crud->change_field_type('AUTHOR', 'invisible');
        $crud->change_field_type('DATE_MOD', 'invisible');
        $crud->change_field_type('DATE_CREATION', 'invisible');

        $crud->callback_before_update(array( $this, '__update' ));
        $crud->callback_before_insert(array( $this, '__insert' ));

        $crud->callback_column( 'TOTAL_SPEND', function( $data ){
            get_instance()->load->model( 'Nexo_Misc' );
            return get_instance()->Nexo_Misc->cmoney_format( $data, true );
        });

        $crud->callback_column( 'EMAIL', function( $data ){
            return empty( $data ) ? __( 'Non Défini', 'nexo' ) : $data;
        });

        $crud->callback_column( 'TEL', function( $data ){
            return empty( $data ) ? __( 'Non Défini', 'nexo' ) : $data;
        });

        $crud->set_field_upload('AVATAR', get_store_upload_path() . '/customers/');

        // XSS Cleaner
        $this->events->add_filter('grocery_callback_insert', array( $this->grocerycrudcleaner, 'xss_clean' ));
        $this->events->add_filter('grocery_callback_update', array( $this->grocerycrudcleaner, 'xss_clean' ));
        $crud->required_fields('NOM', 'REF_GROUP');

		$crud->set_relation('REF_GROUP', store_prefix() . 'nexo_clients_groups', 'NAME');
        $crud->set_relation('AUTHOR', 'aauth_users', 'name');
        $crud->set_rules('EMAIL', __('Email', 'nexo'), 'valid_email');

        $crud->unset_jquery();

        // @since 3.1
        $crud->unset_add();
        $crud->unset_edit();

        // add a custom action on header
        $this->events->add_filter( 'grocery_header_buttons', function( $menus ) {
            $menus[]        =   [
                'text'      =>  __( 'Ajouter un client', 'nexo' ),
                'url'       =>  dashboard_url([ 'customers', 'add' ])
            ];
            return $menus;
        });

        $crud->add_action( __( 'Modifier', 'nexo' ), null, dashboard_url([ 'customers', 'edit' ] ) . '/', 'fa fa-edit' );
        $crud->add_action( __( 'Coupons', 'nexo' ), null, dashboard_url([ 'customers', 'coupons' ] ) . '/', 'fa fa-list' );
        $crud->add_action( __( 'Réinitialiser le compteur', 'nexo' ), null, dashboard_url([ 'customers', 'reset-coupon-count' ] ) . '/', 'fa fa-refresh' );

        // Load Nexo Customer Clients Crud
        $crud   =   $this->events->apply_filters( 'customers_crud_loaded', $crud );

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
     * Create Customer
    **/

    public function __update($post)
    {
        $post[ 'DATE_MOD' ]            =    date_now();
        $post[ 'AUTHOR' ]            =    User::id();

        /**
         * Hook while updating customers
         */
        get_instance()->events->do_action( 'update_customers', $post );

        return $post;
    }

    /**
     * Callback before insert
    **/

    public function __insert($post)
    {
        $post[ 'DATE_CREATION' ]    =    date_now();
        $post[ 'AUTHOR' ]            =    User::id();

        /**
         * Hook while creating customers
         */
        get_instance()->events->do_action( 'create_customers', $post );

        return $post;
    }

    public function lists($page = 'index', $id = null)
    {
		global $PageNow;
		$PageNow			=	'nexo/clients/list';

        if ($page == 'index') {
            $this->Gui->set_title( store_title( __('Liste des clients', 'nexo')) );
        } elseif ($page == 'delete') {
            nexo_permission_check('nexo.delete.customers');

            // Checks whether an item is in use before delete
            nexo_availability_check($id, array(
                array( 'col'    =>    'REF_CLIENT', 'table'    =>    store_prefix() . 'nexo_commandes' )
            ));

            /**
             * Hook while deleting customers
             */
            get_instance()->events->do_action( 'delete_customers', $id );
        } else {
            $this->Gui->set_title( store_title( __('Liste des clients', 'nexo') ));
        }

        $data[ 'crud_content' ]    =    $this->crud_header();
        $_var1                    =    'clients';
        return $this->load->view('../modules/nexo/views/' . $_var1 . '-list.php', $data, true );
    }

    public function add()
    {
		global $PageNow;
        $PageNow			=	'nexo/clients/add';
        
        $this->load->model( 'Nexo_Misc' );

        if ( ! User::can('nexo.create.customers')) {
            nexo_access_denied();
        }

        $data                   =   [];        
        $data[ 'clients' ]      =   [];
        $data[ 'client_id' ]    =   0;
        $data[ 'groups' ]       =   $this->Nexo_Misc->customers_groups();

        // @since 3.1.0
        $this->events->add_action( 'dashboard_footer', function() use ( $data ) {
            get_instance()->load->module_view( 'nexo', 'customers.script', $data );
        });

        $this->Gui->set_title( store_title( __( 'Add a new customer', 'nexo' ) ) );
        return $this->load->module_view( 'nexo', 'customers.gui', null, true );
    }
    
    /**
     * Edit customer
     * @param int customer id
     * @return void
    **/
    public function edit( $customer_id ) 
    {
        global $PageNow;
		$PageNow			=	'nexo/clients/add';

        if (! User::can('nexo.edit.customers')) {
            nexo_access_denied();
        }

        $this->load->module_model( 'nexo', 'NexoCustomersModel' );
        $data[ 'clients' ]      =   $this->NexoCustomersModel->get( $customer_id );
        $data[ 'client_id' ]    =   $customer_id;
        $data[ 'groups' ]       =   $this->Nexo_Misc->customers_groups();

        // @since 3.1.0
        $this->events->add_action( 'dashboard_footer', function() use ( $data ) {
            get_instance()->load->module_view( 'nexo', 'customers.script', $data );
        });

        $this->Gui->set_title( store_title( __( 'Add a new customer', 'nexo' ) ) );
        return $this->load->module_view( 'nexo', 'customers.gui', null, true );
    }

    /**
     * User Groups header
     *
    **/

    public function groups_header( $page )
    {
        if( User::cannot( 'nexo.view.customers-groups' ) ) {
            return nexo_access_denied();
        }

		/**
		 * This feature is not more accessible on main site when
		 * multistore is enabled
		**/

		if( multistore_enabled() && ! is_multistore() ) {
			return show_error( __( 'Cette fonctionnalité a été désactivée.', 'nexo' ) );
		}

        $crud = new grocery_CRUD();
        $crud->set_subject(__('Groupes d\'utilisateurs', 'nexo'));
        $crud->set_table($this->db->dbprefix( store_prefix() . 'nexo_clients_groups'));

		$fields				=	array( 'NAME', 'REF_REWARD', 'DISCOUNT_TYPE', 'DISCOUNT_PERCENT', 'DISCOUNT_AMOUNT', 'DISCOUNT_ENABLE_SCHEDULE', 'DISCOUNT_START', 'DISCOUNT_END', 'DESCRIPTION',  'AUTHOR', 'DATE_CREATION', 'DATE_MODIFICATION' );

		$crud->set_theme('bootstrap');

        $crud->columns('NAME', 'AUTHOR', 'REF_REWARD', 'DISCOUNT_TYPE', 'DISCOUNT_PERCENT', 'DISCOUNT_AMOUNT', 'DATE_CREATION', 'DATE_MODIFICATION');
        $crud->fields( $fields );

        $crud->display_as('NAME', __('Nom', 'nexo'));
        $crud->display_as( 'REF_REWARD', __( 'Récompense Assignée', 'nexo' ) );
        $crud->display_as('DESCRIPTION', __('Description', 'nexo'));
        $crud->display_as('AUTHOR', __('Auteur', 'nexo'));
        $crud->display_as('DATE_CREATION', __('Date de création', 'nexo'));
        $crud->display_as('DISCOUNT_TYPE', __('Type de remise', 'nexo'));
        $crud->display_as('DISCOUNT_PERCENT', __('Pourcentage de remise (Sans "%")', 'nexo'));
        $crud->display_as('DISCOUNT_AMOUNT', __('Montant de la remise', 'nexo'));
        $crud->display_as('DISCOUNT_ENABLE_SCHEDULE', __('Activer la planification', 'nexo'));
        $crud->display_as('DISCOUNT_START', __('Début de la planification', 'nexo'));
        $crud->display_as('DISCOUNT_END', __('Fin de la planification', 'nexo'));
        $crud->display_as('DATE_MODIFICATION', __('Date de modification', 'nexo'));

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

        $crud->callback_column( 'REF_REWARD', function( $reward_id ) {
            
            get_instance()->load->module_model( 'nexo', 'NexoRewardSystemModel', 'reward_model' );
            $reward      =   get_instance()->reward_model->getSingle( $reward_id );
            
            if ( $reward === false ) {
                return __( 'Indisponible', 'nexo' );
            } else {
                return $reward[ 'NAME' ];
            }

        });

        $crud->set_relation('AUTHOR', 'aauth_users', 'name');

        if ( in_array( $page, [ 'edit', 'add' ] ) ) {
            $crud->set_relation('REF_REWARD', store_prefix() . 'nexo_rewards_system', 'NAME' );
        }

        // Load Field Type
        $crud->field_type('DISCOUNT_TYPE', 'dropdown', $this->config->item('nexo_discount_type'));
        $crud->field_type('DISCOUNT_ENABLE_SCHEDULE', 'dropdown', $this->config->item('nexo_true_false'));

        // Callback avant l'insertion
        $crud->callback_before_insert(array( $this, '__group_insert' ));
        $crud->callback_before_update(array( $this, '__group_update' ));

        // XSS Cleaner
        $this->events->add_filter('grocery_callback_insert', array( $this->grocerycrudcleaner, 'xss_clean' ));
        $this->events->add_filter('grocery_callback_update', array( $this->grocerycrudcleaner, 'xss_clean' ));

        // Field Visibility
        $crud->change_field_type('DATE_CREATION', 'invisible');
        $crud->change_field_type('DATE_MODIFICATION', 'invisible');
        $crud->change_field_type('AUTHOR', 'invisible');

        $crud->required_fields('NAME', 'DISCOUNT_TYPE');

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
     * Groups
    **/

    public function groups($page = 'index', $id = null)
    {
		global $PageNow;
        $PageNow			=	'nexo/clients_groups/list';

        if ($page == 'index') {
            $this->Gui->set_title( store_title( __('Groupes', 'nexo')) );
        } elseif ($page == 'delete') {
            nexo_permission_check('nexo.delete.customers-groups');

            // Checks whether an item is in use before delete
            nexo_availability_check($id, array(
                array( 'col'    =>    'REF_GROUP', 'table'    =>    store_prefix() . 'nexo_clients' )
            ));

            /**
             * Hook while deleting customers groups
             */
            get_instance()->events->do_action( 'delete_customers_groups', $id );

        } else {
            $this->Gui->set_title( store_title( __('Ajouter/Modifier un groupe de clients', 'nexo') ) );
        }

        $data[ 'crud_content' ]    =    $this->groups_header( $page );
        return $this->load->view('../modules/nexo/views/user-groups.php', $data, true );
    }

    /**
     * Callback
    **/

    public function __group_insert($data)
    {
        $data[ 'DATE_CREATION' ]    =    date_now();
        $data[ 'AUTHOR' ]            =    User::id();

        /**
         * Hook while creating customer groups
         */
        get_instance()->events->do_action( 'create_customers_groups', $data );

        return $data;
    }

    public function __group_update($data)
    {
        $data[ 'DATE_MODIFICATION' ]    =    date_now();
        $data[ 'AUTHOR' ]                =    User::id();

        /**
         * Hook while updating customer groups
         */
        get_instance()->events->do_action( 'update_customers_groups', $data );
        
        return $data;
    }

    public function defaults()
    {
        $this->lists();
    }

    /**
     * Save Import
     * @return json
     */
    public function saveImport()
    {

    }

    /**
     * Show Import
     * shows the importation page
     * @return view
     */
    public function showImport()
    {
        $this->events->add_action( 'dashboard_footer', function(){
            get_instance()->load->module_view( 'nexo', 'customers.import-script' );
        });
        $this->Gui->set_title( __( 'Importer des clients', 'nexo' ) );
        $this->load->module_view( 'nexo', 'customers.import-gui' );
    }

    public function resetCounter( $customer_id )
    {
        $this->load->module_model( 'nexo', 'NexoRewardSystemModel', 'reward_model' );
        $this->reward_model->resetCouponCount( $customer_id );
        return redirect( dashboard_url([ 'customers?notice=counter-resetted' ]) );
    }

    public function customerCouponsHeader( $customer_id )
    {
        if (
            ! User::can('nexo.create.customers')  &&
            ! User::can('nexo.edit.customers') &&
            ! User::can('nexo.delete.customers') && 
            ! User::can('nexo.view.customers')
        ) {
            return show_error( __( 'Vous n\'avez pas accès à cette fonctionnalité.', 'nexo' ) );
        }

		/**
		 * This feature is not more accessible on main site when
		 * multistore is enabled
		**/

		if( multistore_enabled() && ! is_multistore() ) {
			return show_error( __( 'Cette fonctionnalité a été désactivée', 'nexo' ) );
		}

        $crud = new grocery_CRUD();

        $crud->set_subject(__('Coupons du client', 'nexo'));
        $crud->set_table($this->db->dbprefix( store_prefix() . 'nexo_coupons' ));

        $customer_columns   =   $this->events->apply_filters( 'nexo_customers_coupons_columns', [ 
            'CODE',
            'EXPIRY_DATE',
            'USAGE_COUNT',
            'DISCOUNT_TYPE',
            'AMOUNT',
            'DATE_CREATION',
        ] );

		$crud->set_theme('bootstrap');
        $crud->columns( $customer_columns );

        $crud->display_as('CODE', __('Code', 'nexo'));
        $crud->display_as('EXPIRY_DATE', __('Date d\'expiration', 'nexo'));
        $crud->display_as('USAGE_COUNT', __('Utlisation', 'nexo'));
        $crud->display_as('DISCOUNT_TYPE', __('Type de remise', 'nexo'));
        $crud->display_as('AMOUNT', __('Valeur', 'nexo'));
        $crud->display_as('DATE_CREATION', __('Crée le', 'nexo'));
        $crud->where( 'REF_CUSTOMER', $customer_id );

        /**
         * Callback to support date formating
         * @since 3.12.8
         */
        $crud->callback_column( 'DATE_CREATION', function( $date ) {
            $datetime   =    new DateTime( $date ); 
            return $datetime->format( store_option( 'nexo_datetime_format', 'Y-m-d h:i:s' ) );
        });

        $crud->callback_column( 'USAGE_COUNT', function( $value, $row ) {
            return $value . '/' . ( intval( $row->USAGE_LIMIT ) > 0 ? $row->USAGE_LIMIT : '&infin;' );
        });

        $crud->unset_jquery();

        // @since 3.1
        $crud->unset_add();
        $crud->unset_edit();

        // Load Nexo Customer Clients Crud
        $crud   =   $this->events->apply_filters( 'customers_coupons_crud_loaded', $crud );

        $output = $crud->render();

        foreach ($output->js_files as $files) {
            $this->enqueue->js(substr($files, 0, -3), '');
        }
        foreach ($output->css_files as $files) {
            $this->enqueue->css(substr($files, 0, -4), '');
        }

        return $output;
    }

    public function customerCoupons( $customer_id )
    {
        $this->load->module_model( 'nexo', 'NexoCustomersModel', 'customer_model' );
        $customer   =   $this->customer_model->getSingle( $customer_id );
        $this->Gui->set_title( sprintf( __( 'Coupons du client : %s', 'nexo' ), $customer[ 'NOM' ] ) );
        
        $data[ 'crud_content' ]    =    $this->customerCouponsHeader( $customer_id );
        return $this->load->module_view( 'nexo', 'customers.coupons', $data, true );
    }
}