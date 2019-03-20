<?php
trait Stock_Transfert_Trait
{
    
    /**
     * Stock Transfert
     * @return json response
    **/

    public function stock_transfert_post()
    {
        $this->load->module_model( 'stock-manager', 'Transfert_model' );
        
        $response           =   $this->Transfert_model->transfert([
            'title'         =>  $this->post( 'title' ),
            'store'         =>  $this->post( 'store' ),
            'items'         =>  $this->post( 'items' ),
            'is_request'    =>  $this->post( 'is_request' )
        ]);

        $this->response( $response, 200 );    
    }
}