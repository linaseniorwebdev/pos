<?php

/**
 * Add support for Multi Store
 * @since 2.8
**/

global $store_id, $CurrentStore;

$this->Gui->col_width(1, 4);

$this->Gui->add_meta(array(
    'namespace'        =>        'nexo_checkout_settings',
    'title'            =>        __('Réglages de la caisse', 'nexo'),
    'col_id'        =>        1,
    'gui_saver'        =>        true,
    'footer'        =>        array(
        'submit'    =>        array(
            'label'    =>        __('Sauvegarder les réglages', 'nexo')
        )
    ),
    'use_namespace'    =>        false,
));

$this->Gui->add_item([
    'type'      =>  'dom',
    'content'   =>  $this->load->module_view( 'nexo', 'settings.tab-wrapper', [
        'subPath'   =>  'checkout',
        'namespace' =>  'nexo_checkout_settings',
        'tabs'  =>  [
            'registers'         =>  __( 'Caisses', 'nexo' ),
            'vat'               =>  __( 'TVA', 'nexo' ),
            'features'          =>  __( 'Fonctionnalités', 'nexo' ),
            'printers'          =>  __( 'Imprimantes', 'nexo' ),
            'shortcuts'         =>  __( 'Raccourcis', 'nexo' ),
            'sms'               =>  __( 'SMS', 'nexo' ),
            'search'            =>  __( 'Recherche', 'nexo' ),
        ],
        'activeTab'     =>  isset( $_GET[ 'tab' ] ) ? xss_clean( $_GET[ 'tab' ] ) : 'registers'
    ], true )
], 'nexo_checkout_settings', 1 );

$this->events->do_action('load_nexo_checkout_settings', $this->Gui);

$this->Gui->output();
