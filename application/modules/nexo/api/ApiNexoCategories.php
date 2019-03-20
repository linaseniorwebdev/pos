<?php
class ApiNexoCategories extends Tendoo_Api
{
    /**
     * get all categories
     * @return void
     */
    public function categories( $id = 0 ) 
    {
        $this->load->module_model( 'nexo', 'NexoCategories', 'cat_model' );

        if ( ! empty( $id ) ) {
            return $this->response( $this->cat_model->getSingle( $id ) );
        }
        
        return $this->response( $this->cat_model->get() );
    }
}