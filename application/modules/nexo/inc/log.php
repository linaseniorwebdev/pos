<?php
class NexoLogWrapper extends Tendoo_Module
{
    public function __construct()
    {
        parent::__construct();
        $this->load->module_model( 'nexo', 'NexoLogModel' );
    }

    /**
     * Catch when an order is being deleted
     * and report it
     */
    public function delete_order( $order_id )
    {
        $this->load->model( 'Nexo_Checkout' );
        $order  =   $this->Nexo_Checkout->get_order( $order_id );

        /**
         * make sure an order exist before 
         * reporting that it has been deleted
         */
        if ( $order ) {
            $this->NexoLogModel->log(
                __( 'Commande Supprimée', 'nexo' ),
                sprintf( 
                    __( 'La commande <strong>%s</strong> dont le total est <strong>%s</strong> a été supprimée par l\'utilisateur <strong>%s</strong>', 'nexo' ),
                    $order[0][ 'CODE' ],
                    $order[0][ 'TOTAL' ],
                    User::pseudo()
                )
            );
        }
        return $order_id;
    }

    /**
     * Report when a user delete a product
     * @param product
     * @return void
     */
    public function delete_products( $product )
    {
        $this->NexoLogModel->log(
            __( 'Suppression De Produit', 'nexo' ),
            sprintf( 
                __( 'Le produit <strong>%s</strong> a été supprimée par l\'utilisateur <strong>%s</strong>', 'nexo' ),
                $product[0][ 'DESIGN' ],
                User::pseudo()
            )
        );
        return $product;
    }
    
    /**
     * Notify when the stock is updated
     * @param product
     * @return void
     */
    public function update_stock( $product )
    {
        $this->NexoLogModel->log(
            __( 'Mise à jour du stock', 'nexo' ),
            sprintf( 
                __( 'Le produit <strong>%s</strong> a été mis à jour par <strong>%s</strong>.', 'nexo' ),
                $product[ 'DESIGN' ],
                User::pseudo()
            )
        );
        return $product;
    }

    /**
     * Notify when a grouped products is craeted
     * @param product
     * @return void
     */
    public function create_grouped_products( $product )
    {
        $this->NexoLogModel->log(
            __( 'Creation d\'un produit groupé', 'nexo' ),
            sprintf( 
                __( 'Le produit groupé <strong>%s</strong> a été crée par <strong>%s</strong>.', 'nexo' ),
                $product[ 'DESIGN' ],
                User::pseudo()
            )
        );
        return $product;
    }

    /**
     * Notify when a single product has been updated
     * @param product
     * @return void
     */
    public function nexo_update_product( $product )
    {
        $this->NexoLogModel->log(
            __( 'Mise à jour du produit', 'nexo' ),
            sprintf( 
                __( 'Le produit <strong>%s</strong> a été mis à jour par <strong>%s</strong>.', 'nexo' ),
                $product[ 'DESIGN' ],
                User::pseudo()
            )
        );
        return $product;
    }
    
    /**
     * Notify when a single product has been created
     * @param product
     * @return void
     */
    public function nexo_save_product( $product )
    {
        $this->NexoLogModel->log(
            __( 'Creation d\'un produit', 'nexo' ),
            sprintf( 
                __( 'Le produit <strong>%s</strong> a été crée par <strong>%s</strong>.', 'nexo' ),
                $product[ 'DESIGN' ],
                User::pseudo()
            )
        );
        return $product;
    }

    /**
     * Notify when a single product has been updated
     * @param array data
     * @return array data
     */
    public function after_submit_order( $data )
    {
        extract( $data );
        /**
         * expose
         * ->current_order
         * ->order_details
         * ->data
         */

        $this->NexoLogModel->log(
            __( 'Création d\'une commande', 'nexo' ),
            sprintf( 
                __( 'Une nouvelle commande <strong>%s</strong> dont le total vaut <strong>%s</strong> a été crée par <strong>%s</strong>.', 'nexo' ),
                $current_order[0][ 'CODE' ],
                $current_order[0][ 'TOTAL' ],
                User::pseudo()
            )
        );

        return compact( 'current_order', 'order_details', 'data' );
    }
}