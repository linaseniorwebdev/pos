<script>
    salesRefundData     =   {
        url         :   {
            refund      :   '<?php echo site_url([ 'api', 'nexopos', 'orders', 'refund', '#', store_get_param( '?' ) ]);?>',
        },
        rawPaymentsGateway  :   <?php echo json_encode( $this->config->item( 'nexo_payments_types' ) );?>,
    }
</script>
<script src="<?php echo js_url( 'nexo' ) . 'sales.refund.js';?>"></script>