<script>
const PaymentData   =   {
    rawGateways    :   <?php echo json_encode( $this->config->item( 'nexo_payments_types' ) );?>,
    url     :   {
        payment:    '<?php echo site_url([ 'api', 'nexopos', 'orders', 'payment', '#', store_get_param( '?' ) ]);?>',
        paymentList:    '<?php echo site_url([ 'api', 'nexopos', 'orders', 'payments', '#', store_get_param( '?' ) ]);?>',
    }
}
</script>
<script src="<?php echo js_url( 'nexo' ) . 'sales.payment.js';?>"></script>