<?php $this->load->module_config( 'nexo', 'nexo' );?>
<script>
tendooApp.filter( 'paymentName', [ '__paymentName', function( __paymentName ){
	return function( name ) {
		return __paymentName.func( name );
	}
}]);
</script>