<?php
/**
 * New print solution
 * )@since 3.12.5
 */
$this->Gui->add_item(array(
    'type'        =>    'dom',
    'content'    =>    '<h4>' . __('Configuration de l\'impression', 'nexo') . '</h4>'
), $namespace, 1);

/**
 * @since 2.3
**/
$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_enable_autoprint',
    'label'        =>    __('Activer l\'impression automatique des tickets de caisse ?', 'nexo'),
    'description'        =>    __('Par défaut vaut : "Non"', 'nexo'),
    'options'    =>    array(
        ''            =>    __('Veuillez choisir une option', 'nexo'),
        'yes'        =>    __('Oui', 'nexo'),
        'no'        =>    __('Non', 'nexo')
    )
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_print_gateway',
    'label'                 =>  __( 'Passerelle d\'impression ?', 'nexo'),
    'description'           =>  __( 'Par défaut vaut : "Impression normale". Vous pouvez aussi décider d\'utiliser les imprimantes des caisses enregistreuses.', 'nexo'),
    'options'               =>  array(
        ''                  =>  __('Veuillez choisir une option', 'nexo'),
        'normal_print'      =>  __('Impression Normale', 'nexo'),
        'nexo_print_server' =>  __('Nexo Print Server', 'nexo'),
        'register_nps'      =>  __( 'Imprimantes des caisses enregistreuses (NPS)', 'nexo' )
    )
), $namespace, 1);

$this->Gui->add_item(array(
    'type'          =>    'text',
    'label'         =>    __('Nexo Print Server URL', 'nexo'),
    'name'          =>    store_prefix() . 'nexo_print_server_url',
    'description'   =>    __('Par défaut: "http://localhost:3236"', 'nexo')
), $namespace, 1);

$this->Gui->add_item(array(
    'type'          =>    'select',
    'name'          =>    store_prefix() . 'nexo_pos_printer',
    'label'         =>    __( 'Choisir une imprimante', 'nexo'),
    'description'   =>    __('Choisir une imprimante pour les tickets de caisse', 'nexo'),
    'options'       =>    array(
        ''          =>    __('Veuillez choisir une option', 'nexo')
    )
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'dom',
    'content'    =>     $this->load->module_view( 'nexo', 'settings.select-printer-script', null, true )
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'dom',
    'content'    =>    '<h4>' . __( 'Configuration du thème des reçus', 'nexo') . '</h4>'
), $namespace, 1 );

$receipt_themes 	=	$this->events->apply_filters( 'nexo_receipt_theme', array(
    'default'       =>    __('Par défaut', 'nexo'),
    'light'		    =>	__( 'Léger', 'nexo' ),
	'simple'		=>	__( 'Simple', 'nexo' )
) );

$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_receipt_theme',
    'label'        =>    __('Thème des tickets de caisse', 'nexo'),
    'options'    =>    $receipt_themes
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_nps_print_copies',
    'label'        =>    __( 'Nombre d\'exemplaire d\'impression NPS', 'nexo'),
    'description'   =>  __( 'Permet de déterminer le nombre de copie à imprimer sur Nexo Print Server.', 'nexo' ),
    'options'    =>    [
        1   =>  __( '1 Copie', 'nexo' ),
        2   =>  __( '2 Copies', 'nexo' ),
        3   =>  __( '3 Copies', 'nexo' ),
        4   =>  __( '4 Copies', 'nexo' ),
        5   =>  __( '5 Copies', 'nexo' )
    ]
), $namespace, 1);