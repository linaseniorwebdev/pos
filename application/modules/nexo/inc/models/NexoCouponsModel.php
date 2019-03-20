<?php
class NexoCouponsModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get( $id = null ) {

        if ( $id === null ) {
            return $this->db
            ->get( store_prefix() . 'nexo_coupons' )
            ->result_array();
        }

        $coupon     =   $this->db->where( 'ID', $id )
            ->get( store_prefix() . 'nexo_coupons' )
            ->result_array();

        if ( ! empty( $coupon ) ) {
            return $coupon[0];
        }

        return false;
    }

    /**
     * get customers coupons
     * @param int customer id
     * @return array
     */
    public function getCustomerCoupons( $customer_id )
    {
        return $this->db->where( 'REF_CUSTOMER', $customer_id )
            ->get( store_prefix() . 'nexo_coupons' )
            ->result_array();
    }

    /**
     * get customers valid coupons
     * @param int customer id
     * @return array
     */
    public function getCustomerValidCoupons( $customer_id )
    {
        $date       =   $this->db->escape_like_str( date_now() );
        return $this->db->where( 'REF_CUSTOMER', $customer_id )
            ->where( '( ( ( USAGE_LIMIT > 0 && USAGE_COUNT < USAGE_LIMIT ) || ( USAGE_LIMIT = 0 ) ) && EXPIRY_DATE > \'' . $date . '\')' )
            ->get( store_prefix() . 'nexo_coupons' )
            ->result_array();
    }

    public function getSingle( $id )
    {
        $coupon     =   $this->db->where( 'ID', $id )
            ->get( store_prefix() . 'nexo_coupons' )
            ->result_array();

        if ( ! empty( $coupon ) ) {
            return $coupon[0];
        }

        return false;
    }

    /**
     * create a coupon 
     * @param data
     * @return boolean
     */
    public function create( $data )
    {
        extract( $data );
        /**
         * might expose the following data
         * -> CODE
         * -> DESCRIPTION
         * -> DATE_CREATION
         * -> DATE_MOD
         * -> AUTHOR
         * -> DISCOUNT_TYPE
         * -> AMOUNT
         * -> EXPIRY_DATE
         * -> USAGE_COUNT
         * -> INDIVIDUAL_USE
         * -> PRODUCTS_IDS
         * -> EXCLUDE_PRODUCTS_IDS
         * -> USAGE_LIMIT
         * -> USAGE_LIMIT_PER_USER
         * -> LIMIT_USAGE_TO_X_ITEMS
         * -> FREE_SHIPPING
         * -> PRODUCT_CATEGORIES
         * -> EXCLUDE_PRODUCT_CATEGORIES
         * -> EXCLUDE_SALE_ITEMS
         * -> MINIMUM_AMOUNT
         * -> MAXIMUM_AMOUNT
         * -> USED_BY
         * -> REWARDED_CASHIER
         * -> REF_CUSTOMER
         * -> EMAIL_RESTRICTIONS
         */
        $this->db->insert( store_prefix() . 'nexo_coupons', $data );
    }
}