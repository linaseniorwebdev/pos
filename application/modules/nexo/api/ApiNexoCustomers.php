<?php
use Carbon\Carbon;
class ApiNexoCustomers extends Tendoo_Api
{
    public function importCSV()
    {
        $customers     =   [];

        if ( $this->post( 'empty' ) ) {
            $this->db->from( store_prefix() . 'nexo_clients' )
                ->truncate();
        }

        foreach( $this->post( 'csv' ) as $row ) {
            if ( ! empty( $row ) ) {
                $singleLine     =   [];
                foreach( $this->post( 'model' ) as $index => $model ) {
                    if ( ! empty( $model ) && count( $row ) > 1 ) {
                        // var_dump( $model, $index, @$row[ $index ] );
                        $singleLine[ $model ]   =   $row[ $index ];
                    }
                }
                
                if( ! empty( $singleLine ) ) {
                    $singleLine[ 'DATE_CREATION' ]  =   date_now();
                    $singleLine[ 'DATE_MOD' ]       =   date_now();
                    $customers[]    =   $singleLine;
                }
            }
        }

        $this->db->insert_batch( store_prefix() . 'nexo_clients', $customers );

        return $this->response([
            'status'    =>  'success',
            'message'   =>  __( 'Les clients ont été correctement importées' )
        ]);
    }
}