<?php
/**
 * Create a log model to save what is made by users 
 * within the applications
 */
class NexoLogModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Post Activity
     * @param string title
     * @param string description
     * @return json
     */
    public function log( $title, $description )
    {
        if ( store_option( 'nexo_premium_enable_history', 'no' ) === 'yes' ) {
            $this->db->insert( store_prefix() . 'nexo_historique',  [
                'TITRE'             =>  $title,
                'DETAILS'           =>  $description,
                'DATE_CREATION'     =>  date_now()
            ]);
    
            $insert_id          =   $this->db->insert_id();
        }

        return [
            'status'    =>  'success',
            'message'   =>  __( 'L\'historique a correctement été crée', 'nexo' )
        ];
    }
}