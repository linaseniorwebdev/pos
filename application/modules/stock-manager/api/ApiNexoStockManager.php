<?php
class ApiNexoStockManager extends Tendoo_Api
{
    private $TransfertModel;
    public function __construct()
    {
        parent::__construct();
        $this->load->module_model( 'stock-manager', 'Transfert_model' );
        $this->TransfertModel   =   get_instance()->Transfert_model;
    }

    /**
     * Allow Transfert from Stock Manager
     * @return void
     */
    public function approve_transfert()
    {
        return $this->response( $this->TransfertModel->approve_transfert([
            'transfert_id'  =>  $this->post( 'transfert_id' ),
            'description'   =>  $this->post( 'description' )
        ] ) );
    }

    /**
     * Cancel the transfert
     * @return json
     */
    public function cancel_transfert()
    {
        return $this->response( $this->TransfertModel->cancel_transfert([
            'transfert_id'  =>  $this->post( 'transfert_id' ),
            'description'   =>  $this->post( 'description' )
        ] ) );
    }

    /**
     * Cancel the transfert
     * @return json
     */
    public function reject_transfert()
    {
        return $this->response( $this->TransfertModel->reject_transfert([
            'transfert_id'  =>  $this->post( 'transfert_id' ),
            'description'   =>  $this->post( 'description' )
        ] ) );
    }

    /**
     * Approuve a stock request
     * @return json
     */
    public function approve_request()
    {
        return $this->response([
            'status'    =>  'success',
            'message'   =>  __( 'The stock request has been approuved' )
        ]);
    }

    /**
     * Verification of a stock request
     * @return json
     */
    public function verification()
    {
        return $this->response( $this->TransfertModel->verify([
            'transfert_id'  =>  $this->post( 'transfert_id' ),
            'description'   =>  $this->post( 'description' )
        ] ) );
    }

    /**
     * Transfert a stock to the requested store
     * @return void
     */
    public function proceedStockRequest()
    {
        return $this->response( $this->TransfertModel->proceessStockRequest([
            'transfert_id'      =>  $this->post( 'transfert_id' ),
            'description'       =>  ''
        ] ) );
    }
}