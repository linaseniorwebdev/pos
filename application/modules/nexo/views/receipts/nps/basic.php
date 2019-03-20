<?php 
$this->load->model( 'Nexo_Misc' );

echo '<?xml version="1.0" encoding="UTF-8"?>';?>
<document>
    <align mode="center">
        <bold>
            <text-line size="3:3">
            <?php 
            switch( store_option( 'nps_logo_type' ) ) {
                case 'nps-logo': 
                    echo store_option( 'nps_logo' );
                break;
                case 'store-name':
                    echo store_option( 'site_name' );
                break;
            }
            ;?></text-line>
        </bold>
    </align>
    <line-feed></line-feed>
    <align mode="left">
        <?php foreach( buildingLines( 
            $this->parser->parse_string( store_option( 'receipt_col_1' ), $template , true ),
            $this->parser->parse_string( store_option( 'receipt_col_2' ), $template , true )
        ) as $line ):?>
        <text-line><?php echo nexting( $line );?></text-line>
        <?php endforeach;?>
    </align>
    <line-feed></line-feed>
    <text>
        <text-line><?php echo __( 'Produits', 'nexo' );?></text-line>
        <?php 
        $subTotal = 0;
        $discountTotal = 0;
        ?>
        <?php foreach( $items as $item ):?>
            <text-line><?php echo nexting([], '-');?></text-line>

            <?php if ( store_option( 'item_name', 'only_primary' ) === 'only_primary' ):?>
            <text-line>
            <?php echo nexting([
                $item[ 'NAME' ] . ' (x' . $item[ 'QUANTITE' ] . ')',
                get_instance()->Nexo_Misc->cmoney_format( $item[ 'PRIX_TOTAL' ] )
            ]);
            ?></text-line>
            <?php endif;?>

            <?php if ( store_option( 'item_name', 'only_primary' ) === 'only_secondary' ):?>
            <text-line>
            <?php echo nexting([
                $item[ 'ALTERNATIVE_NAME' ],
                get_instance()->Nexo_Misc->cmoney_format( $item[ 'PRIX_TOTAL' ] )
            ]);
            ?></text-line>
            <?php endif;?>

            <?php if ( store_option( 'item_name', 'only_primary' ) === 'use_both' ):?>
            <text-line>
            <?php echo nexting([
                $item[ 'NAME' ] . ' (x' . $item[ 'QUANTITE' ] . ')',
                get_instance()->Nexo_Misc->cmoney_format( $item[ 'PRIX_TOTAL' ] )
            ]);
            ?></text-line>
            <text-line>
            <?php echo nexting([
                $item[ 'ALTERNATIVE_NAME' ],
                ''
            ]);
            ?></text-line>
            <?php endif;?>

            <?php $this->events->do_action( 'nps_after_item_line', $item );?>
            <?php $subTotal     +=  floatval( $item[ 'PRIX_TOTAL' ]);?>
        <?php endforeach;?>
        <?php
        $discount_amount			=	0;
        if( $item[ 'REMISE_TYPE' ] == 'percentage' ) {
            $discount_amount		=	( nexoCartGrossValue( $items ) * floatval( $item[ 'REMISE_PERCENT' ] ) ) / 100;
            // echo $this->Nexo_Misc->cmoney_format( floatval( $discount_amount ) );
        } else if( $item[ 'REMISE_TYPE' ] == 'flat' ) {
            $discount_amount		=	floatval( $item[ 'REMISE' ] );
            // echo $this->Nexo_Misc->cmoney_format( floatval( $discount_amount ) );
        }
        $discountTotal			+=	$discount_amount;
        ?>
    </text>
    <line-feed></line-feed>
    <text>
        <text-line><?php echo nexting([], '*');?></text-line>   
    </text>
    <bold>
        <text-line>
        <?php echo nexting([
            __( 'Sous Total', 'nexo' ),
            get_instance()->Nexo_Misc->cmoney_format( $subTotal )
        ]);?></text-line>
        <text-line><?php echo nexting([], '-');?></text-line>
        <text-line><?php echo nexting([
            __( 'Remises', 'nexo' ),
            get_instance()->Nexo_Misc->cmoney_format( $discountTotal )
        ]);?></text-line>

        <?php if ( $order[ 'GROUP_DISCOUNT' ] != '0' ):?>
        <text-line><?php echo nexting([], '-');?></text-line>
        <text-line><?php echo nexting([
            __( 'Remise du Groupe', 'nexo' ),
            get_instance()->Nexo_Misc->cmoney_format( $order[ 'GROUP_DISCOUNT' ] )
        ]);?></text-line>
        <?php endif;?>
        
        <text-line><?php echo nexting([], '-');?></text-line>

        <?php if( @$tax || store_option( 'nexo_vat_type' ) == 'fixed' ):?>            
            
            <?php if ( @$tax && store_option( 'nexo_vat_type' ) == 'variable' ):?>
                <text-line><?php echo nexting([
                    sprintf( __( '%s (%s%%)', 'nexo' ), $tax[0][ 'NAME' ], $tax[0][ 'RATE' ] ),
                    $this->Nexo_Misc->cmoney_format( $order[ 'TVA' ] )
                ]);?></text-line>
            <?php elseif ( store_option( 'nexo_vat_type' ) == 'fixed' ):?>
                <text-line><?php echo nexting([
                    __( 'TVA', 'nexo') . ' (' . store_option( 'nexo_vat_percent' ) . '%)',
                    $this->Nexo_Misc->cmoney_format( $order[ 'TVA' ] )
                ]);?></text-line>
            <?php endif;?>
            <text-line><?php echo nexting([], '-');?></text-line>

        <?php endif;?>
        <text-line><?php echo nexting([
            __( 'Total', 'nexo' ),
            get_instance()->Nexo_Misc->cmoney_format( $order[ 'TOTAL' ] )
        ]);?></text-line>
        <text-line><?php echo nexting([], '-');?></text-line>

        <?php
        $order_payments         =   $this->Nexo_Misc->order_payments( $order[ 'CODE' ] );
        $payment_types          =   $this->events->apply_filters( 'nexo_payments_types', $this->config->item( 'nexo_payments_types' ) );

        foreach( $order_payments as $payment ) {
            if ( $payment[ 'OPERATION' ] === 'incoming' ):
            ?>
            <text-line><?php echo nexting([
                @$payment_types[ $payment[ 'PAYMENT_TYPE' ] ] == null ? __( 'Type de paiement inconnu', 'nexo' ) : @$payment_types[ $payment[ 'PAYMENT_TYPE' ] ],
                $this->Nexo_Misc->cmoney_format( floatval( $payment[ 'MONTANT' ] ) )
            ]);?></text-line>
            <text-line><?php echo nexting([], '-');?></text-line>
            <?php
            else:
            ?>
            <text-line><?php echo nexting([
                @$payment_types[ $payment[ 'PAYMENT_TYPE' ] ] == null ? __( 'Type de paiement inconnu', 'nexo' ) : @$payment_types[ $payment[ 'PAYMENT_TYPE' ] ],
                $this->Nexo_Misc->cmoney_format( - floatval( $payment[ 'MONTANT' ] ) )
            ]);?></text-line>
            <text-line><?php echo nexting([], '-');?></text-line>
            <?php
            endif;
        }
        ?>
        <?php if ( in_array( $order[ 'TYPE' ], [ 'nexo_order_partially_refunded', 'nexo_order_refunded' ] ) ):?>
        <?php
        /**
         * handling refunds
         */
        $refunds    =   get_instance()->orderModel->order_refunds( $order[ 'ID' ] );
        $totalRefunds   =   array_sum( array_map( function( $refund ) {
            return floatval( $refund[ 'TOTAL' ] );
        }, $refunds ) );
        ?>
        <text-line><?php echo nexting([
            __( 'Remboursement', 'nexo' ),
            $this->Nexo_Misc->cmoney_format( 
                - $totalRefunds
            )
        ]);?></text-line>
        <text-line><?php echo nexting([], '-');?></text-line>
        <?php else:?>
        <?php $totalRefunds     =   0;?>
        <?php endif;?>

        <text-line><?php echo nexting([
            __( 'Somme Perçue', 'nexo' ),
            $this->Nexo_Misc->cmoney_format( 
                ( floatval( $order[ 'SOMME_PERCU' ] ) - $totalRefunds )
            )
        ]);?></text-line>
        <text-line><?php echo nexting([], '-');?></text-line>

        <?php
        $terme        =    floatval( $order[ 'SOMME_PERCU' ] ) >= floatval( $order[ 'TOTAL' ] ) ? __('à rendre :', 'nexo') : __('&Agrave; percevoir :', 'nexo');
        ?>

    </bold>
    <align mode="center">
        <text-line size="2:2">
        <?php echo 
            $terme . ' ' .
            $this->Nexo_Misc->cmoney_format( 
                abs(
                    ( 
                        floatval( $order[ 'TOTAL' ]) -
                        floatval( $order[ 'SOMME_PERCU' ])
                    ) 
                    + floatval( $totalRefunds )
                )
            );
            ;?>
        </text-line>
    </align>
    <text>
        <text-line><?php echo nexting([], '-');?></text-line>
    </text>
    <line-feed></line-feed>
    <align mode="center">
        <text-line><?php echo xss_clean( $this->parser->parse_string( store_option( 'nexo_bills_notices' ), $template , true ) );?></text-line>
    </align>
		<?php 		
		for( $i = 1; $i <= intval( store_option( 'nps_max_footer_space', 0 ) ); $i++ )  {
		echo '<line-feed></line-feed>';
		}?>
    <paper-cut></paper-cut>
</document>
