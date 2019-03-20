<script>
class NexoPOSReset {
    constructor() {
        this.askReset();
    }

    askReset() {
        swal({
            title: '<?php echo _s( 'Confirm Your Action', 'reset' );?>',
            type: 'info',
            html: `<?php echo _s( 'The reset module has been enabled. 
            While it\'s enabled, we would like you to confirm that you want to delete all your data. 
            Also note that if you\'re using <strong>Nexo Updater</strong>, you need to deactivate your licence first before doing that.
            A licence can be deactivated by the administrator only once. For more informations : contact@nexopos.com', 'reset');?>`,
            showCancelButton: true
        }).then( result => {
            if ( result.value ) {
                document.location = '<?php echo current_url() . '?reset-process=confirm';?>';
            } else {
                this.askDisableModule();
            }
        })
    }

    askDisableModule() {
        swal({
            title: '<?php echo _s( 'Disable Reset On Active', 'reset' );?>',
            type: 'info',
            html: `<?php echo _s( 'If you don\'t wish to reset right now. Would you like to disable the Reset On Active module ?', 'reset');?>`,
            showCancelButton: true
        }).then( result => {
            if ( result.value ) {
                document.location = '<?php echo site_url( 'dashboard/modules/disable/reset' );?>';
                return false;
            } else {
                this.askReset();
            }
        })
    }
}
$( document ).ready( function() {
    new NexoPOSReset();
})
</script>