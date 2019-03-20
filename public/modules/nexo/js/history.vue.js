$( document ).ready( function() {
    const HistoryVue    =   new Vue({
        el: '#history-vue',
        data: Object.assign( HistoryVueData, {
            currentPage     :   1,
            result          :   {},
            check           :   false
        }),
        mounted() {
            this.fetchHistory().then( result => {
                $( '#history-vue' ).fadeIn(500);
            })
        },
        watch: {
            check() {
                this.toggleCheck();
            }
        },
        methods: {
            /**
             * Toggle check
             * @return void
             */
            toggleCheck() {
                console.log( this.check );
                this.result.entries.forEach( entry => entry.checked = this.check );
            },

            /**
             * fetch all history
             * @return json
             */
            fetchHistory() {
                return HttpRequest.get( this.url.getAll + '/' + this.currentPage ).then( result => {
                    result.data.entries.forEach( entry => entry.checked = false );
                    this.result     =   result.data;
                })
            },

            /**
             * Delete all selected 
             * entries
             * @return json
             */
            deleteSelected( ids ) {
                swal({
                    title: ids ? this.textDomain.deleteSingleTitle : this.textDomain.deleteSelectedTitle,
                    text: ids ? this.textDomain.deleteSingleText : this.textDomain.deleteSelectedText,
                    showCancelButton: true
                }).then( result => {
                    if( result.value ) {
                        const selected  =   ids || this.result.entries
                            .filter( entry => entry.checked )
                            .map( entry => entry.ID );
                        
                        HttpRequest.post( this.url.deleteSelected, { selected }).then( result => {
                            NexoAPI.Toast()( result.data.message );
                            this.fetchHistory();
                        });
                    }
                });
            },

            /**
             * Delete Single Entry
             * @param object history
             * @return void
             */
            deleteSingle( entry ) {
                const ids   =   [ entry.ID ];
                this.deleteSelected( ids );
            },

            /**
             * Select page
             * @return void
             */
            selectPage( page ) {
                this.currentPage    =   page;
                this.fetchHistory();
            }
        },
        computed: {
            totalPages() {
                if ( Object.values( this.result ).length > 0 ) {
                    return Array.apply(null, { length: this.result.total_pages }).map( Number.call, Number );
                }
                return [];
            },

            canDeleteAll() {
                if ( Object.values( this.result ).length > 0 ) {
                    return this.result.entries
                        .filter( entry => entry.checked === true ).length > 0;
                }
                return false;
            }
        }
    })
});