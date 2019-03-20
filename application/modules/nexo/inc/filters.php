<?php
class Nexo_Filters extends Tendoo_Module
{

    /**
     * Default JS Libraries
     * @param array
     * @return array
    **/

    function default_js_libraries($libraries) 
    {

        foreach ($libraries as $key => $lib) {
            if (in_array($lib, array( '../plugins/jQueryUI/jquery-ui-1.10.3.min' ))) { // '../plugins/jQuery/jQuery-2.1.4.min',
                unset($libraries[ $key ]);
            }
        }

        return $libraries;

    }

    /**
     * Add link to premium version
    **/

    public function remove_link($link)
    {
        return 'http://codecanyon.net/item/nexopos-web-application-for-retail/16195010';
    }

    /**
	 * POS Note Button
	**/
	public function nexo_cart_buttons( $data )
	{
		$data[ 'order_note' ]     =   '<button class="btn btn-default" type="button" alt=" ' . __( 'Note', 'nexo' ) . '" data-set-note> ' . sprintf( __( '%s', 'nexo' ), '<i class="fa fa-pencil"></i> <span class="hidden-sm hidden-xs">' . __( 'Note', 'nexo' ) . '</span>' ) . '</button>';

		return $data;
	}

    /**
     * Login Redirection
    **/

    public function login_redirection( $redirection ) 
    {
        if( User::in_group( 'store.cashier' ) || User::in_group( 'store.demo' ) ) {
            if( multistore_enabled() ) {
                return site_url( array( 'dashboard', 'nexo', 'stores', 'all' ) );
            }
            return site_url( array( 'dashboard', 'nexo', 'pos' ) );
        }
        return $redirection;
    }

    /**
     * Dashboard Dependencies
     * @param array
     * @return array
    **/

    public function dashboard_dependencies( $deps )
    {
        $deps[]     =   'ngNumeraljs';
        $deps[]     =   'ui.bootstrap.datetimepicker';
        $deps[]     =   'cfp.hotkeys';
        $deps[]     =   'schemaForm';
        $deps[]     =   'ngSanitize';
        $deps[]     =   'ng-pros.directive.autocomplete';
        $deps[]     =   'ngFileUpload';
        return $deps;
    }

    /**
     * Sign In Logo
     * @param string
     * @return string
    **/

    public function signin_logo( $string )
    {
        global $Options;

        if( @$Options[ store_prefix() . 'nexo_logo_type' ] == 'text' ) {
            return @$Options[ store_prefix() . 'nexo_logo_text' ];
        } else if( @$Options[ store_prefix() . 'nexo_logo_type' ] == 'image_url' ) {
            return '<img style="' . ( ! in_array( @$Options[ store_prefix() . 'nexo_logo_width' ], array( null, '' ) ) ? 'width:' . $Options[ store_prefix() . 'nexo_logo_width' ] . 'px;' : '' ) . ( ! in_array( @$Options[ store_prefix() . 'nexo_logo_height' ], array( null, '' ) ) ? 'height:' . $Options[ store_prefix() . 'nexo_logo_height' ] . 'px;' : '' ) . '" src="' . @$Options[ store_prefix() . 'nexo_logo_url' ] . '" alt="' . @$Options[ store_prefix() . 'nexo_logo_text' ] . '"/>';
        }
        return $string;
    }

    /**
     * Dashboard Footer right
     * Display some text on dashboard footer
     * @param string
     * @return string
    **/

    public function dashboard_footer_right( $text ) 
    {
        global $Options;
        if( ! is_multistore() ) {
            return xss_clean( @$Options[ 'nexo_footer_text' ] );
        } else if( store_option( 'nexo_footer_text', null ) != null ) {
            return store_option( 'nexo_footer_text', $text );
        }
        return $text;
    }

    /**
     * Dashboard Long Logo
     * @return string
    **/

    public function dashboard_logo_long( $text )
    {
        if( ! is_multistore() ) {
            if( ! in_array( store_option( 'nexo_logo_text', 'default' ), array( 'default', null, 'disable' ) ) ){
                return store_option( 'nexo_logo_text', 'default' ) ?: $text;
            }
        } else if( store_option( 'nexo_logo_text', null ) != null ) {
            return store_option( 'nexo_logo_text', $text );
        }
        return $text;
    }

    /**
     * Dashboard Logo Small
     * @return string
    **/

    public function dashboard_logo_small( $text )
    {
        if( ! is_multistore() ) {
            if( ! in_array( store_option( 'nexo_logo_type', 'default' ), array( 'default', null, 'disable' ) ) ){
                return '<img src="' . store_option( 'nexo_logo_url', base_url() . '/public/img/logo_minim.png' ) . '" alt="logo" style="width:50px;"/>';
            }
        } else if( store_option( 'nexo_logo_url', null ) != null ) {
            return '<img src="' . store_option( 'nexo_logo_url', base_url() . '/public/img/logo_minim.png' ) . '" alt="dashboard-logo" style="width: 50px"/>';
        }
        return $text;
    }

    /**
     * Dashoard Footer Text
     * @return string
    **/

    public function dashboard_footer_text( $text )
    {
        global $Options;
        if( ! is_multistore() ) {
            if( ! in_array( @$Options[ 'nexo_logo_type' ], array( 'default', null ) ) ){
                return @$Options[ 'nexo_logo_text' ];
            }
        } else if( store_option( 'nexo_logo_text', null ) != null ) {
            return store_option( 'nexo_logo_text', $text );
        }
        return $text;
    }

    /**
     * Store Menu to add a new notification center
    **/

    public function store_menus( $text )
    {
        return $this->load->module_view( 'nexo', 'header.notification-menus', null, true ) . $text;
    }

    /**
     * Awesome Filter
     * @param array
     * @return array;
    **/

    public function ac_filter_get_request( $data )
    {
        if( $data[ 'table' ] == 'nexo_taxes' ) {
            $data[ 'object' ]->db->select( 'aauth_users.name as AUTHOR' );
            $data[ 'object' ]->db->join( 'aauth_users', 'aauth_users.id = ' . store_prefix() . 'nexo_taxes.AUTHOR' );
            $data[ 'primaryKey' ]   =   'ID';
        }
        return $data;
    }

    /**
     * Primary Key
    **/

    public function ac_delete_entry( $data )
    {
        $data[ 'primaryKey' ]   =   'ID';
        return $data;
    }

    /**
     * Post Order Details
     * @param array order details
     * @return order details
     */
    public function post_order_details( $details, $data )
    {
        $details[ 'STATUS' ]        =   'pending';
        if( $details[ 'TYPE' ] === 'nexo_order_comptant' ) {
            $details[ 'STATUS' ]        =   'completed';
            if( floatval( $data[ 'shipping' ][ 'price' ] ) > 0 ) {
                $details[ 'STATUS' ]    =   'pending';
            }
        }
        return $details;
    }

    /**
     * Post Order Details
     * @param array order details
     * @return order details
     * @deprecated
     */
    public function put_order_details( $details, $data )
    {
        $details[ 'STATUS' ]        =   'pending';
        if ( $details[ 'TYPE' ] === 'nexo_order_comptant' ) {
            $details[ 'STATUS' ]        =   'completed';
            if( floatval( $data[ 'shipping' ][ 'price' ] ) > 0 ) {
                $details[ 'STATUS' ]    =   'pending';
            }
        }
        return $details;
    }
}
