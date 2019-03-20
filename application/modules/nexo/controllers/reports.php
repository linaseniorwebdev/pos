<?php
use Carbon\Carbon;

class NexoReportsController extends CI_Model
{
    public function index()
    {
        $this->journalier();
    }

    // @remove
    public function journalier($start_date = null, $end_date = null)
    {
        if (! User::can('nexo.read.annual-sales' ) ) {
            return nexo_access_denied();
        }

        global $Options;

        switch (@$Options[ 'site_language' ]) {
            case 'fr_FR'    :    $lang    = 'fr'; break;
            default        :    $lang    = 'en'; break;
        }

        Carbon::setLocale($lang);

        $this->cache        =    new CI_Cache(array('adapter' => 'file', 'backup' => 'file', 'key_prefix'    =>    'nexo_daily_reports_' . store_prefix() ));

        if ($start_date == null && $end_date == null) {

            // Start Date
            $CarbonStart    =    Carbon::parse(date_now())->startOfMonth();

            // End Date
            $CarbonEnd        =    Carbon::parse(date_now())->endOfMonth();

            // Is Date valid
            $DateIsValid    =    $CarbonStart->lt($CarbonEnd);

            // Default date
            $start_date        =    $CarbonStart->toDateString();
            $end_date        =    $CarbonEnd->toDateString();
        } else {

            // Start Date
            $CarbonStart    =    Carbon::parse($start_date);

            // End Date
            $CarbonEnd        =    Carbon::parse($end_date);

            // Is Date valid
            $DateIsValid    =    $CarbonStart->lt($CarbonEnd);
        }

        $data                =    array(
            'report_slug'    =>     'from-' . $start_date . '-to-' . $end_date
        );

        if (! $DateIsValid) {
            show_error(sprintf(__('Le rapport ne peut être affiché, la date spécifiée est incorrecte', 'nexo')));
        }

        if ($CarbonStart->diffInMonths($CarbonEnd) > 999) {
            show_error(sprintf(__('Le rapport ne peut être affiché, l\'intervale de date ne peut excéder 3 mois.', 'nexo')));
        }

        $this->load->model('Nexo_Misc');

        $this->enqueue->js('../modules/nexo/bower_components/chart.js/Chart.min');
        $this->enqueue->js('../modules/nexo/bower_components/moment/min/moment.min');
        $this->enqueue->js('../modules/nexo/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min');
        $this->enqueue->css('../modules/nexo/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min');

        $this->Gui->set_title( store_title( __('Rapport des ventes journalières', 'nexo') ) );

        $data[ 'start_date' ]    =    $CarbonStart->toDateString();
        $data[ 'end_date' ]        =    $CarbonEnd->toDateString();
        $data[ 'CarbonStart' ]    =    $CarbonStart;
        $data[ 'CarbonEnd' ]    =    $CarbonEnd;
        $data[ 'Cache' ]        =    $this->cache;

        $this->load->view("../modules/nexo/views/reports/daily.php", $data);
    }

    /**
     * save daily log for the previous date
     * @return void
     */
    public function saveDailyLog()
    {
        $this->load->module_model( 'nexo', 'Nexo_Orders_Model', 'orderModel' );

        $date           =   $this->input->get( 'date' );

        $startOfDay     =   Carbon::parse( $date == null ? date_now() : $date )
            ->subDay( $date == null ? 1 : 0 )
            ->startOfDay()->toDateTimeString();
        $endOfDay       =   Carbon::parse( $date == null ? date_now() : $date )
            ->subDay( $date == null ? 1 : 0 )
            ->endOfDay()->toDateTimeString();

        $sales  =   $this->db->where( 'DATE_CREATION >=', $startOfDay )
            ->where( 'DATE_CREATION <=', $endOfDay )
            ->get( store_prefix() . 'nexo_commandes' )
            ->result_array();

        /**
         * let's count the refund made for each orders
         * and save it along with the fetched orders
         */
        foreach( $sales as $index => $sale ) {
            $refunds     =   $this->orderModel->order_refunds( $sale[ 'ID' ] );
            if ( $refunds ) {
                $sales[ $index ][ 'refunds' ]  =   $refunds;
            }
        }

        $total              =   0;
        $taxes              =   0;
        $discount           =   0;
        $unpaid             =   0;
        $total_refunds      =   0;
        $partial            =   0;
        $paid               =   0;
        $total_unpaid       =   0;
        $total_paid         =   0;
        $total_partially    =   0;
        $refunds_count      =   0;
        $carbon             =   Carbon::parse( $date );
        $day_of_week        =   $carbon->dayOfWeek;

        foreach( $sales as $sale ) {
            $total  +=  floatval( $sale[ 'TOTAL' ] );
            $taxes  +=  floatval( $sale[ 'TVA' ]);

            if ( $sale[ 'TYPE' ] == 'nexo_order_devis' ) {
                $unpaid++;
                $total_unpaid   +=  floatval( $sale[ 'TOTAL' ] );
            }

            if ( $sale[ 'TYPE' ] == 'nexo_order_comptant' ) {
                $total_paid   +=  floatval( $sale[ 'TOTAL' ] );
                $paid++;
            }

            if ( $sale[ 'TYPE' ] == 'nexo_order_advance' ) {
                $total_partially   +=  floatval( $sale[ 'TOTAL' ] );
                $partial++;
            }

            if ( isset( $sale[ 'refunds' ]) ) {
                /**
                 * let's count the total refund
                 * and calculate the total
                 */
                $totalRefunds   =   count( $sale[ 'refunds' ] );
                if ( $totalRefunds > 0 ) {
                    $total_refunds   +=  floatval( @$sale[ 'TOTAL_REFUND' ] );
                    $refunds_count  +=  $totalRefunds;
                }
            }

            if( $sale[ 'REMISE_TYPE' ] === 'percentage' ) {
                $percentage     =   ( floatval( $sale[ 'TOTAL' ] ) * floatval( $sale[ 'REMISE_PERCENT' ] ) ) / 100;
                $discount       +=  $percentage;
            } else {
                $discount       +=  (
                    floatval( $sale[ 'REMISE' ] ) + 
                    floatval( $sale[ 'RABAIS' ] ) + 
                    floatval( $sale[ 'RISTOURNE' ] )
                );
            }
        }

        /**
         * Check if a report already exists
         */
        $getEntries  =   $this->db->where( 'DATE_CREATION', $endOfDay )
            ->get( store_prefix() . 'nexo_daily_log' )
            ->result_array();
        
        /**
         * Creating Daily Log
         */
        $dailyLog   =   [
            'unpaid_nbr'        =>  $unpaid,
            'partially_nbr'     =>  $partial,
            'paid_nbr'          =>  $paid,
            'refunds_count'     =>  $refunds_count,
            'total_discount'    =>  $discount,
            'total_taxes'       =>  $taxes,
            'total_sales'       =>  $total,
            'total_paid'        =>  $total_paid,
            'total_partially'   =>  $total_partially,
            'total_unpaid'      =>  $total_unpaid,
            'total_refunds'     =>  $total_refunds,
            'day_of_week'       =>  $day_of_week,
            'date'              =>  $carbon->toDateTimeString()
        ];

        /**
         * if a report doesn't exist
         * and the refresh param isn't provided
         */
        if ( ! $getEntries && ! @$_GET[ 'refresh' ] === 'true' ) {
            $this->db->insert( store_prefix() . 'nexo_daily_log', [
                'JSON'   =>  json_encode( $dailyLog ),
                'DATE_CREATION'     =>  $endOfDay
            ]);

            set_option( store_prefix() . 'daily-log', $endOfDay );
            
            echo json_encode( $dailyLog );
            return;
        } else {
            $this->db->where( 'DATE_CREATION', $endOfDay )
                ->update( store_prefix() . 'nexo_daily_log', [
                'JSON'   =>  json_encode( $dailyLog )
            ]);
            echo json_encode( $dailyLog );
            return;
        }
    }

    /**
     * get daily log
     */
    public function getDailyLog()
    {
        $startOfWeek    =   $this->input->get( 'start_date' );
        $endOfWeek      =   $this->input->get( 'end_date' );

        if ( $startOfWeek == null ) {
            $startOfWeek    = Carbon::parse( date_now() )
            ->startOfWeek()
            ->startOfDay()
            ->toDateTimeString();
        }

        if ( $endOfWeek == null ) {
            $endOfWeek      =   Carbon::parse( date_now() )
            ->endOfWeek()
            ->endOfDay()
            ->toDateTimeString();
        }

        $results    =   $this->db->where( 'DATE_CREATION >=', $startOfWeek )
            ->where( 'DATE_CREATION <=', $endOfWeek )
            ->limit(7)
            ->get( store_prefix() . 'nexo_daily_log' )
            ->result_array();
        
        $json   =   array_map(function( &$input ) {
            return json_decode( $input[ 'JSON' ], true );
        }, $results );
        
        echo json_encode( $json );
    }
}