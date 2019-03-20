<?php
class NexoTemplateController extends Tendoo_Module
{
    /**
     * Load Template for Customers
     * @param string tab
     * @return string
    **/
    
    public function customers_form()
    {
        return $this->load->module_view( 'nexo', 'customers.form-template', null, true );
    }

    public function customers_main()
    {
        return $this->load->module_view( 'nexo', 'customers.main-template', null, true );
    }

    public function load( $view )
    {
        return $this->load->module_view( 'nexo', 'templates/' . $view, null, true );
    }

    /**
     * Shippings
     * @return string view
    **/

    public function shippings( $template = 'main-template' )
    {
        return $this->load->module_view( 'nexo', 'shippings.' . $template, null, true );
    }

    /**
     * Orders Templates
     * @return string view
     */
    public function orders( $namespace )
    {
        return $this->load->module_view( 'nexo', 'templates.orders.' . $namespace, null, true );
    }
}