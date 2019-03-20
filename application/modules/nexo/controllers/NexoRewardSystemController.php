<?php
class NexoRewardSystemController extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    
    /**
     * display a list of available 
     * rewards system
     * @return void
    **/
    public function listRewards()
    {
        $this->events->add_action( 'dashboard_footer', function() {
            get_instance()->load->module_view( 'nexo', 'rewards.list.script' );
        });
        
        $this->Gui->set_title( store_title( __( 'Liste des récompenses', 'nexo' ) ) );
        $this->load->module_view( 'nexo', 'rewards.list.gui' );
    }

    /**
     * display a ui to create a reward system
     * @return void
    **/
    public function create()
    {
        $this->events->add_action( 'dashboard_footer', function() {
            get_instance()->load->module_view( 'nexo', 'rewards.create.script' );
        });

        $this->load->module_model( 'nexo', 'NexoCouponsModel', 'coupon_model' );
        
        $coupons    =   $this->coupon_model->get();
        
        $this->Gui->set_title( store_title( __( 'Créer une récompense', 'nexo' ) ) );
        $this->load->module_view( 'nexo', 'rewards.create.gui', compact( 'coupons' ) );
    }

    /**
     * display a ui to edit an existing
     * reward system
     * @param int reward id
     * @return void
    **/
    public function edit( $id )
    {
        $this->load->module_model( 'nexo', 'NexoRewardSystemModel', 'reward_model' );
        
        $reward     =   $this->reward_model->getSingle( $id );

        $this->events->add_action( 'dashboard_footer', function() use ( $reward ) {
            get_instance()->load->module_view( 'nexo', 'rewards.create.script', compact( 'reward' ) );
        });

        $this->load->module_model( 'nexo', 'NexoCouponsModel', 'coupon_model' );
        
        $coupons    =   $this->coupon_model->get();
        
        $this->Gui->set_title( store_title( __( 'Modifier une récompense', 'nexo' ) ) );
        $this->load->module_view( 'nexo', 'rewards.create.gui', compact( 'coupons' ) );
    }

    /**
     * Delete a specific reward system
     * @param int reward id
     * @return void
    **/
    public function delete()
    {
        
    }
}