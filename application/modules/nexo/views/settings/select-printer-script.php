<script>
$( document ).ready(function(){
    $.ajax( '<?php echo store_option( 'nexo_print_server_url' );?>/api/printers', {
        success    :   function( result ) {
            $( '[name="<?php echo store_prefix();?>nexo_pos_printer"]' ).html( '<option><?php echo __( 'Choisir une option', 'nexo' );?></option>' );
            result.forEach( printer => {
                let selected    =   printer.name ==  '<?php echo store_option( 'nexo_pos_printer' );?>' ? 'selected="selected"' : null;
                $( '[name="<?php echo store_prefix();?>nexo_pos_printer"]' ).append( `<option ${selected} value="${printer.name}">${printer.name}</option>` );
            });
        }
    })
})
</script>