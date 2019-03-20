<script>
const defaultOrderOptionsTabs = [{
	title: '<?php echo __( 'Détails', 'nexo' );?>',
	namespace: 'details',
	active: false,
},{
	title: '<?php echo __( 'Paiements', 'nexo' );?>',
	namespace: 'payment',
	active: false,
},{
	title: '<?php echo __( 'Nouveau remboursement', 'nexo' );?>',
	namespace: 'refund',
	active: false,
}, {
	title: '<?php echo __( 'Historique des remboursements', 'nexo' );?>',
	namespace: 'refund-history',
	active: false,
}];
</script>
<?php include_once( dirname( __FILE__ ) . '/localization.php' );?>
<?php include_once( dirname( __FILE__ ) . '/refund-scripts.php' );?>
<?php include_once( dirname( __FILE__ ) . '/details-scripts.php' );?>
<?php include_once( dirname( __FILE__ ) . '/payment-scripts.php' );?>
<?php include_once( dirname( __FILE__ ) . '/refund-history-scripts.php' );?>
<script src="<?php echo js_url( 'nexo' ) . 'bs4-button-toggle.vue.js';?>"></script>
<script src="<?php echo js_url( 'nexo' ) . 'sales.core.js';?>"></script>
<script src="<?php echo js_url( 'nexo' ) . 'sales-list.js';?>"></script>
<?php if (@$Options[ store_prefix() . 'nexo_enable_stripe' ] != 'no'):?>
<script type="text/javascript" src="https://checkout.stripe.com/checkout.js"></script>
<script type="text/javascript">
	'use strict';
	// Close Checkout on page navigation:
	$(window).on('popstate', function() {
		// alert( 'POP' );
		//get your angular element
		  var elem = angular.element(document.querySelector('[ng-controller="nexo_order_list"]'));

		  //get the injector.
		  var injector = elem.injector();

		  //get the service.
		  // var __stripeCheckout = injector.get( '__stripeCheckout' );

		  //update the service.
		  // __stripeCheckout.handler.close();

		  // elem.scope().$apply();
	});
</script>
<?php endif;?>
<style>
.order-details-container .section-nav {
	border-right: solid 1px #e6e6e6;
}
.order-details-container .section-nav .list-group {
	margin-top: -1px;
    margin-right: -1px;
}
</style>