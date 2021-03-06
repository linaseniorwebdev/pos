<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class elFinder extends CI_Model{

    public function __construct()
    {
        return;
        parent::__construct();
        //Codeigniter : Write Less Do More

        $this->events->add_filter( 'admin_menus', array( $this, 'admin_menus' ), 15 );
        $this->events->add_action( 'do_enable_module', [ $this, 'enable_module' ] );
        $this->events->add_action( 'tendoo_settings_final_config', [ $this, 'final_config' ] );
    }

    /**
     * Final Config
     * @return config
    **/

    public function final_config()
    {
        $this->aauth        =    $this->users->auth;
        $this->aauth->create_perm( 'view_file_manager',    __( 'File Manager Access', 'nexo'),            __('Let the use have access to the file manager.', 'nexo'));
        $this->aauth->allow_group( 'master', 'view_file_manager');
    }

    /**
     * Enable module
     * @return void
    **/

    public function enable_module( $namespace ) 
    {
        if( $namespace == 'elfinder' && get_option( 'elfinder_installed' ) == null ) {
            $this->aauth->create_perm( 'view_file_manager',    __( 'File Manager Access', 'nexo'),            __('Let the use have access to the file manager.', 'nexo'));
            $this->options->set( 'view_file_manager', 'true', true );
        }
    }

    /**
    *
    * Admin Menus
    *
    * @param  array Menus
    * @return array new menu
    */

    public function admin_menus( $menus )
    {
        $backup     =   $menus;
        if( User::can( 'view_file_manager' ) ) :
            $menus  =   array_insert_before( 'settings', $menus, 'elfinder', array(
                array(
                    'title' =>  __( 'File Manager', 'elfinder' ),
                    'href'  =>  site_url( array( 'dashboard', 'files' ) ),
                    'icon'  =>  'fa fa-file'
                )
            ));
        endif;
        return $menus ? $menus : $backup;
    }
}

new elFinder;
