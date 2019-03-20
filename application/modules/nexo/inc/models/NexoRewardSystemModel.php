<?php
use Carbon\Carbon;

class NexoRewardSystemModel extends CI_Model
{
    public function __construct()
    {
        $this->load->helpers( 'url_slug' );
    }
    
    public function create( $data ) 
    {
        extract( $data ); 
        /**
         * expose 
         * -> reward_coupon
         * -> reward_expiration (days)
         * -> reward_name
         * -> reward_target
         * -> reward_rules
         */
        
        $this->db->insert( store_prefix() . 'nexo_rewards_system', [
            'NAME'              =>  $reward_name,
            'DATE_CREATION'     =>  date_now(),
            'DATE_MOD'          =>  date_now(),
            'AUTHOR'            =>  User::id(),
            'REF_COUPON'        =>  $reward_coupon,
            'MAXIMUM_POINT'     =>  $reward_target,
            'COUPON_EXPIRATION' =>  $reward_expiration
        ]);

        $id     =   $this->db->insert_id();

        /**
         * let's now create a reward combinaison.
         */
        foreach( $reward_rules as $rule ) {
            $this->db->insert( store_prefix() . 'nexo_rewards_rules', [
                'AUTHOR'        =>  User::id(),
                'DATE_CREATION' =>  date_now(),
                'REF_REWARD'    =>  $id,
                'PURCHASES'     =>  $rule[ 'required_purchases' ],
                'POINTS'        =>  $rule[ 'reward_points' ]
            ]);
        }

        return true;
    }

    /**
     * Update Reward
     * @param int rewar did
     * @param array Reward to update
     * @return boolean
     */
    public function update( $id, $data ) 
    {
        /**
         * start by deleting existing
         * reward rules
         */
        $this->deleteRewardRules( $id );

        extract( $data ); 
        /**
         * expose 
         * -> reward_coupon
         * -> reward_expiration (days)
         * -> reward_name
         * -> reward_target
         * -> reward_rules
         */
        
        $this->db->where( 'ID', $id )->update( store_prefix() . 'nexo_rewards_system', [
            'NAME'              =>  $reward_name,
            'DATE_MOD'          =>  date_now(),
            'AUTHOR'            =>  User::id(),
            'REF_COUPON'        =>  $reward_coupon,
            'MAXIMUM_POINT'     =>  $reward_target,
            'COUPON_EXPIRATION' =>  $reward_expiration
        ]);

        /**
         * let's now create a reward combinaison.
         */
        foreach( $reward_rules as $rule ) {
            $this->db->insert( store_prefix() . 'nexo_rewards_rules', [
                'AUTHOR'        =>  User::id(),
                'DATE_CREATION' =>  date_now(),
                'REF_REWARD'    =>  $id,
                'PURCHASES'     =>  $rule[ 'required_purchases' ],
                'POINTS'        =>  $rule[ 'reward_points' ]
            ]);
        }

        return true;
    }

    /**
     * return the number of entries available
     * @return number
     */
    public function count_entries()
    {
        return $this->db->count_all_results( store_prefix() . 'nexo_rewards_system' );
    }

    /**
     * delete a rewards alongs with his rules
     * @param int reward id
     * @return boolean;
     */
    public function delete( $id )
    {
        $reward     =   $this->get( $id );

        if ( ! empty( $reward ) ) {
            $this->db->where( 'ID', $id )
                ->delete( store_prefix() . 'nexo_rewards_system' );
            $this->db->where( 'REF_REWARD', $id )
                ->delete( store_prefix() . 'nexo_rewards_rules' );

            return true;
        }
        return false;
    }

    /**
     * delete single rule
     * @param int rule id
     * @return boolean
     */
    public function deleteRules( $id = null )
    {
        if ( $id !== null ) {
            $this->db->where( 'ID', $id );
        }

        $this->db
            ->delete( store_prefix() . 'nexo_rewards_rules' );

        return true;
    }

    public function deleteRewardRules( $id )
    {
        $this->db
            ->where( 'REF_REWARD', $id )
            ->delete( store_prefix() . 'nexo_rewards_rules' );
    }

    /**
     * populate current reward with rules
     * @param array Reward
     * @return array populated reward.
     */
    public function populateRewardRules( $entry )
    {
        $entry[ 'rules' ]   =   $this->getRewardRules( $entry[ 'ID' ] );
        return $entry;
    }

    /**
     * Get reward rules
     * @param int Reward ID
     * @return array rules[]
     */
    public function getRewardRules( $id )
    {
        $rules  =   $this->db->where( 'REF_REWARD', $id )
            ->get( store_prefix() . 'nexo_rewards_rules' )
            ->result_array();

        return $rules;
    }

    /**
     * get a reward or all rewards
     * @param int | null reward identifier or null
     * @return array | false
     */
    public function get( $id = null )
    {
        if ( $id === null ) {
            $entries    =   $this->db->get( store_prefix() . 'nexo_rewards_system' )
                ->result_array();

            foreach( $entries as &$entry ) {
                $entry  =   $this->populateRules( $entry );
            }

            return $entries;
        }

        $reward     =   $this->db->where( 'ID', $id )
            ->get( store_prefix() . 'nexo_rewards_system' )
            ->result_array();
        
        if ( ! empty( $reward ) ) {
            $reward[0]  =   $this->populateRewardRules( $reward[0] );
            return $reward;
        }
        
        return false;
    }

    /**
     * get paginated results
     * @param int page, 
     * @param int limit
     * @return entries[]
     */
    public function getEntries( $per_page, $page )
    {
        $this->load->module_model( 'nexo', 'NexoCouponsModel', 'coupon_model' );

        $page           =   $per_page * ( $page - 1 );
        $searchTerm     =   $this->input->get( 'search' );
        $columns        =   $this->db->list_fields( store_prefix() . 'nexo_rewards_system' );
        
        $this->db->order_by( 'DATE_MOD', 'desc' );

        if ( ! empty( $searchTerm ) ) {
            foreach( $columns as $column ) {
                $this->db->or_like( $column, $searchTerm );
            }
        }

        $results    =   $this->db->limit( $per_page, $page )
            ->get( store_prefix() . 'nexo_rewards_system' )
            ->result_array();

        foreach( $results as &$result ) {
            $result[ 'coupon' ]     =   $this->coupon_model->get( $result[ 'REF_COUPON' ] );
            // $author             =   $this->db-
            $result[ 'author' ]     =   User::get( $result[ 'AUTHOR' ] );
        }

        return $results;
    }

    /**
     * get single entry
     * @param int reward id
     * @param array single reward
     */
    public function getSingle( $id )
    {
        $entry  =   $this->get( $id );
        if ( $entry ) {
            return $entry[0];
        }
        return false;
    }

    /**
     * HAndle reward system if
     * it's enabled
     * @param data configuration
     * @return AsyncResponse
     */
    public function handleRewardIfEnabled( $data )
    {
        if ( store_option( 'nexo_enable_reward_system', 'no' ) === 'yes' ) {
            extract( $data );
            /**
             * expose the following
             * -> customer_id : id
             * -> total : of the current order
             * -> order : nexo_commandes array
             */
            
            /**
             * let's check first if the order 
             * is complete or not. We should not trigger
             * reward for incomplete order
             */
            if ( $order[ 'TYPE' ] !== 'nexo_order_comptant' ) {
                return false;
            }

            $this->load->module_model( 'nexo', 'NexoCustomersModel', 'customer_model' );
            $customer   =   $this->customer_model->getSingle( $customer_id );

            if ( $customer ) {
                $group  =   $this->customer_model->getCustomerGroup( $customer_id );
                
                if ( $group ) {
                    $reward     =   $this->getSingle( $group[ 'REF_REWARD' ] );

                    if ( $reward ) {
                        $applyingRule    =   $this->getApplyingRule( $customer, $reward, $total );
                        
                        if ( $applyingRule ) {
                            
                            /**
                             * let's additionnate the customer points
                             * and compute his points.
                             */
                            $customer[ 'REWARD_POINT_COUNT' ]   =  floatval( $customer[ 'REWARD_POINT_COUNT' ] ) + floatval( $applyingRule[ 'POINTS' ] );

                            if ( $this->deserveReward( $customer, $reward ) ) {
                                $this->issueReward( $customer, $reward );
                            } else {
                                $this->updateCustomerPoints( $customer );
                            }
                        }
                    }

                    return [
                        'status'    =>  'failed',
                        'message'   =>  __( 'Aucun système de récompense n\'est attribué au groupe du client', 'nexo' )
                    ];
                }

                return [
                    'status'    =>  'failed',
                    'message'   =>  __( 'Impossible de récupérer le groupe auquel est assigné le client', 'nexo' )
                ];
            }
            
            return [
                'status'    =>  'failed',
                'message'   =>  __( 'Impossible de trouver le client', 'nexo' )
            ];
        }

        return [
            'status'    =>  'failed',
            'message'   =>  __( 'La gestion de récompense est désactivée', 'nexo' )
        ];
    }

    /**
     * Check if we can compute reward
     * @param array customer
     * @param array reward
     * @return boolean
     */
    public function deserveReward( $customer, $reward )
    {
        if ( floatval( $customer[ 'REWARD_POINT_COUNT' ] ) >= floatval( $reward[ 'MAXIMUM_POINT'] ) ) {
            return true;
        }
        return false;
    }

    /**
     * compute the point for the selected 
     * customers
     * @param array customer
     * @param array reward
     * @param int/float total
     * @return boolean
     */
    public function getApplyingRule( $customer, $reward, $total )
    {
        $rules  =   $reward[ 'rules' ];

        /**
         * let's sort the rules first
         */
        usort( $rules, function( $first, $second ) {
            return floatval( $first[ 'PURCHASES' ] ) <=> floatval( $second[ 'PURCHASES' ] );
        });

        if ( $rules ) {
            $previous   =   0;
            foreach( $rules as $rule ) {
                if ( $total > $previous && floatval( $rule[ 'PURCHASES' ] ) >= $total ) {
                    break;
                }
                $previous   =   floatval( $rule[ 'PURCHASES' ] );
            }

            return $rule ?? false;
        }
        return false;
    }

    /**
     * update customer points
     * @param array customer
     * @param float|int points
     */
    public function updateCustomerPoints( $customer, $points = 0 )
    {
        $this->db->where( 'ID', $customer[ 'ID' ] )
            ->update( store_prefix() . 'nexo_clients', [
                'REWARD_POINT_COUNT'    =>  floatval( $customer[ 'REWARD_POINT_COUNT' ] ) + floatval( $points )
            ]);
    }

    /**
     * issue a reward
     * @param array $customer
     * @param array reward
     * @return void
     */
    public function issueReward( $customer, $reward )
    {
        $this->load->module_model( 'nexo', 'NexoCouponsModel', 'coupon_model' );
        $coupon             =   $this->coupon_model->getSingle( $reward[ 'REF_COUPON' ] );
        $key                =   'coupon_generated_for_' . url_slug( $coupon[ 'CODE' ], [
            'delimited'     =>  '_'
        ]);

        $countChildCoupon    =   store_option( $key, 0 );

        if ( $coupon ) {

            /**
             * set default value for 
             * the generated reward
             */
            $newCoupon  =   [
                'CODE'              =>  $coupon[ 'CODE' ] . zero_fill( intval( $countChildCoupon ), 3 ),
                'DATE_CREATION'     =>  date_now(),
                'AUTHOR'            =>  $coupon[ 'AUTHOR' ],
                'REF_CUSTOMER'      =>  $customer[ 'ID' ],
                'EXPIRY_DATE'       =>  Carbon::parse( date_now() )->addDays( $reward[ 'COUPON_EXPIRATION' ] )->toDateTimeString(),
                'USAGE_COUNT'       =>  0,
                'INDIVIDUAL_USE'    =>  1,
            ];

            $merged     =   array_merge( $coupon, $newCoupon );

            /**
             * this will avoid duplicated entries
             */
            unset( $merged[ 'ID' ] );

            $this->coupon_model->create( $merged );

            set_option( store_prefix() . $key, intval( $countChildCoupon ) + 1 );

            $this->db->where( 'ID', $customer[ 'ID' ] )
                ->update( store_prefix() . 'nexo_clients', [
                    'REWARD_POINT_COUNT'    =>  abs( 
                        floatval( $reward[ 'MAXIMUM_POINT' ] ) - floatval( $customer[ 'REWARD_POINT_COUNT' ] ) 
                    )
                ]);

            /**
             * we might need to dispatch an event
             */
        }
    }

    /**
     * reset coupon count
     * for a specific customers
     * @param customer id
     * @return void
     */
    public function resetRewardCount( $customer_id )
    {
        $this->db->where( 'ID', $customer_id )
            ->update( store_prefix() . 'nexo_clients', [
                'REWARD_PURCHASE_COUNT'     =>  0,
                'REWARD_POINT_COUNT'        =>  0
            ]);
    }
}