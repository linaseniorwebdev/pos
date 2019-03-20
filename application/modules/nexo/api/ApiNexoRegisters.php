<?php
use Carbon\Carbon;
class ApiNexoRegisters extends Tendoo_Api
{
    /**
     * List all available registers
     * @return json of registers
     */
    public function getAll()
    {
        $registers  =   $this->db->get( store_prefix() . 'nexo_registers' )
        ->result_array();
        return $this->response( $registers );
    }

    /**
     * Idle Register
     * @return json
     */
    public function idleRegister( $id )
    {
        $this->load->model( 'Nexo_Checkout' );
        $register   =   $this->Nexo_Checkout->get_register( $id );
        
        if ( $register ) {
            $this->Nexo_Checkout->set_idle( 'idle_starts', $id );
            return $this->response([
                'status'    =>  'success',
                'message'   =>  __( 'La session a été arrêtée !', 'nexo' )
            ]);
        }

        return $this->response([
            'status'    =>  'failed',
            'message'   =>  __( 'Impossible d\'identifier la caisse enregistreuse', 'nexo' )
        ], 404 );
    }

    /**
     * Idle Register
     * @return json
     */
    public function activeRegister( $id )
    {
        $this->load->model( 'Nexo_Checkout' );
        $register   =   $this->Nexo_Checkout->get_register( $id );
        
        if ( $register ) {
            $this->Nexo_Checkout->set_idle( 'idle_ends', $id );
            return $this->response([
                'status'    =>  'success',
                'message'   =>  __( 'La session a été relancée !', 'nexo' )
            ]);
        }

        return $this->response([
            'status'    =>  'failed',
            'message'   =>  __( 'Impossible d\'identifier la caisse enregistreuse', 'nexo' )
        ], 404 );
    }
}