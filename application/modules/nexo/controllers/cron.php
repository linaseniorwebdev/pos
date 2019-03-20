<?php
use Carbon\Carbon;
use Dompdf\Dompdf;

class NexoCron extends Tendoo_Module
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model( 'Nexo_Misc' );
    }

    /**
     * Send by email
     * @return void
     */
    public function sendByEmail()
    {
        $email      =   filter_var( store_option( 'admin_email_for_reports' ), FILTER_SANITIZE_EMAIL );
        $report     =   store_option( 'email_reports_digest' );
        $hour       =   store_option( 'submit_order_hour' );
        $date       =   Carbon::parse( date_now() );
        $cache      =   new CI_Cache( array( 'adapter' => 'file', 'backup' => 'file', 'key_prefix'    =>    'nexo_emailed_reports' ));

        $report_date    =   Carbon::parse( $date->toDateString() . ' ' . $hour );

        if( $date->lt( $report_date ) ) {
            echo json_encode([
                'status'    =>  'failed',
                'message'   =>  'timespan has not yet been reached.'
            ]);
            return;
        }

        if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            echo json_encode([
                'status'    =>  'failed',
                'message'   =>  'invalid email provided.'
            ]);
            return;
        }

        if ( ! in_array( $report, [ 'detailed_sales' ] ) ) {
            echo json_encode([
                'status'    =>  'failed',
                'message'   =>  'invalid report provided.'
            ]);
            return;
        }

        /**
         * if the date is valid and if the report has not yet been send
         */
        if ( preg_match( '/([0-9]){1,2}:([0-9]){1,2}/', $hour ) && ! $cache->get( store_prefix() . '_daily_sales_' . $date->day ) || $this->input->get( 'force' ) == 'true' ) {
            
            /**
             * Saving the cache for one day
             */
            $cache->save( store_prefix() . '_daily_sales_' . $date->day, true, 86400 );

            $date   =   date_now();

            $dompdf = new Dompdf();
            $dompdf->loadHtml( $this->load->module_view( 'nexo', 'emails.daily-sales', null, true ) );
            $dompdf->render();
            $dompdf->setPaper( 'A4', 'landscape' );

            /**
             * Output the report
             */
            $pdf_path   =   UPLOADPATH . 'daily-report.pdf';
            $output     =   $dompdf->output();
            file_put_contents( $pdf_path, $output );

            $this->load->library('email');
            $this->email->from( 'notifications@nexopos.com', store_option( 'site_name' ) );
            $this->email->to( $email );
            $this->email->subject( sprintf( __( 'Rapport Journalier : %s', 'nexo' ), Carbon::parse( $date )->toDateString() ) );
            $this->email->message( sprintf( __( 'Bonjour, est attaché à cet email le rapport des ventes journalières pour le jour de %s', 'nexo' ), $date ) );
            $this->email->attach( $pdf_path );
            $this->email->send();
        } else {
            echo json_encode([
                'status'    =>  'failed',
                'message'   =>  'invalid time provided or the report has yet been send.'
            ]);
            return;
        }
    }
}