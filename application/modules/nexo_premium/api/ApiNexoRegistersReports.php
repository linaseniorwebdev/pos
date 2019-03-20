<?php

use Carbon\Carbon;

class ApiNexoRegistersReports extends Tendoo_Api
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * show sessions available within a spefic time range
     * for a selected register and cashier
     * @return json
     */
    public function sessions()
    {
        $now        =   Carbon::parse( date_now() );
        $start      =   Carbon::parse( $this->post( 'start_date' ) );
        $end        =   Carbon::parse( $this->post( 'end_date' ) );
        $dayDiff    =   $start->diffInDays( $end );
        $results    =   [];

        /**
         * check if the register is open
         */
        $this->load->model( 'Nexo_Checkout' );
        $register   =   $this->Nexo_Checkout->get_register( $this->post( 'register' ) );
        if( @$register[0][ 'STATUS' ] === 'opened' ) {
            return $this->response([
                'status'    =>  'failed',
                'message'   =>  __( 'Impossible d\'afficher le rapport pour une caisse actuellement ouverte. Veuillez fermer la caisse avant de continuer.', 'nexo' )
            ]);
        } else if ( empty( $register ) ) {
            return $this->response([
                'status'    =>  'failed',
                'message'   =>  __( 'Impossible d\'afficher le rapport. Nous n\'avons pas été en mesure de localiser la caisse. Veuillez rafraichir le rapport et essayer à nouveau.', 'nexo' )
            ]);
        }


        if( $dayDiff > 31 ) {
            return $this->response([
                'status'    =>  'failed',
                'message'   =>  __( 'Impossible d\'afficher ce rapport sur une durée qui excède 31 jours. Veuillez réduire l\'intervale de temps.', 'nexo' )
            ]);
        }

        for( $i = 0; $i < $dayDiff; $i++ ) {

            $currentDate            =   $start;
            $currentStartDate       =   $currentDate->copy()->startOfDay()->toDateTimeString();
            $currentEndDate         =   $currentDate->copy()->endOfDay()->toDateTimeString();

            $registerActivities     =   $this->db
                ->where( 'DATE_CREATION >=', $currentStartDate )
                ->where( 'DATE_CREATION <=', $currentEndDate )
                ->where( 'AUTHOR', $this->post( 'cashier' ) )
                ->where( 'REF_REGISTER', $this->post( 'register' ) )
                ->get( store_prefix() . 'nexo_registers_activities' )
                ->result_array();
            
            /**
             * If the last activity is not a closing one
             * the we should add at the end of a day a closing activity.
             * This might need to test if an idle has started or if only the register still open
             */

            /**
             * If the first activity is a closing one
             * then we should add at the beginning an opening activity
             * this might need to test if an idle is closed or if only a register is closed
             */
            
            $openingAmount          =   0;
            $closingAmount          =   0;
            $sessionMinutes         =   0;
            $idleMinutes            =   0;

            if ( $registerActivities ) {
                /**
                 * Helps to calculate active and idles
                 */
                $workingTimes   =   [
                    'active'    =>  [],
                    'idle'      =>  []
                ];

                $lastOpenIndex  =   null;
                $lastIdleIndex  =   null;

                foreach( $registerActivities as $index => $activity ) {
                    if ( $activity[ 'TYPE' ] === 'opening' ) {
                        /**
                         * Calculating opening balance
                         */
                        $openingAmount   += floatval( $activity[ 'BALANCE' ] );
                        $workingTimes[ 'active' ][ $index ]     =    [
                            'start'     =>  Carbon::parse( $activity[ 'DATE_CREATION' ] ),
                            'end'       =>  null,
                            'duration'  =>  null
                        ];

                        /**
                         * This index will be used later
                         * to fill the activity end
                         */
                        $lastOpenIndex  =   $index;

                    } else if ( $activity[ 'TYPE' ] === 'closing' ) {
                        /**
                         * A register need to be opened first before
                         * counting the closing balance
                         */
                        if ( $lastOpenIndex !== null ) {
                            /**
                             * Closing Balance
                             */
                            $closingAmount   += floatval( $activity[ 'BALANCE' ] );
                            $workingTimes[ 'active' ][ $lastOpenIndex ][ 'end' ]     =    Carbon::parse( $activity[ 'DATE_CREATION' ] );
                            $lastOpenIndex  =   null;
                        }
                    } else if ( $activity[ 'TYPE' ] === 'idle_starts' ) {
                        $workingTimes[ 'idle' ][ $index ]   =   [
                            'start'     =>  Carbon::parse( $activity[ 'DATE_CREATION' ]),
                            'end'       =>  null,
                            'duration'  =>  null
                        ];
                        $lastIdleIndex  =   $index;
                    } else if ( $activity[ 'TYPE' ] === 'idle_ends' && $lastIdleIndex !== null ) {
                        $workingTimes[ 'idle' ][ $lastIdleIndex ][ 'end' ]  =   Carbon::parse( $activity[ 'DATE_CREATION' ] );
                        $lastIdleIndex  =   null;
                    }
                }

                /**
                 * Let's now calculate the working time
                 */
                foreach( $workingTimes[ 'active' ] as $time ) {
                    $sessionMinutes     +=   $time[ 'start' ]->diffInMinutes( $time[ 'end' ] );
                }

                /**
                 * let's now calculate the idle time
                 */
                foreach( $workingTimes[ 'idle' ] as $time ) {
                    $idleMinutes     +=   $time[ 'start' ]->diffInMinutes( $time[ 'end' ] );
                }
            }            

            /**
             * Drawer Balance
             */
            $drawerBalance      =   $openingAmount + $closingAmount;

            /**
             * Expected Balance
             * counting all orders
             */
            $orders     =   $this->db
                ->where( 'DATE_CREATION >=', $currentStartDate )
                ->where( 'DATE_CREATION <=', $currentEndDate )
                ->where( 'AUTHOR', $this->post( 'cashier' ) )
                ->where( 'REF_REGISTER', $this->post( 'register' ) )
                ->where_in( 'TYPE', [ 'nexo_order_comptant', 'nexo_order_advance', 'nexo_order_refunded', 'nexo_order_partially_refunded' ])
                ->get( store_prefix() . 'nexo_commandes' )
                ->result_array();

            $expectedBalance      =   0;
            if ( $orders ) {
                $expectedBalance  =   array_sum( array_map( function( $order ) {
                    return floatval( $order[ 'TOTAL' ] );
                }, $orders ) ) + $openingAmount;        
            }

            $results[]      =   [
                'date'              =>  $currentDate->toDateTimeString(),
                'start_date'        =>  $currentStartDate,
                'end_date'          =>  $currentEndDate,
                'opening_amount'    =>  $openingAmount,
                'closing_amount'    =>  $closingAmount,
                'drawer_balance'    =>  $drawerBalance,
                'expected_balance'  =>  $expectedBalance,
                'session_minutes'   =>  $sessionMinutes,
                'idle_minutes'      =>  $idleMinutes,
                'working_minutes'   =>  $sessionMinutes - $idleMinutes
            ];

            $start->addDays(1);
        }

        return $this->response( $results );
    }
}