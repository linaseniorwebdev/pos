<script>
function loadPrinters( select ) {
    if ( select.val() ) {
        $.ajax( select.val() + '/api/printers', {
            success    :   function( result ) {
                $( '[name="ASSIGNED_PRINTER"]' ).html( '<option><?php echo __( 'Choisir une option', 'nexo' );?></option>' );
                result.forEach( printer => {
                    let selected    =  ( printer.name ==  '<?php echo @$register[ 'ASSIGNED_PRINTER' ];?>' ) ? 'selected="selected"' : null;
                    $( '[name="ASSIGNED_PRINTER"]' ).append( `<option ${selected} value="${printer.name}">${printer.name}</option>` );
                });
                NexoAPI.Toast()( `<?php echo __( 'La connection avec le serveur établie', 'nexo' );?>` );
                $( '[name="ASSIGNED_PRINTER"]' ).trigger( "chosen:updated" );
            },
            error   :   function() {
                NexoAPI.Notify().warning(
                    `<?php echo __( 'Une erreur est survenu', 'nexo' );?>`,
                    `<?php echo __( 'Impossible d\'accéder au serveur de l\'imprimante. Assurez-vous que l\'URL mentionnée est correcte. En cas de soucis, contactez l\'assistance.', 'nexo' );?>`,
                );
            }
        })
    }
}
$( document ).ready( function() {
    $( '[name="NPS_URL"]' ).blur( function() {
        loadPrinters( $( this ) );
    });

    loadPrinters( $( '[name="NPS_URL"]' ) );
});
</script>