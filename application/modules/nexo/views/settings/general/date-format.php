<?php
$this->Gui->add_item( array(
    'type'          =>    'dom',
    'content'       =>    '<h4>' . __( 'Configuration du format des dates', 'nexo' ) . '</h4>'
), $namespace, 1 );

$this->Gui->add_item( array(
    'type' =>    'text',
    'name' =>	store_prefix() . 'nexo_date_format',
    'label' =>   __( 'Format de la date', 'nexo' ),
    'description' =>   __( 'Permet de formater la date', 'nexo' ),
), $namespace, 1 );


$this->Gui->add_item( array(
    'type' =>    'text',
    'name' =>	store_prefix() . 'nexo_datetime_format',
    'label' =>   __( 'Format de la date et de l\'heure', 'nexo' ),
    'description' =>   __( 'Permet de formater la date et l\'heure. Par défaut : Y-m-d h:i:s a', 'nexo' ),
), $namespace, 1 );

$this->Gui->add_item( array(
    'type' =>    'text',
    'name' =>	store_prefix() . 'nexo_js_datetime_format',
    'label' =>   __( 'Format de la date et de l\'heure pour javascript', 'nexo' ),
    'description' =>   __( 'Permet de formater la date et l\'heure', 'nexo' ),
), $namespace, 1 );