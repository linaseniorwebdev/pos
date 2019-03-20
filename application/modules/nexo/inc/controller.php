<?php
class Nexo_Controller extends CI_Model
{
    public function __construct()
    {
		parent::__construct();
		$this->events->add_action( 'load_dashboard', array( $this, 'load_dashboard' ), 20 );
		$this->events->add_filter( 'admin_menus', array( $this, 'menus' ), 15);
    }

    public function menus($final)
    {
		// @since 2.7.7
		global $Nexo_Menus, $Options;
		$Nexo_Menus    =    [];
		$this->events->do_action('nexo_before_checkout', $Nexo_Menus);

		/***
		 * Display Store Menu only when multi store is enabled
		 * @since 2.8
		**/

		if( get_option( 'nexo_store' ) == 'enabled' ) {
			$Nexo_Menus[ 'nexo_shop' ]        =    array(
				array(
					'title'		=>        __('Boutiques', 'nexo'), // menu title
					'icon'		=>        'fa fa-cubes', // menu icon
					'disable'	=>    true,
					'permission' 	=>	[ 
						'nexo.enter.stores'
					]
				)
			);

			// Create a new store
			if ( User::in_group([ 'administrator', 'master', 'store.manager' ]) ) {
				$Nexo_Menus[ 'nexo_shop' ][]	=	array(
					'title'		=>        __('Liste des boutiques', 'nexo'), // menu title
					'href'		=>		site_url( array( 'dashboard', 'nexo', 'stores' ) ),
					'permission' 	=>		'nexo.view.stores'
				);
	
				$Nexo_Menus[ 'nexo_shop' ][]	=	array(
					'title'		=>        __('Ajouter une boutique', 'nexo'), // menu title
					'href'		=>		site_url( array( 'dashboard', 'nexo', 'stores', 'add' ) ),
					'permission' 	=>		'nexo.create.stores'
				);
			} else {
				$Nexo_Menus[ 'nexo_shop' ][]	=	array(
					'title'		=>        __('Boutiques', 'nexo'), // menu title
					'href'		=>		site_url( array( 'dashboard', 'nexo', 'stores', 'all' ) ),
					'permission'	=>		'nexo.enter.stores'
				);
			}

		}

		// @since 2.8
		// Adjust menu when multistore is enabled
		$uri			=	$this->uri->segment(2,false);
		$store_uri		=	'';

		if( $uri == 'stores' || in_array( @$Options[ 'nexo_store' ], array( null, 'disabled' ), true ) ) {

			// Only When Multi Store is enabled
			// @since 2.8

			if( @$Options[ 'nexo_store' ] == 'enabled' && $this->config->item( 'nexo_multi_store_enabled' ) ) {
				$store_uri	=	'nexo/stores/' . $this->uri->segment( 3, 0 ) . '/';
			}

			if( @$Options[ store_prefix() . 'nexo_enable_registers' ] == 'oui' ) {

				$Nexo_Menus[ 'caisse' ]        =    array(
					array(
						'title'		=> 	__('Caisses', 'nexo'), // menu title
						'icon'		=> 	'fa fa-inbox', // menu icon
						'disable'		=>    true,
						'permission'	=>	[ 
							'nexo.view.registers'
						]
					)
				);

				$Nexo_Menus[ 'caisse' ][]		=	array(
					'title'       	=>    __('Liste des caisses', 'nexo'), // menu title
					'icon'        	=>    'fa fa-shopping-basket', // menu icon
					'href'        	=>    dashboard_url([ 'registers' ]), // url to the page,
					'permission'	=>	'nexo.view.registers'
				);

				$Nexo_Menus[ 'caisse' ][]		=	array(
					'title'       	=>    __('Ajouter une caisse', 'nexo'), // menu title
					'icon'        	=>    'fa fa-shopping-basket', // menu icon
					'href'        	=>    dashboard_url([ 'registers', 'add' ]), // url to the page,,
					'permission'	=>	'nexo.create.registers'
				);
			}


			if( in_array( store_option( 'nexo_enable_registers' ), array( null, 'non' ) ) ){
				$Nexo_Menus[ 'caisse' ][]		=	array(
					'title'       =>    __('Ouvrir le PDV', 'nexo'), // menu title
					'icon'        =>    'fa fa-shopping-cart', // menu icon
					'href'        =>    dashboard_url([ 'pos' ]), // url to the page,
					'permission' 	=>	'nexo.create.orders'
				);
			}

			// @since 2.7.5

			$Nexo_Menus[ 'sales' ]			=	array(
				array(
					'title'       	=>    __('Ventes', 'nexo'), // menu title
					'icon'        	=>    'fa fa-shopping-basket', // menu icon
					'href'        	=>    dashboard_url([ 'orders' ]), // url to the page,
					'permission'	=>	'nexo.view.orders'
				)
			);

			// Coupon Features
			// @since 3.0.1
			$this->events->do_action('nexo_before_coupons', $Nexo_Menus);

			if( store_option( 'disable_coupon' ) != 'yes' ) {
				$Nexo_Menus[ 'coupons' ]    =    $this->events->apply_filters('nexo_coupons_menu_array',[
					array(
						'title'            =>    __('Coupons', 'nexo'),
						'icon'            =>    'fa fa-ticket',
						'disable'           =>  true,
						'permission'		=>	[
							'nexo.view.coupons'
						]
					),
					array(
						'title'            =>    __('Liste des coupons', 'nexo'),
						'href'            =>   	dashboard_url([ 'coupons' ]),
						'permission'		=>	'nexo.view.coupons'
					),
					[
						'title'            	=>    __('Ajouter un coupon', 'nexo'),
						'href'            	=>    dashboard_url([ 'coupons', 'add' ]),
						'permission'		=>	'nexo.create.coupons'
					]
				]);
			}

			$this->events->do_action('nexo_after_coupons', $Nexo_Menus);

			$this->events->do_action('nexo_before_shipping', $Nexo_Menus);

			$Nexo_Menus[ 'arrivages' ]    =    $this->events->apply_filters('nexo_shipping_menu_array', array(
				array(
					'title'        =>    __('Inventaire', 'nexo'),
					'href'        =>    '#',
					'disable'    =>    true,
					'icon'        =>    'fa fa-archive',
					'permission'	=>	[
						'nexo.view.items',
						'nexo.view.categories',
						'nexo.view.departments',
						'nexo.view.supplies',
						'nexo.view.taxes',
					]
				),
				array(
					'title'        =>    __('Approvisionnements', 'nexo'),
					'href'        =>    dashboard_url([ 'supplies' ])
				),
				array(
					'title'        =>    __('Nouvel Approvisionnement', 'nexo'),
                    'href'        =>    dashboard_url([ 'supplies', 'stock' ]),
                    'permission'    =>  'nexo.create.supplies',
				),
				// @since 3.0.20
				array(
					'title'		=>	__( 'Ajustement des quantités', 'nexo' ),
					'href'		=>		dashboard_url([ 'items', 'stock-adjustment' ] )
				),
				array(
					'title'        =>    __('Liste des articles', 'nexo'),
					'href'        =>    dashboard_url([ 'items' ]),
				),
				array(
					'title'        =>    __('Ajouter un article', 'nexo'),
					'href'        =>    dashboard_url([ 'items' ,'add' ]),
				),
				array(
					'title'        =>    __('Ajouter des produits groupés', 'nexo'),
					'href'        =>    dashboard_url([ 'grouped-items' ,'add' ]),
				),
				array(
					'title'         =>  __( 'Importer les articles', 'nexo' ),
					'href'          =>  dashboard_url([ 'items', 'import' ])
				),
				array(
					'title'        =>    __('Liste des taxes', 'nexo'),
					'href'		=>	dashboard_url([ 'taxes'])
				),
				array(
					'title'        =>    __('Ajouter une taxe', 'nexo'),
					'href'        =>    dashboard_url([ 'taxes', 'add' ])
				),
				array(
					'title'        =>    __('Liste des catégories', 'nexo'),
					'href'        =>    dashboard_url([ 'categories'])
				),
				array(
					'title'        =>    __('Ajouter une catégorie', 'nexo'),
					'href'        	=>    dashboard_url([ 'categories', 'add' ])
				)
			));

			$Nexo_Menus[ 'vendors' ]	=	array(
				array(
					'title'        =>    __('Fournisseurs', 'nexo'),
					'disable'        =>  true,
					'href'			=>	'#',
					'icon'			=>	'fa fa-truck',
					'permission'		=>	'nexo.view.providers'
				),
				array(
					'title'        =>    __('Liste des fournisseurs', 'nexo'),
					'href'        =>     dashboard_url([ 'providers' ]),
					'permission'	=>	'nexo.view.providers'
				),
				array(
					'title'        =>    __('Ajouter un fournisseur', 'nexo'),
					'href'        =>     dashboard_url([ 'providers', 'add' ]),
					'permission'	=>	'nexo.create.providers'
				),
			);

			$this->events->do_action('nexo_before_customers', $Nexo_Menus);

			$clientMenus 	=	array(
				array(
					'title'        =>    __('Clients', 'nexo'),
					'href'        =>    '#',
					'disable'    =>    true,
					'icon'        =>    'fa fa-users',
					'permission'	=>	[
						'nexo.view.customers',
						'nexo.view.customers-groups',
					]
				),
				array(
					'title'        =>    __('Liste des clients', 'nexo'),
					'href'        =>    dashboard_url([ 'customers' ]),
					'permission'	=>	'nexo.view.customers'
				),
				array(
					'title'        =>    __('Ajouter un client', 'nexo'),
					'href'        =>    dashboard_url([ 'customers', 'add']),
					'permission'	=>	'nexo.create.customers'
				),
				array(
					'title'        =>    __('Groupes', 'nexo'),
					'href'        =>    dashboard_url([ 'groups-customers' ]),
					'permission'	=>	'nexo.view.customers-groups'
				),
				array(
					'title'        =>    __('Ajouter un groupe', 'nexo'),
					'href'        =>    dashboard_url([ 'groups-customers', 'add' ]),
					'permission'	=>	'nexo.create.customers-groups'
				),
				array(
					'title'        =>    __('Importer des clients', 'nexo'),
					'href'        =>    dashboard_url([ 'import', 'customers' ]),
					'permission'	=>	'nexo.create.customers'
				)				
			);

			if ( store_option( 'nexo_enable_reward_system', 'no' ) === 'yes' ) {
				$clientMenus[] 		=	[
					'title'	=>	__( 'Système de récompense', 'nexo' ),
					'href'	=>	dashboard_url([ 'rewards-system' ])
				];

				$clientMenus[] 		=	[
					'title'	=>	__( 'Ajouter une récompense', 'nexo' ),
					'href'	=>	dashboard_url([ 'rewards-system', 'create' ])
				];
			}

			$Nexo_Menus[ 'clients' ]        =    $this->events->apply_filters('nexo_customers_menu_array', $clientMenus );

			$this->events->do_action('nexo_before_reports', $Nexo_Menus);

			// $Nexo_Menus[ 'rapports' ]    =    $this->events->apply_filters('nexo_reports_menu_array', array(
			// 	array(
			// 		'title'        =>    __('Rapports', 'nexo'),
			// 		'href'        =>    '#',
			// 		'disable'    =>    true,
			// 		'icon'        =>    'fa fa-bar-chart',
			// 		'permission'	=>	[
			// 			'nexo.read.detailed-report',
			// 			'nexo.read.best-sales',
			// 			'nexo.read.daily-sales',
			// 			'nexo.read.incomes-losses',
			// 			'nexo.read.expenses-listings',
			// 			'nexo.read.cash-flow',
			// 			'nexo.read.annual-sales',
			// 			'nexo.read.cashier-performances',
			// 			'nexo.read.customer-statistics',
			// 			'nexo.read.inventory-tracking',
			// 		]
			// 	)
			// ));

			$this->events->do_action('nexo_before_accounting', $Nexo_Menus);

			$this->events->do_action('nexo_before_history', $Nexo_Menus);

			$this->events->do_action('nexo_before_settings', $Nexo_Menus);

			$Nexo_Menus[ 'nexo_settings' ]    =    $this->events->apply_filters('nexo_settings_menu_array', array(
				array(
					'title'            =>    __( 'Réglages NexoPOS', 'nexo'),
					'icon'            =>    'fa fa-gear',
					'href'            =>    '#',
					'disable'        =>    true,
					'permission'		=>	'nexo.manage.settings'
				),
				array(
					'title'            =>    __('Général', 'nexo'),
					'icon'            =>    'fa fa-gear',
					'href'            =>    dashboard_url([ 'settings' ]),
					'permission'		=>	'nexo.manage.settings'
				),
				array(
					'title'            =>    __('Caisse', 'nexo'),
					'icon'            =>    'fa fa-gear',
					'href'            =>    dashboard_url([ 'settings', 'checkout' ]),
					'permission'		=>	'nexo.manage.settings'
				),
				array(
					'title'            =>    __('Articles', 'nexo'),
					'icon'            =>    'fa fa-gear',
					'href'            =>    dashboard_url([ 'settings', 'items' ]),
					'permission'		=>	'nexo.manage.settings'
				),
				[
					'title'	 		=>	__( 'Commandes', 'nexo' ),
					'href' 			=>	dashboard_url([ 'settings', 'orders' ]),
					'permission'		=>	'nexo.manage.settings'
				],
				[
					'title'	 		=>	__( 'Fournisseurs', 'nexo' ),
					'href' 			=>	dashboard_url([ 'settings', 'providers' ]),
					'permission'		=>	'nexo.manage.settings'
				],
				[
					'title'	 		=>	__( 'Rapports', 'nexo' ),
					'href' 			=>	dashboard_url([ 'settings', 'reports' ]),
					'permission'		=>	'nexo.manage.settings'
				],
				// [
				// 	'title'	 		=>	__( 'Dépenses', 'nexo' ),
				// 	'href' 			=>	dashboard_url([ 'settings', 'expenses' ]),
				// 	'permission'		=>	'nexo.manage.settings'
				// ],
				array(// @since 2.7.9
					'title'            =>    __('Factures & Reçus', 'nexo'),
					'icon'            =>    'fa fa-gear',
					'href'            =>    dashboard_url([ 'settings', 'invoices' ]),
					'permission'		=>	'nexo.manage.settings'
				),
				array(// @since 3.0.19
					'title'            =>    __('Raccourcis Claviers', 'nexo'),
					'icon'            =>    'fa fa-keyboard-o',
					'href'            =>    dashboard_url([ 'settings', 'keyboard' ]),
					'permission'		=>	'nexo.manage.settings'
				),
				array(
					'title'            	=>    __('Clients', 'nexo'),
					'icon'            	=>    'fa fa-gear',
					'href'            	=>    dashboard_url([ 'settings', 'customers' ]),
					'permission'		=>	'nexo.manage.settings'
				),
				array(
					'title'            	=>    __('Réinitialisation', 'nexo'),
					'icon'            	=>    'fa fa-gear',
					'href'            	=>    dashboard_url([ 'settings', 'reset' ]),
					'permission'		=>	'nexo.manage.settings'
				),
				array(
					'title'            	=>    __('Journal & Historique', 'nexo'),
					'icon'            	=>    'fa fa-gear',
					'href'            	=>    dashboard_url([ 'logs-history' ]),
					'permission'		=>	'nexo.manage.settings'
				),
				array(
					'title'				=>	__( 'A propos', 'nexo' ),
					'icon' 				=>	'fa fa-help',
					'href'				=>	dashboard_url([ 'about' ]),
					'permission'			=>	'nexo.manage.settings'
				)
			));
		}

		/**
		 * Store Settings
		 * @since 2.8
		**/

		if( get_option( 'nexo_store', 'disabled' ) == 'enabled' ) {

			$Nexo_Menus[ 'nexo_store_settings' ]	=	array(
				array(
					'title'			=>	__( 'Réglages des boutiques', 'nexo' ),
					'href'			=>	dashboard_url([ 'settings', 'stores' ]),
					'icon'			=>	'fa fa-wrench',
					'permission' 		=>	'nexo.manage.settings'
				)
			);

		} else { // in order to simplify Setting menu, we remove Store setting from admin menu add set it as Nexo Settings Sub menu

			$Nexo_Menus[ 'nexo_settings' ][]	=	array(
				'title'			=>	__( 'Réglages des boutiques', 'nexo' ),
				'href'			=>	dashboard_url([ 'settings', 'stores' ]),
				'icon'			=>	'fa fa-wrench',
				'permission'		=>	[
					'nexo.manage.settings',
					'nexo.view.stores'
				]
			);

		}

		$start    	=    array_slice($final, 0, 1);
		$end		=    array_slice($final, 1);
		$final    	=    array_merge($start, $Nexo_Menus, $end);

		/**
		 * Hide Main Site Menus
		 * @since 2.8.0
		**/

		if( $uri === 'stores' ) {

			foreach( $final as $key => $menu ) {
				if( ! in_array( $key, array( 'activite', 'rapports', 'clients', 'vendors', 'arrivages', 'factures', 'nexo_settings', 'sales', 'caisse', 'coupons' ) ) ) {
					unset( $final[ $key ] );
				}
			}

			// Create a dashboard menu for Sub shop
			// @since 2.8.0

			if( $this->uri->segment( 2 ) == 'stores' ){

				$final		=	array_insert_before( 'caisse', $final, 'store-dashboard', array(
					array(
						'title'		=>	__( 'Tableau de bord', 'nexo' ),
						'href'		=>	site_url( array( 'dashboard', 'stores', $this->uri->segment( 3 ) ) ),
						'icon'		=>	'fa fa-dashboard'
					)
				) );

				@$final[ 'nexo_settings' ][0]	=	array(
					'title'			=>	__( 'Réglages de la boutique', 'nexo' ),
					'disable'		=>	true,
					'icon'			=>	'fa fa-cogs',
					'href'			=>	'javascript:void()',
					'permission'	=>	'nexo.manage.settings'
				);
			}
		}

		return $final;
    }

    public function load_dashboard()
    {
		// @since 3.0.16
		$store_menus    =   get_instance()->events->apply_filters( 'nexo_store_menus', $this->load->module_view( 'nexo', 'header/store-menus', null, true ) );

		$this->events->add_action( 'display_admin_header_menu', function( $action ) use ( $store_menus ) {
            echo $store_menus;
		});
    }
}
new Nexo_Controller;
