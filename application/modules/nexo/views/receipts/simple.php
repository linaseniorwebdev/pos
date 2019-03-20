<?php
/**
 * Starting Cache
 * Cache should be manually restarted
**/

use Carbon\Carbon;

if (! $order_cache = $cache->get($order[ 'order' ][0][ 'ID' ]) || @$_GET[ 'refresh' ] == 'true') {
    ob_start();
}
?>

<?php if( @$_GET[ 'ignore_header' ] != 'true' ):?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo sprintf(__('Order ID : %s &mdash; Nexo Shop Receipt', 'nexo'), $order[ 'order' ][0][ 'CODE' ]);?></title>
<link rel="stylesheet" media="all" href="<?php echo css_url('nexo') . '/bootstrap.min.css';?>" />
</head>
<style media="screen">
/*@font-face {
    font-family: "My Custom Font";
    src: url(<?php echo module_url( 'nexo' ) . 'fonts/OCRAEXT.ttf';?>) format("truetype");
}
body {
    font-family: "My Custom Font", Verdana, Tahoma;
    p {font-size: 4vw;}
}*/
</style>


<body>
<?php endif;?>


<?php global $Options;?>
<?php if (@$order[ 'order' ][0][ 'CODE' ] != null):?>
<div class="container-fluid">
    <div class="row">
        <div class="well col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="row order-details">
                <div class="col-lg-12 col-xs-12 col-sm-12 col-md-12">
                    <h2 class="text-center"><?php echo @$Options[ store_prefix() . 'site_name' ];?></h2>
                </div>
                <?php ob_start();?>
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                    <?php echo xss_clean( @$Options[ store_prefix() . 'receipt_col_1' ] );?>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 text-right">
                    <?php echo xss_clean( @$Options[ store_prefix() . 'receipt_col_2' ] );?>
                </div>
            </div>
            <?php
            $string_to_parse	=	ob_get_clean();
            echo $this->parser->parse_string( $string_to_parse, $template , true );
            ?>
            <div class="row">
                <div class="text-center">
                    <h3><?php _e('Ticket de caisse', 'nexo');?></h3>
                </div>
                </span>
                <table class="table">
                    <!-- <thead>
                        <tr>
                            <th class="col-md-6 "><?php _e('Produits', 'nexo');?></th>
                            <th class="col-md-2 text-right"><?php _e('Prix', 'nexo');?></th>

                            <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                            <th class="col-md-1 text-right"><?php _e('Remise', 'nexo');?></th>
                            <?php endif;?>
                            <th class="col-md-1 text-right"><?php _e('Total', 'nexo');?></th>
                        </tr>
                    </thead> -->
                    <tbody>
                    	<?php
                        $total_global    =    0;
                        $total_unitaire    =    0;
                        $total_unitaire_brut    =   0;
                        $total_quantite    =    0;
						$total_discount		=	0;

                        $products       =   $this->events->apply_filters( 'receipt_items', $order[ 'products' ] );
                        if( $products ) {
                            foreach ( $products as $_produit) {
                                // $total_global        +=    floatval($_produit[ 'PRIX_TOTAL' ]);
                                $total_unitaire_brut    +=    floatval($_produit[ 'PRIX_BRUT' ]);
                                $total_unitaire      	+=    floatval($_produit[ 'PRIX' ]);
                                $total_quantite   	 	+=    floatval($_produit[ 'QUANTITE' ]);
                                $total_global        	+=    ( floatval($_produit[ 'PRIX_TOTAL' ]) );
                                ?>
                            <tr>
                                <td class="text-left">
                                <?php echo $_produit[ 'QUANTITE' ];?> X 
                                <!-- Dealing with the item name and alternative name -->
                                <?php if ( store_option( 'item_name' ) == 'use_both' ):?>
                                    <?php echo empty( $_produit[ 'DESIGN' ] ) ? $_produit[ 'NAME' ] : $_produit[ 'DESIGN' ];?>
                                    <small>( <?php echo $_produit[ 'ALTERNATIVE_NAME' ];?> )</small>
                                <?php elseif ( store_option( 'item_name' ) == 'only_secondary' ):?>
                                    <?php echo $_produit[ 'ALTERNATIVE_NAME' ];?>
                                <?php else: // use_primary?>
                                    <?php echo empty( $_produit[ 'DESIGN' ] ) ? $_produit[ 'NAME' ] : $_produit[ 'DESIGN' ];?>
                                <?php endif;?>
                                <!-- end -->
                                <br>
                                <?php echo $_produit[ 'CODEBAR' ];?></td>
                                <td class="text-right" width="150">
                                <?php echo $this->Nexo_Misc->cmoney_format( floatval($_produit[ 'PRIX_BRUT' ]) );
                                ?>
                                </td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right" width="180">
                                <?php
                                $discount_amount			=	0;
                                if( $_produit[ 'DISCOUNT_TYPE' ] == 'percentage' ) {
                                    $discount_amount		=	floatval( ( ( floatval( $_produit[ 'PRIX_BRUT' ] ) * intval( $_produit[ 'QUANTITE' ] ) ) * floatval( $_produit[ 'DISCOUNT_PERCENT' ] ) ) / 100 );
                                    echo '- ' . $this->Nexo_Misc->cmoney_format( floatval( $discount_amount ) );
                                } else if( $_produit[ 'DISCOUNT_TYPE' ] == 'flat' ) {
                                    $discount_amount		=	( floatval( $_produit[ 'DISCOUNT_AMOUNT' ] ) * intval( $_produit[ 'QUANTITE' ] ) );
                                    echo '- ' . $this->Nexo_Misc->cmoney_format( floatval( $discount_amount ) );
                                }
    
                                $total_discount			+=	$discount_amount;
    
                                ?> 
                                </td>
                                <?php endif;?>
                                <td class="text-right" width="150">
                                    <?php echo $this->Nexo_Misc->cmoney_format( floatval( $_produit[ 'PRIX_TOTAL' ] ) );?>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                            <tr>
                                <td class="text-left"><?php echo $total_quantite;?></td>
    
                                <td class="text-right">
                                <?php 
                                echo $this->Nexo_Misc->cmoney_format( floatval( $total_unitaire_brut ) );
                                ;?>
                                </td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right">- <?php echo $this->Nexo_Misc->cmoney_format( floatval( $total_discount ) );?></td>
                                <?php endif;?>
                                <td class="text-right">
                                <?php echo $this->Nexo_Misc->cmoney_format(
                                    floatval($total_global)
                                );?>
                                </td>
                            </tr>
                            <?php
                            if( ! empty( $order[ 'order' ][0][ 'SHIPPING_AMOUNT' ] ) ):
                            ?>
                            <tr>
                                <td class="text-left"><?php echo __( 'Livraison', 'nexo' );?></td>
    
                                <td class="text-right">
                                </td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right"></td>
                                <?php endif;?>
                                <td class="text-right">
                                <?php echo $this->Nexo_Misc->cmoney_format(
                                    floatval( $order[ 'order' ][0][ 'SHIPPING_AMOUNT' ] )
                                );?>
                                </td>
                            </tr>
                            <?php endif;?>
                            <?php if (floatval($_produit[ 'RISTOURNE' ])):?>
                            <tr>
                                <td class=""><?php _e('Remise automatique', 'nexo');?></td>
                                <td class="text-right">(-)</td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right"></td>
                                <?php endif;?>
                                <td class="text-right">
                                <?php echo $this->Nexo_Misc->cmoney_format(
                                    floatval($_produit[ 'RISTOURNE' ])
                                );?>
                                </td>
                            </tr>
                            <?php endif;?>
                            <?php if ( $_produit[ 'REMISE_TYPE' ] == 'flat' ):?>
                            <tr>
                                <td class=""><?php _e('Remise expresse', 'nexo');?></td>
                                <td class="text-right">(-)</td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right"></td>
                                <?php endif;?>
                                <td class="text-right">
                                <?php echo $this->Nexo_Misc->cmoney_format(
                                    floatval($_produit[ 'REMISE' ])
                                );?>
                                </td>
                            </tr>
                            <?php endif;?>
                            <?php if ( $_produit[ 'REMISE_TYPE' ] == 'percentage' ):?>
                            <tr>
                                <td class=""><?php _e('Remise (%)', 'nexo');?></td>
                                <td class="text-right">(-)</td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right"></td>
                                <?php endif;?>
                                <td class="text-right">
                                <?php echo $this->Nexo_Misc->cmoney_format(
                                    nexoCartPercentageDiscount( $order[ 'products' ], $_produit )
                                );?>
                                </td>
                            </tr>
                            <?php endif;?>
                            <?php if ( $order[ 'order' ][0][ 'GROUP_DISCOUNT' ] != '0' ):?>
                            <tr>
                                <td class=""><?php _e('Remise de groupe', 'nexo');?></td>
                                <td class="text-right">(-)</td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right"></td>
                                <?php endif;?>
                                <td class="text-right">
                                <?php echo $this->Nexo_Misc->cmoney_format(
                                    floatval( $order[ 'order' ][0][ 'GROUP_DISCOUNT' ] )
                                );?>
                                </td>
                            </tr>
                            <?php endif;?>
                            <?php if (in_array( store_option( 'nexo_vat_type' ),  [ 'fixed', 'variable' ], true )):?>
                            <tr>
                                <td class=""><?php _e('Net Hors Taxe', 'nexo');?></td>
                                <td class="" style="text-align: right">(=)</td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right"></td>
                                <?php endif;?>
                                <td class="text-right">
                                <?php echo $this->Nexo_Misc->cmoney_format(
                                bcsub(
                                    floatval($total_global),
                                    (
                                        floatval(@$_produit[ 'RISTOURNE' ]) +
                                        floatval(@$_produit[ 'RABAIS' ]) +
                                        floatval(@$_produit[ 'REMISE' ]) +
                                        nexoCartPercentageDiscount( $order[ 'products' ], $_produit ) +
                                        floatval(@$_produit[ 'GROUP_DISCOUNT' ])
                                    ), 2
                                ) );?>
                                </td>
                            </tr>
                            <?php if( $tax || store_option( 'nexo_vat_type' ) == 'fixed' ):?>     
                            <tr>
                                <?php if ( $tax && store_option( 'nexo_vat_type' ) == 'variable' ):?>
                                    <td class=""><?php echo sprintf( __( '%s (%s%%)', 'nexo' ), $tax[0][ 'NAME' ], $tax[0][ 'RATE' ] );?></td>
                                <?php elseif ( store_option( 'nexo_vat_type' ) == 'fixed' ):?>
                                    <td class=""><?php _e('TVA', 'nexo');?> (<?php echo @$Options[ store_prefix() . 'nexo_vat_percent' ];?>%)</td>
                                <?php endif;?>
                                <td class="" style="text-align: right">(+)</td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right"></td>
                                <?php endif;?>
                                <td class="text-right">
                                <?php echo $this->Nexo_Misc->cmoney_format(
                                    $_produit[ 'TVA' ]
                                );?>
                                </td>
                            </tr>
                            <?php endif;?>
                            <tr>
                                <td class=""><strong><?php _e('TTC', 'nexo');?></strong></td>
                                <td class="" style="text-align: right">(=)</td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right"></td>
                                <?php endif;?>
                                <td class="text-right">
                                <?php echo $this->Nexo_Misc->cmoney_format( 
                                    bcsub(
                                        floatval($total_global) + floatval($_produit[ 'TVA' ]) + floatval($order[ 'order'][0][ 'SHIPPING_AMOUNT' ]),
                                        (
                                            floatval(@$_produit[ 'RISTOURNE' ]) +
                                            floatval(@$_produit[ 'RABAIS' ]) +
                                            floatval(@$_produit[ 'REMISE' ]) +
                                            nexoCartPercentageDiscount( $order[ 'products' ], $_produit ) +
                                            floatval(@$_produit[ 'GROUP_DISCOUNT' ])
                                        ), 2
                                    )
                                );?>
                                </td>
                            </tr>
                            <?php else:?>
                            <tr>
                                <td class=""><strong><?php _e('Net à Payer', 'nexo');?></strong></td>
                                <td class="" style="text-align: right">(=)</td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right"></td>
                                <?php endif;?>
                                <td class="text-right">
                                <?php echo sprintf(
                                    __('%s %s %s', 'nexo'),
                                    $this->Nexo_Misc->display_currency('before'),
                                    bcsub(
                                        floatval($total_global) + floatval($_produit[ 'TVA' ]) + floatval($order[ 'order'][0][ 'SHIPPING_AMOUNT' ]),
                                        (
                                            floatval(@$_produit[ 'RISTOURNE' ]) +
                                            floatval(@$_produit[ 'RABAIS' ]) +
                                            floatval(@$_produit[ 'REMISE' ]) +
                                            floatval(@$_produit[ 'GROUP_DISCOUNT' ])
                                        ), 2
                                    ),
                                    $this->Nexo_Misc->display_currency('after')
                                );?>
                                </td>
                            </tr>
                            <?php endif;?>
    
                            <?php
                            $order_payments         =   $this->Nexo_Misc->order_payments( $order[ 'order' ][0][ 'CODE' ] );
                            $payment_types          =   $this->config->item( 'nexo_payments_types' );
                            foreach( $order_payments as $payment ) {
                                ?>
                                <tr>
                                    <td class="">
                                        <?php echo @$payment_types[ $payment[ 'PAYMENT_TYPE' ] ] == null ? __( 'Type de paiement inconnu', 'nexo' ) : @$payment_types[ $payment[ 'PAYMENT_TYPE' ] ]; ?></td>
                                    <td class="text-right"></td>
                                    <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                    <td class="text-right"></td>
                                    <?php endif;?>
                                    <td class="text-right">
                                    <?php if ( $payment[ 'OPERATION' ] === 'incoming' ):?>
                                    <?php echo $this->Nexo_Misc->cmoney_format( floatval( $payment[ 'MONTANT' ] ) );?>
                                    <?php else:?>
                                    <?php echo $this->Nexo_Misc->cmoney_format( - floatval( $payment[ 'MONTANT' ] ) );?>
                                    <?php endif;?>                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            
                            <?php if ( in_array( $order[ 'order' ][0][ 'TYPE' ], [ 'nexo_order_partially_refunded', 'nexo_order_refunded' ] ) ):?>                            
                            <?php
                            $this->load->module_model( 'nexo', 'Nexo_Orders_Model', 'orderModel' );
                            $refunds    =   get_instance()->orderModel->order_refunds( $order[ 'order' ][0][ 'ID' ] );
                            $totalRefunds   =   array_sum( array_map( function( $refund ) {
                                return floatval( $refund[ 'TOTAL' ] );
                            }, $refunds ) );
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo __( 'Remboursement', 'nexo' );?></strong>
                                </td>
                                <td class="text-right"></td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right"></td>
                                <?php endif;?>
                                <td class="text-right"><strong><?php echo $this->Nexo_Misc->cmoney_format( - floatval( $totalRefunds ) );?></strong></td>
                            </tr>
                            <?php else:?>
                            <?php $totalRefunds     =   0;?>
                            <?php endif;?>
    
                            <tr>
                                <td class=""><strong><?php _e('Somme Total Perçue', 'nexo');?></strong></td>
                                <td class="text-right"></td>
                                <?php if( @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ):?>
                                <td class="text-right"></td>
                                <?php endif;?>
                                <td class="text-right">
                                <?php echo $this->Nexo_Misc->cmoney_format( 
                                    ( floatval( $order[ 'order' ][0][ 'SOMME_PERCU' ] ) - $totalRefunds )
                                );?>
                                </td>
                            </tr>

                            <?php
                            $terme        =    'nexo_order_comptant'    == $order[ 'order' ][0][ 'TYPE' ] ? __('Solde :', 'nexo') : __('&Agrave; percevoir :', 'nexo');
                            ?>
                            <tr>
                                <td class="text-right" colspan="<?php echo @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ? 3 : 2;?>"><h4><strong><?php echo $terme;?></strong></h4></td>
                                <td class="text-right text-danger"><h4><strong>
                                    <?php
                                    echo $this->Nexo_Misc->cmoney_format( 
                                        abs(
                                            ( 
                                                floatval( $order[ 'order' ][0][ 'TOTAL' ]) -
                                                floatval( $order[ 'order' ][0][ 'SOMME_PERCU' ])
                                            ) 
                                            + floatval( $totalRefunds )
                                        )
                                    );
                                    ;?>
                                </strong>
                                </h4></td>
                            </tr>
                            <?php
                        } else {
                            ?>
                            <tr>
                                <td colspan="<?php echo @$Options[ store_prefix() . 'unit_item_discount_enabled' ] == 'yes' ? 4 : 3;?>">
                                <?php echo __( 'Aucun produit à afficher', 'nexo' );?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php include_once( dirname( __FILE__ ) . '/barcode.php' );?>
				<p class="text-center"><?php echo xss_clean( $this->parser->parse_string( @$Options[ store_prefix() . 'nexo_bills_notices' ], $template , true ) );?></p>
                <div class="container-fluid hideOnPrint">
                    <div class="row hideOnPrint">
                        <div class="col-lg-12">
                        <a href="<?php echo dashboard_url([ 'orders' ]);?>" class="btn btn-success btn-lg btn-block"><?php _e('Revenir à la liste des commandes', 'nexo');?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else:?>
<div class="container-fluid"><?php echo tendoo_error(__('Une erreur s\'est produite durant l\'affichage de ce reçu. La commande concernée semble ne pas être valide ou ne dispose d\'aucun produit.', 'nexo'));?></div>
<div class="container-fluid hideOnPrint">
    <div class="row hideOnPrint">
        <div class="col-lg-12">
        <a href="<?php echo dashboard_url([ 'orders' ]);?>" class="btn btn-success btn-lg btn-block"><?php _e('Revenir à la liste des commandes', 'nexo');?></a>
        </div>
    </div>
</div>
<?php endif;?>
<style media="screen, print">
* {
    text-transform: uppercase;
}
@media print {
	* {
		font-family:"Courier New", Courier, monospace;
        font-weight:800;
        text-transform: uppercase;
	}
	.hideOnPrint {
		display:none !important;
	}
	td, th {font-size: 3.5vw;}
	.order-details, p {
		font-size: 3.7vw;
        font-weight: 800;
	}
	.order-details h2 {
		font-size: 7vw;
        font-weight: 800;
	}
	h3 {
		font-size: 3.5vw;
        font-weight: 800;
	}
	h4 {
		font-size: 3.5vw;
        font-weight: 800;
	}
}
</style>
<?php include( dirname( __FILE__ ) . '/receipt-footer.php' );?>
<?php if( @$_GET[ 'ignore_header' ] != 'true' ):?>
</body>
</html>
<?php endif;?>

<?php
if (! $cache->get($order[ 'order' ][0][ 'ID' ]) || @$_GET[ 'refresh' ] == 'true') {
    $cache->save($order[ 'order' ][0][ 'ID' ], ob_get_contents(), 999999999); // long time
}
