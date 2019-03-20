<?php

class Nexo_Sms extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->events->add_action( 'load_pos_footer', array( $this, 'footer' ));

        // Extends Nexo Settings pages
        $this->events->add_filter('nexo_settings_menu_array', array( $this, 'sms_settings' ));

        // Create Dashboard
        $this->events->add_action('load_dashboard', array( $this, 'load_dashboard' ));

        //
        $this->events->add_action('tendoo_settings_tables', array( $this, 'install' ));
    }

    /**
     * Load Dashboard
    **/

    public function load_dashboard()
    {
        // Load Languages Lines
        $this->lang->load_lines(dirname(__FILE__) . '/language/lines.php');

        // Load Config
        $this->load->module_config('nexo_sms');
    }

    /**
     * Footer
     * Load Javascript on Dashboard footer
    **/

    public function footer()
    {
        $this->load->module_view('nexo_sms', 'script');
    }

    /**
     * SMS settings
    **/

    public function sms_settings($array)
    {
		if ( ! User::in_group( 'master' ) && ! User::in_group( 'store.manager' ) ) {
            return $array;
        }

		global $Options;
		// @since 2.8
		// Adjust menu when multistore is enabled
		$uri			=	$this->uri->segment(2,false);
		$store_uri		=	'';

		if( $uri == 'stores' || in_array( @$Options[ store_prefix() . 'nexo_store' ], array( null, 'disabled' ), true ) ) {

			// Only When Multi Store is enabled
			// @since 2.8

			if( @$Options[ store_prefix() . 'nexo_store' ] == 'enabled' ) {
				$store_uri	=	'stores/' . $this->uri->segment( 3, 0 ) . '/';
			}
		}

        $array    		=	array_insert_after(2, $array, count($array), array(
            'title'     =>	__('SMS', 'nexo'),
            'icon'      =>	'fa fa-gear',
            'href'      =>	site_url(array( 'dashboard', store_slug(), 'nexo', $store_uri . 'settings', 'sms' ))
        ));

        return $array;
    }

    /**
     * Install
    **/

    public function install()
    {
        Modules::enable('nexo_sms');
        // Load Languages Lines
        $this->lang->load_lines(dirname(__FILE__) . '/language/lines.php');

        $this->load->module_config( 'nexo_sms', 'nexo_sms');
        // Set default SMS invoice
        $this->options->set('nexo_sms_invoice_template', $this->config->item('default_sms_invoice_template'));
    }
}
new Nexo_Sms;
