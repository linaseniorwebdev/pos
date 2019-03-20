<?php

$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_vat_type',
    'label'        =>    __( 'Activer la TVA', 'nexo'),
    'options'    =>    array(
		''		=>	__( 'Veuillez choisir une option', 'nexo' ),
        'disabled'      =>    __('Désactiver', 'nexo'),
        'fixed'         =>    __('TVA fixe', 'nexo'),
        'variable'      =>    __('TVA variable', 'nexo'),
        'item_vat'      =>      __( 'TVA des produits', 'nexo' )
    )
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'label'        =>    __('Définir le taux fixe de la TVA (%)', 'nexo'),
    'name'        =>    store_prefix() . 'nexo_vat_percent',
    'placeholder'    =>    __('Exemple : 20', 'nexo')
), $namespace, 1);