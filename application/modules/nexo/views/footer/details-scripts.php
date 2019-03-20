<script>
	const SalesListTabs 	=	[{
		title: '<?php echo _s( 'Commande', 'nexo' );?>',
		namespace: 'orders',
		active: true
	}, {
		title: '<?php echo _s( 'Addresses', 'nexo' );?>',
		namespace: 'address',
		active: false
	}];
	const SalesListUrls 		=	{
		order 		:	'<?php echo site_url([ 'api', 'nexopos', 'full-order', '#', store_get_param( '?' ) ]);?>',
		orderState 	:	'<?php echo site_url([ 'api', 'nexopos', 'order-status', '#', store_get_param( '?' ) ]);?>',
		printer 	:	'<?php echo store_option( 'nexo_print_server_url', 'http://localhost:3236' );?>/api/printers',
		printJob 	:	'<?php echo dashboard_url([ 'local-print', '#', store_get_param( '?' ) ]);?>',
		nps 		:	'<?php echo store_option( 'nexo_print_server_url', 'http://localhost:3236' );?>',
	}
	const SalesListOptions 		=	<?php echo json_encode( array_map( function( $value, $key ) {
		return [
			'label'		=>	$value,
			'value'		=>	$key
		];
	}, array_values( $this->config->item( 'nexo_orders_status' ) ), array_keys( $this->config->item( 'nexo_orders_status' ) ) ) );?>;
</script>
<script src="<?php echo js_url( 'nexo' ) . 'sales.details.js';?>"></script>