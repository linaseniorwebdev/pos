const RewardCreateVue   =   new Vue({
    el: "#reward-create-vue",
    data: {
        reward_name         :   '',
        reward_coupon       :   '',
        reward_target       :   '',
        add_rules           :   false,
        isSubmitting        :   false,
        rules               :   [],
        reward_expiration   :   1,
        ...RewardCreateVueData
    },
    mounted() {
        if ( this.reward !== null ) {
            this.reward_name        =   this.reward.NAME;
            this.reward_coupon      =   this.reward.REF_COUPON;
            this.reward_target      =   this.reward.MAXIMUM_POINT;
            this.reward_expiration  =   this.reward.COUPON_EXPIRATION;
            this.rules              =   this.reward.rules.map( rule => {
                return {
                    required_purchases: parseInt( rule.PURCHASES ),
                    id: rule.ID,
                    reward_points: parseInt( rule.POINTS )
                }
            });
        }
    },
    methods: {
        /**
         * add a specific rule
         * @return void
         */
        addRule() {
            this.rules.push({
                required_purchases: 1,
                reward_points: 1
            });
        },

        /**
         * Delete a specific rule
         * @param {Number} index 
         * @return void
         */
        deleteRewardRule( index, rule ) {
            // if ( rule.id !== undefined ) {
            //     swal({
            //         title: this.textDomain.confirmAction,
            //         text: this.textDomain.confirmMessage,
            //         showCancelButton: true
            //     }).then( result => {
            //         if ( result.value ) {
            //             HttpRequest.delete( this.url.delete.replace( '#', rule.id ) ).then( result => {
            //                 NexoAPI.Toast()( result.data.message );
            //                 this.rules.splice( index, 1 );
            //             })
            //         }
            //     })
            // } else {
            // }            
            this.rules.splice( index, 1 );
        },

        saveReward() {

            if ( this.rules.length === 0 ) {
                return swal({
                    title: this.textDomain.errorOccured,
                    text: this.textDomain.rulesMissing,
                    type: 'error'
                })
            }

            let hasError    =   false;
            this.rules.forEach( (rule, index ) => {
                this.rules.forEach( (__rule, __index ) => {
                    if ( ( rule.required_purchases === __rule.required_purchases || rule.reward_points === __rule.reward_points ) && ( index != __index ) ) {
                        hasError        =   true;
                    }
                });
            });

            if ( hasError ) {
                return swal({
                    title: this.textDomain.errorOccured,
                    type: 'error',
                    text: this.textDomain.hasConflictedRules
                });
            }

            let hasEmptyField   =   false;

            [ 
                'reward_name',
                'reward_coupon',
                'reward_target',
            ].forEach( field => {
                if ( this[ field ] === '' ) {
                    hasEmptyField   =   true;
                }
            });

            if ( hasEmptyField ) {
                return swal({
                    title: this.textDomain.errorOccured,
                    type: 'error',
                    text: this.textDomain.hasEmptyField
                });
            }

            /**
             * we can peacefully submit the 
             * form to the server
             */
            this.isSubmitting   =   true;

            const isEdit    =   this.reward !== null && Object.keys( this.reward ).length > 0;

            /**
             * detect if we're creating or editing.
             * detect works for the URL as well
             */
            HttpRequest[ isEdit ? 'put': 'post' ]( isEdit ? this.url.put.replace( '#', this.reward.ID ) : this.url.post, {
                reward_name: this.reward_name,
                reward_coupon: this.reward_coupon,
                reward_target: this.reward_target,
                reward_rules: this.rules,
                reward_expiration: this.reward_expiration
            }).then( result => {
                NexoAPI.Toast()( result.data.message );
                setTimeout( () => {
                    document.location   =   this.url.list;
                }, 1000 );
            }).catch( error => {
                this.isSubmitting   =   false;
            });
        }
    }
})