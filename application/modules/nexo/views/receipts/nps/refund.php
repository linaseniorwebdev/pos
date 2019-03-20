<?php 

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
            $this->parser->parse_string( store_option( 'refund_receipt_col_1' ), $template , true ),
            $this->parser->parse_string( store_option( 'refund_receipt_col_2' ), $template , true )
        ) as $line ):?>
        <text-line><?php echo nexting( $line );?></text-line>
        <?php endforeach;?>
    </align>
    <line-feed></line-feed>
    <align mode="center">
        <text-line><?php echo nexting([], '-');?></text-line>
        <text-line><?php echo __( 'Ticket de remboursement', 'nexo' );?></text-line>
    </align>
    <align mode="left">
        <?php 
        $subTotal = 0;
        $discountTotal = 0;
        ?>
        <?php foreach( $refund[ 'items' ] as $item ):?>
            <text-line><?php echo nexting([], '-');?></text-line>
            <text-line>
            <?php echo nexting([
                $item[ 'NAME' ] . ' (x' . $item[ 'QUANTITY' ] . ')',
                get_instance()->Nexo_Misc->cmoney_format( $item[ 'TOTAL_PRICE' ] )
            ]);
            ?></text-line>
            <text-line>-> <?php echo $item[ 'STATUS' ] === 'defective' ? __( 'Produit Défectueux', 'nexo' ) : __( 'Produit en bon état', 'nexo' );?></text-line>
            <?php $subTotal     +=  floatval( $item[ 'TOTAL_PRICE' ]);?>
        <?php endforeach;?>
        <?php if ( count( $refund[ 'items' ] ) === 0 ):?>
            <text-line><?php echo nexting([], '-');?></text-line>
            <text-line><?php echo __( 'Remboursement effectuée sans retour de stock', 'nexo' );?></text-line>
        <?php endif;?>
    </align>
    <text>
        <text-line><?php echo nexting([], '-');?></text-line>   
    </text>
    <bold>
        <text-line>
        <?php echo nexting([
            __( 'Total', 'nexo' ),
            get_instance()->Nexo_Misc->cmoney_format( $refund[ 'TOTAL' ] )
        ]);?></text-line>
        <text-line><?php echo nexting([], '-');?></text-line>

    </bold>
    <align mode="center">
        <text-line size="1:1">
        <?php echo sprintf( __( 'Raison : %s', 'nexo' ), $refund[ 'DESCRIPTION' ] );?>
        </text-line>
    </align>
    <text>
        <text-line><?php echo nexting([], '-');?></text-line>
    </text>
    <line-feed></line-feed>
    <line-feed></line-feed>
    <align mode="center">
        <text-line><?php echo xss_clean( $this->parser->parse_string( store_option( 'nexo_bills_notices' ), $template , true ) );?></text-line>
    </align>
    <paper-cut></paper-cut>
</document>