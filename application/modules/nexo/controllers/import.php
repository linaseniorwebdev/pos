<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use League\Csv\Reader;

class NexoImportController extends CI_Model{

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    /**
     *  Item
     *  @param
     *  @return
    **/

    public function items()
    {
        $this->load->model( 'Nexo_Shipping' );
        $deliveries     =   $this->Nexo_Shipping->get();
        $providers      =   $this->Nexo_Shipping->get_providers();

        $this->events->add_action( 'dashboard_footer', function(){
            get_instance()->load->module_view( 'nexo', 'import/script' );
        });

        $this->Gui->set_title( store_title( __( 'Importer des articles depuis un CSV', 'nexo' ) ) );
        $this->load->module_view( 'nexo', 'import/items', compact( 'deliveries', 'providers' ) );
    }

}
