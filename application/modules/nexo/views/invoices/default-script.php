<?php include_once( MODULESPATH . '/nexo/inc/angular/order-list/filters/money-format.php' );?>
<script>
tendooApp.controller( 'invoiceCTRL', [ '$scope', function( $scope ) {
    $scope.data         =   <?php echo json_encode( $order );?>;
    $scope.shipping     =   <?php echo json_encode( ( array ) @$shipping[0] );?>;
    $scope.billing     =   <?php echo json_encode( ( array ) @$billing[0] );?>;
    $scope.refund       =   <?php echo json_encode( $refunds );?>;
    $scope.totalRefund  =   0;

    $scope.refund.forEach( refund => {
        $scope.totalRefund  +=  parseFloat( refund.TOTAL );
    });
    
    /**
     * Sub Total
     * @param object
     * @return numeric
     */
     $scope.subTotal        =   function( items ) {
        var subTotal       =   0;

        _.each( items, ( item ) => {
            subTotal        +=  ( parseFloat( item.PRIX ) * parseFloat( item.QUANTITE ) );
        });
        return subTotal;
     }
    
    $scope.toRepay          =   function() {
        return ( $scope.total() - $scope.data.order[0].SOMME_PERCU );
    }

    /**
     * Calculate total for the invoice
     * @return int
     */
    $scope.total        =   function(){
        let totalItems          =   parseFloat( $scope.subTotal( $scope.data.products ) );
        let totalShipping       =   parseFloat( $scope.data.order[0].SHIPPING_AMOUNT );
        let VAT                 =   parseFloat( $scope.data.order[0].TVA );
        return ( ( totalItems - $scope.getDiscount() )  +   totalShipping + VAT );
    }

    /**
     * Calculate Discount
     * @return discount
     */
    $scope.getDiscount    =   function(){
        let order       =   $scope.data.order[0];
        if ( $scope.data.order[0].REMISE_TYPE == 'percentage' ) {
            let amount      =   ( parseFloat( order.REMISE_PERCENT ) * parseFloat( order.TOTAL ) ) / 100;
            return amount;
        } else {
            return order.REMISE;
        }
        return 0;
    }
}])
</script>