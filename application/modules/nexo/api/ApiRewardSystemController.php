<?php
class ApiRewardSystemController extends Tendoo_Api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->module_model( 'nexo', 'NexoRewardSystemModel', 'nexo_reward_model' );
    }

    /**
     * Post a new reward system
     * @return AsyncResponse
     */
    public function postReward()    
    {        
        $request_body = file_get_contents('php://input');
        $data = json_decode( $request_body, true );

        $this->nexo_reward_model->create( $data );

        return $this->response([
            'status'    =>  'success',
            'message'   =>  __( 'La récompense a été enregistrée', 'nexo' )
        ]);
    }

    /**
     * Edit Reward using the provided id
     * @param int reward id
     * @return AsyncResponse
     */
    public function editReward( $id )
    {
        $request_body = file_get_contents('php://input');
        $data = json_decode( $request_body, true );

        $this->nexo_reward_model->update( $id, $data );

        return $this->response([
            'status'    =>  'success',
            'message'   =>  __( 'La récompense a été sauvegardée', 'nexo' )
        ]);
    }

    /**
     * delete a reward
     * @param int reward id
     * @return AsyncResponse
     */
    public function deleteReward( $id )
    {
        $this->nexo_reward_model->delete( $id );

        return $this->response([
            'status'    =>  'success',
            'message'   =>  __( 'La récompense a été supprimée', 'nexo' )
        ]);
    }

    /**
     * Delete single reward rule
     * @param int rule id
     * @param AsyncResponse
     */
    public function deleteRule( $id )
    {
        $this->nexo_reward_model->deleteRules( $id );
        
        return $this->response([
            'statuts'   =>  'success',
            'message'   =>  __( 'La règle de la récompense a été supprimée', 'nexo' )
        ]);
    }

    /**
     * get paginated rewards
     * @param int page id
     * @return array
     */
    public function getPaginated( $page = 1 )
    {
        $this->load->library( 'pagination' );
        
        $config                     =   [];
        $config[ 'base_url' ]       =   site_url([ 'api', 'nexopos', 'rewards-system' ]);
        $config[ 'per_page' ]       =   10;
        $config[ 'current_page' ]   =   intval( $page );
        $config[ 'total_rows' ]     =   $this->nexo_reward_model->count_entries();
        $config[ 'total_pages' ]    =   ceil( $config[ 'total_rows' ] / $config[ 'per_page' ] );
        $config[ 'entries' ]        =   $this->nexo_reward_model->getEntries( $config[ 'per_page' ], $page );

        return $this->response( $config );
    }

    /**
     * Bulk Delete a reward
     * @param void
     * @return AsyncResponse
     */
    public function bulkDelete()
    {
        if ( $this->post( 'ids' ) ) {
            foreach( $this->post( 'ids' ) as $id ) {
                $this->nexo_reward_model->delete( $id );
            }

            return $this->response([
                'status'    =>  'success',
                'message'   =>  __( 'Les éléments selectionnées ont été supprimé', 'nexo' )
            ]);
        }

        return $this->response([
            'status'    =>  'failed',
            'message'   =>  __( 'Impossible de continuer ! Une valeur importante est manquante', 'nexo' )
        ], 403 );
    }
}