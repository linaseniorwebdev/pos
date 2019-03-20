<?php

class NexoEmailModel extends Tendoo_Module
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Send email through Nexo Platform
     * @param string to
     * @param string from
     * @param string message
     */
    public function send( $data )
    {
        $NexoPlatformUrl    =   $this->config->item( 'nexo_platform_url' );

        try {
            
            $apiRequest             =    Requests::post( $this->config->item( 'nexo_platform_url' ) . 'api/nexopos/email/stock-transfert', [
                'X-API-TOKEN'       =>  get_option( 'nexopos_store_access_key' ),
                'X-Requested-With'  =>  'XMLHttpRequest'
            ], $data );

            return json_decode( $apiRequest->body, true );

        } catch( Request_Exception $exception ) {
            return [
                'status'    =>  'failed',
                'message'   =>  $exception->message()
            ];
        }

    }
}