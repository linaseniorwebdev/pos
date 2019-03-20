<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NexoAdvancedStoreManagerApp extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
        $this->events->add_filter( 'admin_menus', array( $this, 'menus' ), 20 );
        $this->events->add_filter( 'stores_controller_callback', array( $this, 'multistore' ) );
        $this->events->add_action( 'after_main_store_card', array( $this, 'before_rows' ) );
        $this->events->add_action( 'load_dashboard', [ $this, 'load_dashboard' ], 99 );
        $this->events->add_filter( 'stores_list_menu', [ $this, 'filter_store_menus' ]);
        $this->events->add_action( 'before_init_store', [ $this, 'before_init_store' ], 10, 1 );
    }

    /**
     * Catch if the current user can access to the store
     * @return void
     */
    public function before_init_store( $details )
    {
        if ( ! User::in_group( 'master' ) && get_option( 'store_access_' . User::id() . '_' . $details[ 'store_id' ] ) !== 'yes' ) {
            return show_error( __( 'You\'re not allowed to access to that store. Contact the store manage for more informations.', 'nsam' ) );
        }
    }

    /**
     *  Load Dashboard
     *  @param void
     *  @return void
    **/

    public function load_dashboard()
    {
        // if( User::can( 'manage_core' ) ) {
        //     $this->Gui->register_page_object( 'nsam', new NsamController );
        // }

        $this->lang->load_lines(dirname(__FILE__) . '/language/lang.php');

        if( ! multistore_enabled() ) {
            nexo_notices([
                'user_id'   =>  User::id(),
                'message'   =>  __( 'Nexo Store Advanced Manager, is not active, since NexoPOS multi Store feature is not active.', 'nsam' ),
                'link'      =>  site_url( array( 'dashboard', 'nexo', 'settings', 'stores' ) ),
                'icon'      =>  'fa fa-info'
            ]);
        }

        global $Options;

        if( is_multistore() && User::in_group([ 'shop.cashier', 'shop.manager', 'shop.demo']  ) ) {
            $store_id   =   get_store_id();
            if( @$Options[ 'store_access_' . User::id() . '_' . $store_id ] != 'yes' ) {
                redirect([ 'dashboard', 'not-allowed-to-access-to-that-store' ]);
            }
        }
    }

    /**
     * Filter Dashboard Menu
     * @param array
     * @return array
    **/

    public function filter_store_menus( $stores ) 
    {
        foreach( $stores as $key => $store ) {
            if( get_option( 'store_access_' . User::id() . '_' . $store[ 'ID' ] ) != 'yes' && ! User::in_group( 'master' ) ) {
                unset( $stores[ $key ] );
            }
        }

        if( count( $stores ) == 0 ) {
            $stores[]    =   [
                'NAME'  =>  __( 'Access Denied', 'nsam' ),
                'ID'    =>  false,
                'STATUS'    =>  'opened',
                'IMAGE'     =>  '',
                'DESCRIPTION'   =>      __( 'You don\'t have access to any store.', 'nsam' )
            ];
        }

        return $stores;
    }

    /**
     *  Admin menu
     *  @param array
     *  @return array
    **/

    public function menus( $menus )
    {
        if( multistore_enabled() && is_multistore() && User::in_group([ 'master', 'shop.manager' ] ) ) {
            $menus[ 'nexo_settings' ][]   =   array(
                'href'      =>  dashboard_url([ 'content-manager' ]),
                'title'     =>  __( 'Content Copy', 'nsam' ),
            );

            $menus[ 'nsam_users' ]      =   [
                array(
                    'href'      =>  site_url( array( 'dashboard', store_slug(), 'nexo', 'users' ) ),
                    'title'     =>  __( 'Users List', 'nsam' ),
                    'icon'      =>  'fa fa-users',
                    'disable'   =>  true
                ),
                array(
                    'href'      =>  site_url( array( 'dashboard', store_slug(), 'nexo', 'users' ) ),
                    'title'     =>  __( 'All users', 'nsam' )
                ),
                array(
                    'href'      =>  site_url( array( 'dashboard', store_slug(), 'nexo', 'users', 'add' ) ),
                    'title'     =>  __( 'New User', 'nsam' )
                )
            ];
        } else {
            if( multistore_enabled() && User::can( 'manage_core' ) ) {

                // $menus[ 'nexo_store_settings' ][]   =   array(
                //     'href'      =>  site_url( array( 'dashboard', 'nsam', 'module_control' ) ),
                //     'title'     =>  __( 'Module Manager', 'nsam' )
                // );

                $menus[ 'nexo_store_settings' ][]   =   array(
                    'href'      =>  site_url( array( 'dashboard', 'nexo', 'settings', 'access-manager' ) ),
                    'title'     =>  __( 'Access Manager', 'nsam' )
                );

                // $menus          =   array_insert_after( 'nexo_shop', $menus, 'nexo_package', [
                //     array(
                //         'title' =>  __( 'Subscriptions', 'nsam' ),
                //         'href'  =>  site_url( array( 'dashboard', 'nsam', 'subscriptions' ) ),
                //         'icon'  =>  'fa fa-calendar'
                //     ),
                //     array(
                //         'title' =>  __( 'Add new', 'nsam' ),
                //         'href'  =>  site_url( array( 'dashboard', 'nsam', 'subscriptions', 'add_new' ) ),
                //     )
                // ]);
            }
        }
        return $menus;
    }

    /**
     *  Multistore controller
     *  @param array controllers
     *  @return array
    **/

    public function multistore( $array )
    {
        $array[ 'nsam' ]  =   new NsamController;
        return $array;
    }

    /**
     *  Before Row
    *  @return void
    **/

    public function before_rows( $content )
    {
        // $this->load->module_view( 'nsam', 'main-store-widget' );
    }

}
new NexoAdvancedStoreManagerApp;
