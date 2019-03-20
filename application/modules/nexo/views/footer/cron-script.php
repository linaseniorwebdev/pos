<?php
use Carbon\Carbon;

$date       =   Carbon::parse( date_now() );
$cache      =   new CI_Cache( array( 'adapter' => 'file', 'backup' => 'file', 'key_prefix'    =>    'nexo_emailed_reports' ));
$hour       =   store_option( 'submit_order_hour' );

$report_date    =   Carbon::parse( $date->toDateString() . ' ' . $hour );

if( $date->gte( $report_date ) && ! $cache->get( store_prefix() . '_daily_sales_' . $date->day ) ) {
    ?>
    <script>
    jQuery(document).ready( function() {
        $.ajax({
            url     :   `<?php echo site_url([ 'cron', 'reports', 'daily-sales', '?store_id=' . get_store_id() ]);?>`,
            success     :   function() {
                console.log( 'Report has been send' );
            }
        });
    })
    </script>
    <?php
}