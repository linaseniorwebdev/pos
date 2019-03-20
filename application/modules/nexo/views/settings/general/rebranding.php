<?php
$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_logo_type',
    'label'        =>    __('Type du logo', 'nexo'),
    'options'    =>    array(
		'default'	=>	__( 'Valeur par défaut', 'nexo' ),
		'image_url'	=>	__( 'Lien vers une image', 'nexo' ),
		'text'		=>	__( 'Texte personnalisé', 'nexo')
    )
), $namespace, 1 );

$this->Gui->add_item(array(
    'type'        =>    'text',
    'label'        =>    __('Texte du logo', 'nexo'),
    'name'        =>    store_prefix() . 'nexo_logo_text',
    'placeholder'    =>    ''
), $namespace, 1 );

$this->Gui->add_item(array(
    'type'        =>    'text',
    'label'        =>    __('Lien vers URL une image', 'nexo'),
    'name'        =>    store_prefix() . 'nexo_logo_url',
    'placeholder'    =>    ''
), $namespace, 1 );

$this->Gui->add_item(array(
    'type'        =>    'text',
    'label'        =>    __('Largeur du logo', 'nexo'),
    'name'        =>    store_prefix() . 'nexo_logo_width',
    'placeholder'    =>    ''
), $namespace, 1 );

$this->Gui->add_item(array(
    'type'        =>    'text',
    'label'        =>    __('Hauteur du logo', 'nexo'),
    'name'        =>    store_prefix() . 'nexo_logo_height',
    'placeholder'    =>    ''
), $namespace, 1 );

$this->Gui->add_item(array(
    'type'        =>    'text',
    'label'        =>    __('Texte du pied de page', 'nexo'),
    'name'        =>    store_prefix() . 'nexo_footer_text',
    'placeholder'    =>    ''
), $namespace, 1 );