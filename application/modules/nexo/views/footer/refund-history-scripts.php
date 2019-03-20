<script>
const RefundHistoryData     =   {
    url :   {
        refundHistory   :   '<?php echo site_url([ 'api', 'nexopos', 'orders', 'refund-history', '#', store_get_param( '?' ) ]);?>',
        refundReceipt   :   '<?php echo site_url([ 'dashboard', 'nexo', 'orders', 'refund-receipt', '#', store_get_param( '?' ) ]);?>',
    }
};

</script>
<script src="<?php echo js_url( 'nexo' ) . 'sales.refund-history.js';?>"></script>