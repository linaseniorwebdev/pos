<?php
class LangSwitcher extends Tendoo_Module
{
    protected $session;
    protected $language;

    public function __construct()
    {
        parent::__construct();
        $this->session      =   get_instance()->session;
        $this->load->config( 'tendoo' );
        $this->languages    =   $this->config->item( 'supported_languages' );
        
        // get_instance()->session->set_userdata( 'site_language', 'tr_TR' );
        // var_dump( get_instance()->session->userdata() );die;

        $this->events->add_action( 'after_app_init', function() {
            $siteLanguage     =   get_instance()->session->userdata( 'site_language' );

            if ( $siteLanguage !==  $this->input->get( 'lang' ) && ! empty( $this->input->get( 'lang' ) ) ) {
                if( ! empty( $this->input->get( 'lang' ) ) ) {
                    $lang   =  $this->input->get( 'lang' );
                } else {
                    die( 'ok' );
                    $lang   =   'en_US';
                }
                
                set_option( 'site_language',  ( string ) $lang );
                get_instance()->session->set_userdata( 'site_language', $lang );
                redirect( current_url() );
            }
        }, 30 );
    }
}
new LangSwitcher;