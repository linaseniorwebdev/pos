<?php
use Carbon\Carbon;
class NexoRegistersReport extends Tendoo_Module
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Activity report UI
     */
    public function activityReport()
    {
        $this->load->model( 'Nexo_Checkout' );
        $data       =   [];
        $data[ 'registers' ]    =   $this->Nexo_Checkout->get_registers();
        $data[ 'users'  ]       =   $this->auth->list_users( 'store.cashier' );
        $this->Gui->set_title( store_title( __( 'Historique des sessions', 'nexo_premium' ) ) );
        $this->load->module_view( 'nexo_premium', 'registers.gui', $data );
    }
}
 