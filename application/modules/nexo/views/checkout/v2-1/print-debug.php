<?php return;?>
<script>
$( document ).keypress( function(e) {
	if( e.which === 13 ) {
		$.ajax( '<?php echo dashboard_url([ 'local-print', 101 ]);?>', {
			success 	:	function( printResult ) {
				$.ajax( '<?php echo store_option( 'nexo_print_server_url' );?>/api/print', {
					type  	:	'POST',
					data 	:	{
						'content' 	:	printResult,
						'printer'	:	'<?php echo store_option( 'nexo_pos_printer' );?>'
					},
					dataType 	:	'json',
					success 	:	function( result ) {
						console.log( result );
					}
				});
			}
		});
	}
})
</script>