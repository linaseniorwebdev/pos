const RewardsVue    =   new Vue({
    el: '#reward-system-vue',
    data: {
        entries: [],
        search: '',
        textDomain,
        result: {},
        ...rewardData
    },
    mounted() {
        this.getRewardSystem();
    },
    computed: {
        page_numbers() {
            if ( this.result.entries !== undefined ) {
                let number  =   [];
                for( let i = 1; i <= this.result.total_pages; i++ ) {
                    number.push( i );
                }
                return number;
            }
            return [];
        }
    },
    methods: {
        /**
         * Search a specific rewards
         */
        search() {

        },

        /**
         * Redirect to creation UI
         */
        create() {
            document.location = this.url.create;
        },

        /**
         * Load Reward system entries
         * @param {Number} page 
         */
        getRewardSystem( page = 1 ) {
            HttpRequest.get( `${this.url.get.replace( '#', page )}&search=${this.search}` ).then( result => {
                this.result         =   result.data;
                this.entries        =   result.data.entries.map( entry => {
                    entry.selected  =   false;
                    return entry;
                });

                setTimeout( () => {

                    $('.icheck').iCheck({
                        checkboxClass: 'icheckbox_square-blue',
                        radioClass: 'iradio_square-blue',
                        increaseArea: '20%' // optional
                    });

                    $('td .icheck' ).on( 'ifChecked', ( e ) => {
                        const id    =   $( e.currentTarget ).data( 'id' );
                        this.entries[ id ].selected     =   true;
                    });

                    $('td .icheck' ).on( 'ifUnchecked', ( e ) => {
                        const id    =   $( e.currentTarget ).data( 'id' );
                        this.entries[ id ].selected     =   false;
                    });

                    $( '.bulk-check' ).on( 'ifChecked', ( e ) => {
                        $( 'td .icheck' ).iCheck( 'check' );
                        this.entries.forEach( entry => entry.selected = true );
                    });

                    $( '.bulk-check' ).on( 'ifUnchecked', ( e ) => {
                        $( 'td .icheck' ).iCheck( 'uncheck' );
                        this.entries.forEach( entry => entry.selected = false );
                    });

                }, 200 );
            }) 
        },

        deleteSelected() {
            const selected  =   this.entries.filter( entry => entry.selected );

            if ( selected.length === 0 ) {
                return swal({
                    title: this.textDomain.warning,
                    text: this.textDomain.shouldSelectAnEntry
                });
            }

            swal({
                title: this.textDomain.confirmAction,
                text: this.textDomain.deleteSelectedEntries,
                showCancelButton: true
            }).then( action => {
                if ( action.value ) {
                    HttpRequest.post( this.url.bulkDelete, {
                        ids: this.entries
                            .filter( entry => entry.selected )
                            .map( entry => entry.ID )
                    }).then( result => {
                        NexoAPI.Toast()( result.data.message );
                        this.getRewardSystem();
                        $( '.bulk-check' ).iCheck( 'uncheck' );
                    }).catch( error => {
                        console.log( 'an unexpected error occured' );
                    })
                }
            })
        },

        updateField( field, index ) {
            console.log( field );
        },

        /**
         * Delete a single entry
         * @param {Object} entry 
         */
        deleteEntry( entry, index ) {
            swal({
                title: this.textDomain.confirmAction,
                text: this.textDomain.wouldYouDeleteThis,
                showCancelButton: true
            }).then( result => {
                if ( result.value ) {
                    HttpRequest.delete( this.url.delete.replace( '#', entry.ID ) ).then( result => {
                        NexoAPI.Toast()( result.data.message );
                        this.entries.splice( index, 1 );
                    });
                }
            })
        },

        searchTerm() {
            if ( this.search.length !== 0 ) {
                this.getRewardSystem( 1 );
            } else {
                NexoAPI.Toast()( this.textDomain.mustFillSomething );
            }
        }
    }
})