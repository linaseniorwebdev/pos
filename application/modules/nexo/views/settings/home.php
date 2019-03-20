<?php
/**
 * Add support for Multi Store
 * @since 2.8
**/

global $store_id, $CurrentStore;

$option_prefix		=	'';

if( $store_id != null ) {
	$option_prefix	=	'store_' . $store_id . '_' ;
}

$this->Gui->col_width(1, 4);
// $this->Gui->col_width(2, 2);

$this->Gui->add_meta(array(
    'type'            =>        'unwrapped',
    'namespace'        =>        'Nexo_shop_details',
    'title'            =>        __('Détails de la boutique', 'nexo'),
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
        'subPath'   =>  'general',
        'namespace' =>  'Nexo_shop_details',
        'tabs'  =>  [
            'basic-informations'    =>  __( 'Informations', 'nexo' ),
            'currencies'            =>  __( 'Devises', 'nexo' ),
            'rebranding'            =>  __( 'Marque', 'nexo' ),
            'date-format'           =>  __( 'Format Date', 'nexo' ),
            'fx'                    =>  __( 'Effets', 'nexo' ),
            'advanced'              =>  __( 'Avancé', 'nexo' ),
        ],
        'activeTab'     =>  isset( $_GET[ 'tab' ] ) ? xss_clean( $_GET[ 'tab' ] ) : 'basic-informations'
    ], true )
], 'Nexo_shop_details', 1 );

$this->events->do_action('load_nexo_general_settings', $this->Gui);

$this->Gui->output();

