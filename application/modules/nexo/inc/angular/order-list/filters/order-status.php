<?php $this->load->module_config( 'nexo', 'nexo' );?>
<script>
tendooApp.filter( 'orderStatus', [ '__orderStatus', function( __orderStatus ){
	return function( status ) {
		return __orderStatus.func( status );
	}
}]);
</script>