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

$this->Gui->col_width( 1, 2 );
$this->Gui->col_width( 2, 2 );

$this->Gui->add_meta( array(
	'col_id'		=>	1,
	'namespace'		=>	'invoice1',
	'type'			=>	'box',
	'title'			=>	__( 'Réglages des reçus de caisse', 'nexo' ),
	'gui_saver'		=>	true,
	'footer'		=>	array(
		'submit'	=>	array(
			'label'	=>	__( 'Sauvegarder les réglages', 'nexo' )
		)
	),
	'use_namespace'	=>	false
) );

/**
$this->Gui->add_item( array(
	'type'			=>	'select',
	'options'		=>	$this->config->item( 'nexo_receipts_namespaces' ),
	'name'			=>	$option_prefix . 'nexo_receipt',
	'label'			=>	__( 'Veuillez choisir le format du reçu par défaut', 'nexo' )
), 'invoice1', 1 );
**/

$this->Gui->add_item([
	'type'		=>	'select',
	'options'	=>	[
		'only_primary' 		=>	__( 'Uniquement le nom principal', 'nexo' ),
		'only_secondary' 	=>	__( 'Uniquement le nom alternatif', 'nexo' ),
		'use_both' 			=>	__( 'Utiliser les deux noms', 'nexo' ),
	],
	'name'			=>	$option_prefix . 'item_name',
	'label'		=>	__( 'Quel nom du produit doit être affiché ?', 'nexo' ),
	'description'	=>	__( 'Vous permet de choisir si vous souhaitez afficher le nom alternatif ou le nom principal, ensemble ou séparément.', 'nexo' )
], 'invoice1', 1 );

$this->Gui->add_item( array(
	'type'			=>	'text',
	'name'			=>	$option_prefix . 'url_to_logo',
	'label' 			=>	__( 'Url logo', 'nexo' ),
	'description'			=>	__( 'Si ce champ est rempli, l\'image sera affichée sur le reçu de vente à la place du nom de la boutique.', 'nexo' ),
), 'invoice1', 1 );

$this->Gui->add_item( array(
	'type'			=>	'text',
	'name'			=>	$option_prefix . 'logo_height',
	'label' 			=>	__( 'Hauteur Logo(px)', 'nexo' ),
	'description'			=>	__( 'Forcer la hauteur du logo. Veuillez définir une valeur numérique, par exemple : "30" et non "30px"', 'nexo' ),
), 'invoice1', 1 );

$this->Gui->add_item( array(
	'type'			=>	'text',
	'name'			=>	$option_prefix . 'logo_width',
	'label' 			=>	__( 'Largeur Logo(px)', 'nexo' ),
	'description'			=>	__( 'Forcer la largeur du logo. Veuillez définir une valeur numérique, par exemple : "30" et non "30px"', 'nexo' ),
), 'invoice1', 1 );

$this->Gui->add_item( array(
	'type'			=>	'textarea',
	'name'			=>	$option_prefix . 'receipt_col_1',
	'label'			=>	__( 'Colonne 1 du reçu par défaut', 'nexo' ),
), 'invoice1', 1 );

$this->Gui->add_item( array(
	'type'			=>	'textarea',
	'name'			=>	$option_prefix . 'receipt_col_2',
	'label'			=>	__( 'Colonne 2 du reçu par défaut', 'nexo' ),
), 'invoice1', 1 );

$this->Gui->add_item(array(
    'type'        =>    'textarea',
    'name'        =>    $option_prefix . 'nexo_bills_notices',
    'label'        =>    __('Notes pour factures', 'nexo')
), 'invoice1', 1);

$this->Gui->add_item( array(
	'type'			=>	'dom',
	'content'		=>
	$this->events->apply_filters( 'nexo_filter_invoice_dom_tag_list', __( '<h4>Utilisez les balises suivantes : </h4>', 'nexo' ) .
		__( '{shop_name} pour afficher le nom de la boutique', 'nexo' ) . '<br>' .
		__( '{shop_phone} pour afficher le numéro de téléphone de la boutique', 'nexo' ) . '<br>' .
		__( '{shop_fax} pour afficher le fax de la boutique', 'nexo' ) . '<br>' .
		__( '{shop_pobox} pour afficher la boite postale de la boutique', 'nexo' ) . '<br>' .
		__( '{shop_street} pour afficher la rue de la boutique', 'nexo' ). '<br>' .
		__( '{shop_email} pour afficher l\'email de la boutique', 'nexo' ). '<br>' .
		__( '{order_date}, pour afficher la date de la commande.', 'nexo' ) . '<br>' .
		__( '{order_updated}, pour afficher la date de modification de la commande.', 'nexo' ) . '<br>' .
		__( '{order_code}, pour afficher le code de la commande.', 'nexo' ) . '<br>' .
		__( '{order_id}, pour afficher l\'identifiant de la commande.', 'nexo' ) . '<br>' .
		__( '{order_note}, pour afficher les notes de la commande.', 'nexo' ) . '<br>' .
		__( '{order_cashier}, pour afficher l\'auteur de la commande.', 'nexo' ) . '<br>' .
		__( '{customer_name}, pour afficher le nom du client.', 'nexo' ) . '<br>' .
		__( '{customer_phone}, pour afficher le numéro de téléphone du client.', 'nexo' ) . '<br>' . 
		
		__( '<h3>Delivery Informations</h3>', 'nexo' ) . '<br>' .		
		__( '{delivery_address_1}, Pour afficher les informations de livraison addresse 1.', 'nexo' ) . '<br>' .
		__( '{delivery_address_2}, Pour afficher les informations de livraison addresse 2', 'nexo' ) . '<br>' .
		__( '{city}, pour afficher la ville de livraison.', 'nexo' ) . '<br>' .
		__( '{country}, pour afficher le pays de livraison', 'nexo' ) . '<br>' .
		__( '{name}, Pour afficher la personne à qui est destiné la livraison', 'nexo' ) . '<br>' .
		__( '{phone}, Pour afficher le numéro de téléphone indiquée à l\'adresse de livraison.', 'nexo' ) . '<br>' .
		__( '{surname}, Pour afficher le prénom de la personne à qui est destiné la livraison.', 'nexo' ) . '<br>' .
		__( '{state}, Pour afficher l\'état ou la livraison doit avoir lieu.', 'nexo' ) . '<br>' .
		__( '{delivery_cost}, pour afficher le côut de livraison.', 'nexo' ) . '<br>'
	) 
), 'invoice1', 1 );

/** 
 *	-----------------------------------------------------------------------------
 *  						Refund Receipt Settings
 * 	-----------------------------------------------------------------------------
**/

$this->Gui->add_meta( array(
	'col_id'		=>	2,
	'namespace'		=>	'invoice3',
	'type'			=>	'box',
	'title'			=>	__( 'Ticket de remboursement', 'nexo' ),
	'gui_saver'		=>	true,
	'footer'		=>	array(
		'submit'	=>	array(
			'label'	=>	__( 'Sauvegarder les réglages', 'nexo' )
		)
	),
	'use_namespace'	=>	false
) );

$this->Gui->add_item( array(
	'type'			=>	'textarea',
	'name'			=>	$option_prefix . 'refund_receipt_col_1',
	'label'			=>	__( 'Colonne 1 du reçu par défaut', 'nexo' ),
), 'invoice3', 2 );

$this->Gui->add_item( array(
	'type'			=>	'textarea',
	'name'			=>	$option_prefix . 'refund_receipt_col_2',
	'label'			=>	__( 'Colonne 2 du reçu par défaut', 'nexo' ),
), 'invoice3', 2 );

$this->Gui->add_item( array(
	'type'			=>	'dom',
	'content'		=>
	__( '<h4>Utilisez les balises suivantes : </h4>', 'nexo' ) .
	__( '{refund_author} Pour afficher le nom de l\'auteur du remboursement', 'nexo' ). '<br>' .
	__( '{refund_date} Pour afficher la date du remboursement', 'nexo' ). '<br>' .
	__( '{refund_type} Pour afficher s\'il s\'agit d\'un remboursement avec ou sans retour de stock', 'nexo' ). '<br>' .
	__( '{shop_name} pour afficher le nom de la boutique', 'nexo' ) . '<br>' .
	__( '{shop_phone} pour afficher le numéro de téléphone de la boutique', 'nexo' ) . '<br>' .
	__( '{shop_fax} pour afficher le fax de la boutique', 'nexo' ) . '<br>' .
	__( '{shop_pobox} pour afficher la boite postale de la boutique', 'nexo' ) . '<br>' .
	__( '{shop_street} pour afficher la rue de la boutique', 'nexo' ). '<br>' .
	__( '{shop_email} pour afficher l\'email de la boutique', 'nexo' ). '<br>'
), 'invoice3', 2 );

/** 
 *	-----------------------------------------------------------------------------
 *  						Supply Invoice Settings
 * 	-----------------------------------------------------------------------------
**/

$this->Gui->add_meta( array(
	'col_id'		=>	2,
	'namespace'		=>	'invoice2',
	'type'			=>	'box',
	'title'			=>	__( 'Reçu d\'approvisionnement', 'nexo' ),
	'gui_saver'		=>	true,
	'footer'		=>	array(
		'submit'	=>	array(
			'label'	=>	__( 'Sauvegarder les réglages', 'nexo' )
		)
	),
	'use_namespace'	=>	false
) );

$this->Gui->add_item( array(
	'type'			=>	'textarea',
	'name'			=>	$option_prefix . 'supply_receipt_col_1',
	'label'			=>	__( 'Colonne 1 du reçu par défaut', 'nexo' ),
), 'invoice2', 2 );

$this->Gui->add_item( array(
	'type'			=>	'textarea',
	'name'			=>	$option_prefix . 'supply_receipt_col_2',
	'label'			=>	__( 'Colonne 2 du reçu par défaut', 'nexo' ),
), 'invoice2', 2 );

$this->Gui->add_item( array(
	'type'			=>	'dom',
	'content'		=>
	__( '<h4>Utilisez les balises suivantes : </h4>', 'nexo' ) .
	__( '{shop_name} pour afficher le nom de la boutique', 'nexo' ) . '<br>' .
	__( '{shop_phone} pour afficher le numéro de téléphone de la boutique', 'nexo' ) . '<br>' .
	__( '{shop_fax} pour afficher le fax de la boutique', 'nexo' ) . '<br>' .
	__( '{shop_pobox} pour afficher la boite postale de la boutique', 'nexo' ) . '<br>' .
	__( '{shop_street} pour afficher la rue de la boutique', 'nexo' ). '<br>' .
	__( '{shop_email} pour afficher l\'email de la boutique', 'nexo' ). '<br>'
), 'invoice2', 2 );

/**
 * Nexo Print Server Settings
 */
$this->Gui->add_meta( array(
	'col_id'		=>	2,
	'namespace'		=>	'nps',
	'type'			=>	'box',
	'title'			=>	__( 'Nexo Print Server', 'nexo' ),
	'gui_saver'		=>	true,
	'footer'		=>	array(
		'submit'	=>	array(
			'label'	=>	__( 'Sauvegarder les réglages', 'nexo' )
		)
	),
	'use_namespace'	=>	false
) );

$this->Gui->add_item( array(
	'type'			=>	'text',
	'name'			=>	$option_prefix . 'nps_width',
	'label'			=>	__( 'Lettre par ligne', 'nexo' ),
	'description'	=>	__( 'La longueur de chaque ticket varie en fonction de l\'appareil utilisé. 
	Nexo Print Server vous permet de définir une largeur ajustable, qui permettra au contenu de s\'adapter au ticket de caisse. Par défault : 48', 'nexo' )
), 'nps', 2 );

$this->Gui->add_item( array(
	'type'			=>	'select',
	'name'			=>	$option_prefix . 'nps_max_footer_space',
	'label'			=>	__( 'Espace au pied de page', 'nexo', 'nexo' ),
	'options'		=>	[
		'0'		=>	__( 'Aucun espace', 'nexo' ),
		'1'		=>	__( '1', 'nexo' ),
		'2'		=>	__( '2', 'nexo' ),
		'3'		=>	__( '3', 'nexo' ),
		'4'		=>	__( '4', 'nexo' ),
		'5'		=>	__( '5', 'nexo' )
	],
	'description'	=>	__( 'Vous permet de définir un espace au pied de page sur NPS.', 'nexo' )
), 'nps', 2 );

$this->Gui->add_item( array(
	'type'			=>	'select',
	'name'			=>	$option_prefix . 'nps_logo_type',
	'label'			=>	__( 'Type du logo', 'nexo' ),
	'options'		=>	[
		'nps-logo'	=>	__( 'Logo NPS', 'nexo' ),
		'store-name'	=>	__( 'Nom de la boutique', 'nexo' )
	],
	'description'	=>	__( 'Vous permet de choisir d\'utiliser le logo crée sur Nexo Print Server 2.x ou d\'utiliser le nom de la boutique.', 'nexo' )
), 'nps', 2 );

$this->Gui->add_item( array(
	'type'			=>	'text',
	'name'			=>	$option_prefix . 'nps_logo',
	'label'			=>	__( 'Code Court', 'nexo' ),
	'description'	=>	__( 'Le code court est l\'identifiant d\'un logo tel qu\'il est défini sur Nexo Print Server 2.x.', 'nexo' )
), 'nps', 2 );

/**
 * @todo might be added to add
 * a base64 print based
 */
// $this->Gui->add_item( array(
// 	'type'			=>	'select',
// 	'name'			=>	$option_prefix . 'nps_print_base64',
// 	'options'		=>	[
// 		'no'		=>	__( 'Non', 'nexo' ),
// 		'yes'		=>	__( 'Oui', 'nexo' ),
// 	],
// 	'label'			=>	__( 'Convertir Impression en Image', 'nexo' ),
// 	'description'	=>	__( 'Convertir une impression en image vous permet d\'améliorer la compatibilité avec d\'autres langues. Cependant, l\'impression peut être unpeu lente.', 'nexo' )
// ), 'nps', 2 );

$this->Gui->output();
