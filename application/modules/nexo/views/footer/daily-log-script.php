<?php
use Carbon\Carbon;

/**
 * let's check if the previous daily log has been saved
 * @since 3.12.1
 */
$dailyLogDate    =   store_option( 'daily-log' );
if ( $dailyLogDate != Carbon::parse( date_now() )->subDay(1)->endOfDay()->toDateTimeString() ) {
    ?>
    <script>
    jQuery(document).ready( function() {
        $.ajax({
            url         :   '<?php echo dashboard_url([ 'reports', 'save-daily-log', store_get_param( '?' ) ]);?>',
            success     :   function() {
                console.log( 'cron daily log has been registered' );
            }
        });
    });
    </script>
    <?php
}