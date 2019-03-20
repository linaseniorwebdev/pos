<?php
use Carbon\Carbon;
use Dompdf\Dompdf;
class ApiNexoReports extends Tendoo_Api
{
    public function monthly_sales()
    {
        $start_date         =    $this->post( 'start_date' );
        $end_date           =    $this->post( 'end_date' );
    }

    /**
     * get the logger cashier report
     * week report
     * @return json
     */
    public function cashierWeekReport( $cashierId )
    {
        $this->load->module_model( 'nexo', 'Nexo_Orders_Model', 'orderModel' );

        $dates      =   [];
        $baseDate   =   Carbon::parse( $this->post( 'startOfWeek' ) );

        for( $day = 0; $day < 7; $day++ ) {
            $currentDay     =   $baseDate->copy()->addDays( $day );
            $dates[]        =   [
                'from'      =>  $currentDay->startOfDay()->toDateTimeString(),
                'to'        =>  $currentDay->endOfDay()->toDateTimeString()
            ];
        }

        $sales      =   [];

        foreach( $dates as $index => $date ) {
            $sales[ $index + 1 ]    =   $this->orderModel->getByCashiers(
                $cashierId,
                $date[ 'from' ],
                $date[ 'to' ]
            );
        }

        return $this->response( compact( 'sales', 'dates' ) );
    }

    /**
     * Load the cashier card details
     * @return json
     */
    public function cashierCard()
    {

    }

    /**
     * Load the cashier history
     * @return json
     */
    public function cashierRegisterHistory()
    {

    }
}