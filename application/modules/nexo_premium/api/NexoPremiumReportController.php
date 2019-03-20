<?php
use Carbon\Carbon;

class NexoPremiumReportController extends Tendoo_Api
{
    public function cashierPerformance()
    {
        $start_date     =   $this->post( 'start_date' );
        $end_date       =   $this->post( 'end_date' );
        $filter         =   $this->post( 'filter' );
        $cashier_id     =   $this->post( 'cashier_id' );
        $CarbonStart    =    Carbon::parse($start_date);
        $CarbonEnd      =    Carbon::parse($end_date);

        if ($filter == 'by-days') {
            if (
                $CarbonStart->lt($CarbonEnd)
                && $CarbonStart->diffInDays($CarbonEnd) >= 7
                && $CarbonStart->diffInMonths($CarbonEnd) < 4 // report can't exceed 3 months
            ) {
                $Dates        =    array();
                $i = 0;

                while ($CarbonStart->toDateTimeString() != $CarbonEnd->copy()->addDay()->toDateTimeString()) {
                    $Dates[ $CarbonStart->toDateTimeString() ]    =    array();
                    $CarbonStart->addDay();
                }

                // Fetching Sales for current cashier
                $cashier_id        =    $this->input->post('cashier_id');

                foreach ($Dates as $date_key => &$content) {
                    if (is_array($cashier_id)) {
                        foreach ($cashier_id as $id) {
                            $this->db->select('*')
                            ->from( store_prefix() . 'nexo_commandes')
                            ->join('aauth_users', store_prefix() . 'nexo_commandes.AUTHOR = aauth_users.id')
                            ->where( store_prefix() . 'nexo_commandes.DATE_CREATION >=', Carbon::parse($date_key)->startOfDay())
                            ->where( store_prefix() . 'nexo_commandes.DATE_CREATION <=', Carbon::parse($date_key)->endOfDay());

                            $this->db->where('aauth_users.id', $id);

                            $query                            =    $this->db->get();
                            $content[ 'cashiers' ][ $id ]    =    $query->result_array();
                        }
                    }
                }


                return $this->response($Dates, 200);
            }

            return $this->response([ 'foo' => 'bar' ], 403);

            return $this->response(array(
                'error'        =>    'insufficient_data'
            ), 200);
        } elseif ($filter == 'by-months') { // doesn't yet support multi id

            if (
                $CarbonStart->lt($CarbonEnd)
                && $CarbonStart->diffInMonths($CarbonEnd) >= 2
                && $CarbonStart->diffInYears($CarbonEnd) < 2 // report can't exceed 3 months
            ) {
                $Dates        =    array();

                while ($CarbonStart->startOfMonth()->toDateTimeString() != $CarbonEnd->copy()->startOfMonth()->addMonth()->toDateTimeString()) {
                    $Dates[ $CarbonStart->startOfMonth()->toDateTimeString() ]    =    array();
                    $CarbonStart->startOfMonth()->addMonth();
                }

                // Fetching Sales for current cashier

                foreach ($Dates as $date_key => &$content) {
                    $this->db->select('*')
                    ->from( store_prefix() . 'nexo_commandes')
                    ->join('aauth_users', store_prefix() . 'nexo_commandes.AUTHOR = aauth_users.id')
                    ->where( store_prefix() . 'nexo_commandes.DATE_CREATION >=', Carbon::parse($date_key)->startOfMonth())
                    ->where( store_prefix() . 'nexo_commandes.DATE_CREATION <=', Carbon::parse($date_key)->endOfMonth())
                    ->where('aauth_users.id', $cashier_id);

                    $query        =    $this->db->get();
                    $content    =    $query->result_array();
                }

                return $this->response($Dates, 200);
            }

            return $this->response(array(
                'error'        =>    'insufficient_data'
            ), 200);
        }
    }

    /**
     * Stock Trancking
     * @return json
     */
    public function stockTracking()
    {
        $startDate      =   $this->post( 'start_date' ) == null ? Carbon::parse( date_now() )->startOfMonth()->toDateTimeString() : Carbon::parse( $this->post( 'start_date' ) )->startOfDay()->toDateTimeString();
        $endDate        =   $this->post( 'end_date' ) == null ? Carbon::parse( date_now() )->endOfMonth()->toDateTimeString() : Carbon::parse( $this->post( 'end_date' ) )->endOfDay()->toDateTimeString();

        $deliveries     =   $this->db
        ->select(
            store_prefix() . 'nexo_articles_stock_flow.BEFORE_QUANTITE,' .
            store_prefix() . 'nexo_articles_stock_flow.AFTER_QUANTITE,' .
            store_prefix() . 'nexo_articles_stock_flow.QUANTITE as QUANTITE,' .
            store_prefix() . 'nexo_articles_stock_flow.TYPE,' .
            store_prefix() . 'nexo_articles_stock_flow.UNIT_PRICE,' .
            store_prefix() . 'nexo_articles_stock_flow.TOTAL_PRICE,' .
            store_prefix() . 'nexo_articles_stock_flow.REF_ARTICLE_BARCODE as BARCODE,' .
            store_prefix() . 'nexo_commandes.CODE as CODE,' .
            store_prefix() . 'nexo_commandes.TOTAL as ORDER_TOTAL,' .
            store_prefix() . 'nexo_categories.NOM as CATEGORY_NAME,' .
            store_prefix() . 'nexo_categories.ID as CATEGORY_ID,' .
            store_prefix() . 'nexo_articles.DESIGN as DESIGN,' .
            store_prefix() . 'nexo_articles.REF_CATEGORIE as REF_CATEGORIE'
        )
        ->where( store_prefix() . 'nexo_articles_stock_flow.DATE_CREATION >=', $startDate )
        ->where( store_prefix() . 'nexo_articles_stock_flow.DATE_CREATION <=', $endDate )
        ->from( store_prefix() . 'nexo_articles_stock_flow' )
        ->join( store_prefix() . 'nexo_articles', store_prefix() . 'nexo_articles.CODEBAR =' . store_prefix() . 'nexo_articles_stock_flow.REF_ARTICLE_BARCODE' )
        ->join( store_prefix() . 'nexo_commandes', store_prefix() . 'nexo_articles_stock_flow.REF_COMMAND_CODE =' . store_prefix() . 'nexo_commandes.CODE', 'left' )
        ->join( store_prefix() . 'nexo_arrivages', store_prefix() . 'nexo_arrivages.ID =' . store_prefix() . 'nexo_articles.REF_SHIPPING', 'left' )
        ->join( store_prefix() . 'nexo_categories', store_prefix() . 'nexo_categories.ID =' . store_prefix() . 'nexo_articles.REF_CATEGORIE', 'left' )
        ->get()
        ->result_array();

        return $this->response( $deliveries );
    }
}