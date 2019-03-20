<?php
class Nexo_Stock_Manager_Controller extends Tendoo_Module
{

    /**
     * Crud Header
     * @return void
    **/

    public function history_crud()
    {
        // if (
        //     ! User::can('edit_transfer_history')    &&
        //     ! User::can('delete_transfer_history')    &&
        //     ! User::can('update_transfer_history') && 
        //     ! User::can('add_transfer_history')
        // ) {
        //     redirect(array( 'dashboard', 'access-denied' ));
        // }
		
		/**
		 * This feature is not more accessible on main site when
		 * multistore is enabled
		**/

        $this->load->module_model( 'stock-manager', 'Transfert_model', 'transfert_model' );
		
        $crud = new grocery_CRUD();
        $crud->set_subject(__('Historique de transfert de stock', 'nexo'));
        $crud->set_theme('bootstrap');
        // $crud->set_theme( 'bootstrap' );
        $crud->set_table( $this->db->dbprefix( 'nexo_stock_transfert' ) );
		
		// If Multi store is enabled
		// @since 2.8		
		$columns					=	array( 'TITLE', 'STATUS', 'APPROUVED_BY', 'FROM_STORE', 'DESTINATION_STORE', 'TYPE', 'AUTHOR', 'DATE_CREATION' );
		
        $crud->columns( $columns );
        $crud->order_by( 'nexo_stock_transfert.DATE_CREATION', 'DESC' );
        // $crud->fields( $fields );
        $crud->where( 'FROM_STORE', get_store_id() );
        $crud->or_where( 'DESTINATION_STORE', get_store_id() );
        
        $crud->unset_add();
        $crud->unset_delete();
        $crud->unset_edit();
        
        $crud->display_as('TITLE', __('Name', 'stock-manager'));
        $crud->display_as('STATUS', __('Status', 'stock-manager'));
        $crud->display_as('APPROUVED_BY', __('Edited by', 'stock-manager'));
        $crud->display_as('FROM_STORE', __('From', 'stock-manager'));
        $crud->display_as('DESTINATION_STORE', __('Send To', 'stock-manager'));
        $crud->display_as( 'TYPE', __('Type', 'stock-manager'));
        $crud->display_as( 'DATE_CREATION', __('Created on', 'stock-manager'));
        $crud->display_as( 'AUTHOR', __('Author', 'stock-manager'));

        $crud->set_relation('AUTHOR', 'aauth_users', 'name');
        $crud->set_relation('APPROUVED_BY', 'aauth_users', 'name');
        $crud->set_relation('FROM_STORE', 'nexo_stores', 'NAME');
        $crud->set_relation('DESTINATION_STORE', 'nexo_stores', 'NAME');

        $crud->add_action(__('Transfert Invoice', 'nexo'), '', dashboard_url([ 'stock-transfert-invoice' ]) . '/', 'fa fa-file');
    
        $crud->callback_column( 'STATUS', function( $primary, $row ) {
            if( $row->STATUS == 'pending' ) {
                return __( 'Pending', 'stock-manager' );
            } else if( $row->STATUS == 'approved') {
                return __( 'Approved', 'stock-manager' );
            } else if( $row->STATUS == 'rejected' ) {
                return __( 'Rejected', 'stock-manager' );
            } else if( $row->STATUS == 'canceled' ) {
                return __( 'Canceled', 'stock-manager' );
            } else if( $row->STATUS == 'requested' ) {
                return __( 'Requested', 'stock-manager' );
            } else if( $row->STATUS == 'transfered' ) {
                return __( 'Transfered', 'stock-manager' );
            }
        });
        
        // XSS Cleaner
        $this->events->add_filter( 'grocery_filter_row', function( $row ) {
            if( $row->sa2ab2bc9 == null ) {
                $row->sa2ab2bc9      =   __( 'N/A', 'stock-manager' );
            }

            if( $row->sa3130f62 == null ) {
                $row->sa3130f62 =   __( 'Main Warehouse', 'stock-manager' );
            }

            if( $row->s837f6f5d == null ) {
                $row->s837f6f5d =   __( 'Main Warehouse', 'stock-manager' );
            }

            return $row;
        });
        
        $this->events->add_filter( 'grocery_filter_actions', function( $data ) {
            $urls           =   $data[0];
            $actions        =   $data[1]; 
            $row            =   $data[2];
            $query          =   $this->transfert_model->get( $row->ID );

            if( $query[0][ 'STATUS' ] === 'pending' && intval( $row->DESTINATION_STORE ) == get_store_id() ) { // means pending

                $urls[ 'receive' ]   =  'javascript:void(0)';
                $actions[ 'receive' ]                   =   new stdClass;
                $actions[ 'receive' ]->css_class        =   'fa fa-check approve_transfert_btn';
                $actions[ 'receive' ]->label            =   __( 'Allow the Transfert', 'stock-manager' );
                $actions[ 'receive' ]->text             =   __( 'Approuve', 'stock-manager' );

                $urls[ 'refuse' ]                   =   'javascript:void(0)';
                $actions[ 'refuse' ]                =   new stdClass;
                $actions[ 'refuse' ]->css_class     =   'fa fa-remove reject_transfert_btn';
                $actions[ 'refuse' ]->label         =   __( 'Refuse', 'stock-manager' );
                $actions[ 'refuse' ]->text             =   __( 'Reject', 'stock-manager' );
            } else if( $query[0][ 'STATUS' ] === 'pending' && intval( $row->FROM_STORE ) == get_store_id() ) {
                $urls[ 'void' ]                   =   'javascript:void(0)';
                $actions[ 'void' ]                =   new stdClass;
                $actions[ 'void' ]->css_class     =   'fa fa-remove cancel_transfert_btn';
                $actions[ 'void' ]->label         =   __( 'Cancel', 'stock-manager' );
                $actions[ 'void' ]->text            =   __( 'Cancel', 'stock-manager' );
            } else if( $query[0][ 'STATUS' ] === 'requested' && intval( $row->FROM_STORE ) == get_store_id() ) {
                $urls[ 'accept-transfert' ]                   =   'javascript:void(0)';
                $actions[ 'accept-transfert' ]                =   new stdClass;
                $actions[ 'accept-transfert' ]->css_class     =   'fa fa-check approve_request_btn';
                $actions[ 'accept-transfert' ]->label         =   __( 'Approve Request', 'stock-manager' );
                $actions[ 'accept-transfert' ]->text            =   __( 'Approve Request', 'stock-manager' );

                $urls[ 'void' ]                   =   'javascript:void(0)';
                $actions[ 'void' ]                =   new stdClass;
                $actions[ 'void' ]->css_class     =   'fa fa-remove cancel_transfert_btn';
                $actions[ 'void' ]->label         =   __( 'Cancel Request', 'stock-manager' );
                $actions[ 'void' ]->text            =   __( 'Cancel Request', 'stock-manager' );
            }


            return [ $urls, $actions, $row ];
        }, 10 );
        $this->events->add_filter('grocery_callback_insert', array( $this->grocerycrudcleaner, 'xss_clean' ));
        $this->events->add_filter('grocery_callback_update', array( $this->grocerycrudcleaner, 'xss_clean' ));
        
        // $crud->columns('customerName','phone','addressLine1','creditLimit');

        $crud->unset_jquery();
        $output = $crud->render();
        
        foreach ($output->js_files as $files) {
            $this->enqueue->js(substr($files, 0, -3), '');
        }
        foreach ($output->css_files as $files) {
            $this->enqueue->css(substr($files, 0, -4), '');
        }
        
        return $output;
    }

    /**
     * Crud
     * @return void
     */
    public function transfert_history()
    {
        $crud       =   $this->history_crud();
        $this->events->add_action( 'dashboard_footer', function(){
            get_instance()->load->module_view( 'stock-manager', 'transfert.history-script' );
        });
        $this->Gui->set_title( store_title( 'Stock Transfer History', 'stock-manager') );
        $this->load->module_view( 'stock-manager', 'transfert.history-gui', compact( 'crud' ) );
    }

    /**
     * New Transfert
     * @return void
     */
    public function new_transfert()
    {
        $this->load->model( 'Nexo_Stores' );

        switch( @$_GET[ 'request' ] ) {
            case 'true': 
                $this->Gui->set_title( store_title( 'Stock Tranfert Request', 'stock-manager' ) );
            break;
            default:
                $this->Gui->set_title( store_title( 'New Stock Transfer', 'stock-manager' ) );
            break;
        }
        
        $this->events->add_action( 'dashboard_footer', function(){
            get_instance()->load->module_view( 'stock-manager', 'transfert.script' );
        });

        return $this->load->module_view( 'stock-manager', 'transfert.gui', null, true );

    }

    /**
     * History Stock Manager
     * @param string 
     * @return void
    **/

    public function history( $page = 'history', $id = 'null' )
    {
        $crud       =   $this->history_crud();
    }

    /**
     * Invoice Report
     * @param void
     * @return void
     */
    public function transfert_invoice( $id )
    {
        $this->load->library( 'parser' );
        $this->load->module_model( 'stock-manager', 'transfert_model' );
        $transfert      =   $this->transfert_model->get( $id );
        if( $transfert ) {
            $items          =   $this->transfert_model->get_with_items( $id );
        } else {
            return show_error( __( 'Page introuvable', 'nexo_premium' ) );
        }

        // denied access to unauthorized
        if( ! in_array( get_store_id(), [ $transfert[0][ 'FROM_STORE' ], $transfert[0][ 'DESTINATION_STORE' ] ] ) ) {
            return show_error( __( 'Vous n\'avez pas accès à cette page', 'nexo_premium' ) );
        }

        $this->Gui->set_title( store_title( __( 'Transfert Invoice' ) ) );
        $this->load->module_view( 'stock-manager', 'transfert.invoice-gui', compact( 'transfert', 'items' ) );
    }

    /**
     * Settings Page for Transfert
     * @return void
    **/

    public function settings()
    {
        $this->Gui->set_title( store_title( __( 'Transfert Settings', 'stock-manager' ) ) );
        $this->load->module_view( 'stock-manager', 'settings.gui' );
    }

    /**
     * Request a transfert
     * @return void
     */
    public function request()
    {
        /**
         * Because we're so lazy !!!
         */
        $_GET[ 'request' ]      =   'true';
        return $this->new_transfert();   
    }
}