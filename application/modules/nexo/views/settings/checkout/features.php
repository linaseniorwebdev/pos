<?php

$this->Gui->add_item([
    'type'          =>    'select',
    'name'          =>    store_prefix() . 'unit_item_discount_enabled',
    'label'         =>    __('Activer la remise par article ?', 'nexo'),
	'description'	=>	__( 'Permet d\'appliquer une remise sur un produit unique. Ce type de remise est différent à la remise du panier, qui s\'applique sur tout les produits du panier.', 'nexo' ),
    'options'       =>    [
		''		    =>	__( 'Veuillez choisir une option', 'nexo' ),
        'no'        =>    __('Non', 'nexo'),
		'yes'       =>    __('Oui', 'nexo')
    ]
], $namespace, 1 );

$this->Gui->add_item([
    'type'        =>    'dom',
    'content'    =>    '<h4>' . __('Visibilité des bouttons', 'nexo') . '</h4>'
], $namespace, 1);

$this->Gui->add_item([
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'hide_discount_button',
    'label'        =>    __('Masquer le bouton des remises ?', 'nexo'),
	'description'	=>	__( 'Cette fonctionnalité vous permet de restreindre l\'utilisation du bouton des remises sur le point de vente. Si la fonctionnalité des coupons est active, les remises de ces dernières peuvent toujours s\'appliquer à une commande.', 'nexo' ),
    'options'    =>    [
		''		=>	__( 'Veuillez choisir une option', 'nexo' ),
        'no'        =>    __('Non', 'nexo'),
		'yes'        =>    __('Oui', 'nexo')
    ]
], $namespace, 1);

$this->Gui->add_item([
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'disable_coupon',
    'label'        =>    __('Désactiver les coupons ?', 'nexo'),
	'description'	=>	__( 'Désactiver l\'option des coupons empêcheront à ces dernièrs de s\'appliquer aux commandes. La désactivation des coupons n\'empêchera pas au délais des coupons déjà émis de s\'écouler.', 'nexo' ),
    'options'    =>    [
		''		=>	__( 'Veuillez choisir une option', 'nexo' ),
        'no'        =>    __('Non', 'nexo'),
		'yes'        =>    __('Oui', 'nexo')
    ]
], $namespace, 1);

$this->Gui->add_item([
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'disable_shipping',
    'label'        =>    __('Désactiver les livraisons ?', 'nexo'),
	'description'	=>	__( 'Désactiver l\'option des livraisons.', 'nexo' ),
    'options'    =>    [
		''		=>	__( 'Veuillez choisir une option', 'nexo' ),
        'no'        =>    __('Non', 'nexo'),
		'yes'        =>    __('Oui', 'nexo')
    ]
], $namespace, 1);

$this->Gui->add_item([
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'disable_customer_creation',
    'label'        =>    __('Désactiver la création des clients ?', 'nexo'),
	'description'	=>	__( 'Permet de désactiver la création des clients. Ces dernièrs pourront toujours être créés depuis l\'interface classique.', 'nexo' ),
    'options'    =>    [
		''		=>	__( 'Veuillez choisir une option', 'nexo' ),
        'no'        =>    __('Non', 'nexo'),
		'yes'        =>    __('Oui', 'nexo')
    ]
], $namespace, 1);

$this->Gui->add_item([
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'disable_quick_item',
    'label'        =>    __('Désactiver la création rapide de produits ?', 'nexo'),
	'description'	=>	__( 'Par défaut, il est possible d\'ajouter des produits et services directement depuis le point de vente. En choisissant "oui", cette fonctionnalité ne sera plus disponible.', 'nexo' ),
    'options'    =>    [
		''		=>	__( 'Veuillez choisir une option', 'nexo' ),
        'no'        =>    __('Non', 'nexo'),
		'yes'        =>    __('Oui', 'nexo')
    ]
], $namespace, 1);

$this->Gui->add_item([
    'type'        =>    'dom',
    'content'    =>    '<h4>' . __('Réglages des prix', 'nexo') . '</h4>'
], $namespace, 1);


$this->Gui->add_item([
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'unit_price_changing',
    'label'        =>    __('Prix unitaire modifiable ?', 'nexo'),
	'description'	=>	__( 'Permet au prix d\'être modifié. La modification du prix unitaire s\'applique uniquement à la vente en cours. Cette modification portera sur le prix de vente, le prix promotionnel et sur le prix fictif.', 'nexo' ),
    'options'    =>    [
		''		=>	__( 'Veuillez choisir une option', 'nexo' ),
        'no'        =>    __('Non', 'nexo'),
		'yes'        =>    __('Oui', 'nexo')
    ]
], $namespace, 1);

$this->Gui->add_item([
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_enable_shadow_price',
    'label'        =>    __('Utiliser les prix fictif', 'nexo'),
    'description'        =>    __('Permet d\'afficher un prix fictif "discutable", qui ne doit pas être inférieure au prix de vente réel d\'un article.', 'nexo'),
    'options'    =>    [
        ''            =>    __('Veuillez choisir une option', 'nexo'),
        'yes'        =>    __('Oui', 'nexo'),
        'no'        =>    __('Non', 'nexo')
    ]
], $namespace, 1);

$this->Gui->add_item([
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_show_remaining_qte',
    'label'        =>    __('Afficher les quantités restantes', 'nexo'),
    'description'        =>    __('Permet d\'afficher les quantités restantes sur l\'interface de vente.', 'nexo'),
    'options'    =>    [
        ''            =>    __('Veuillez choisir une option', 'nexo'),
        'yes'        =>    __('Oui', 'nexo'),
        'no'        =>    __('Non', 'nexo')
    ]
], $namespace, 1);


$this->Gui->add_item([
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_enable_numpad',
    'label'        =>    __('Activer le clavier numérique', 'nexo'),
    'options'    =>    [
		''		=>	__( 'Veuillez choisir une option', 'nexo' ),
        'oui'        =>    __('Oui', 'nexo'),
        'non'        =>    __('Non', 'nexo')
    ]
], $namespace, 1);

$this->Gui->add_item([
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'disable_partial_order',
    'label'        =>    __('Désactiver les commandes incomplètes ?', 'nexo'),
	'description'	=>	__( 'Cette option permettra de désactiver l\'enregistrement des commandes incomplètes dans le système.', 'nexo' ),
    'options'    =>    [
		''		=>	__( 'Veuillez choisir une option', 'nexo' ),
        'no'        =>    __('Non', 'nexo'),
		'yes'        =>    __('Oui', 'nexo')
    ]
], $namespace, 1 );