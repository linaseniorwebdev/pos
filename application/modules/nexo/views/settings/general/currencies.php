<?php
$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'nexo_currency',
    'label'        =>    __('Symbole de la devise', 'nexo'),
    'description'   =>  __( 'Permet de définir le symbole de la devise.', 'nexo' ),
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'nexo_currency_iso',
    'label'        =>    __('Format ISO de la devise', 'nexo'),
    'description'   =>  __( 'Permet de définir le symbole de la devise.', 'nexo' ),
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_currency_position',
    'label'        =>    __('Position de la devise', 'nexo'),
    'description'   =>  __( 'Permet de déterminer la position de la devise en fonction du montant.', 'nexo' ),
    'options'    =>    array(
        'before'    =>    __('Avant le montant', 'nexo'),
        'after'        =>    __('Après le montant', 'nexo')
    )
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'thousand_separator',
    'label'        =>    __('Séparateur de milliers', 'nexo'),
    'description'   =>  __( 'Permet de définir un séparateur pour les milliers. Par défaut "," est utilisé', 'nexo' )
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'decimal_separator',
    'label'        =>    __('Séparateur Décimal', 'nexo'),
    'description'   =>  __( 'Permet de définir un séparateur pour les valeur décimales. Par défaut "." est utilisé', 'nexo' )
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'select',
    'options'       =>  [ 0, 1, 2, 3, 4, 5 ],
    'name'        =>    store_prefix() . 'decimal_precision',
    'label'        =>    __('Précision Décimale', 'nexo'),
    'description'   =>  __( 'La précision décimale permet de définir le nombre de chiffre après la virgule pour un nombre décimal.', 'nexo' )
), $namespace, 1);