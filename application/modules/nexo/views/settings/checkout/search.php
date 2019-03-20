<?php
$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'enable_quick_search',
    'label'        =>    __('Activer la recherche rapide ?', 'nexo'),
	'description'	=>	__( 'Si votre boutique contient beaucoup de produits, l\'utilisation de la recherche rapide est indispensable.', 'nexo' ),
    'options'    =>    array(
		''		=>	__( 'Veuillez choisir une option', 'nexo' ),
        'no'        =>    __('Non', 'nexo'),
		'yes'        =>    __('Oui', 'nexo')
    )
), $namespace, 1 );

/**
 * @since 3.12.8
 */
$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'auto_submit_barcode_entry',
    'label'        =>    __('Soumission Automatique', 'nexo'),
	'description'	=>	__( 'Si votre lecteur de code barre ne soumet pas automatiquement les codes scannées, vous pouvez utiliser cette fonctionnalité qui s\'active seulement si la recherche rapide est activée.', 'nexo' ),
    'options'    =>    array(
		''		=>	__( 'Veuillez choisir une option', 'nexo' ),
        'no'        =>    __('Non', 'nexo'),
		'yes'        =>    __('Oui', 'nexo')
    )
), $namespace, 1 );