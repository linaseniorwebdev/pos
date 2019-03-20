$( document ).ready( function() {
    const casherDashboardVue    =   new Vue({
        el : '#cashier-dashboard',
        data    :   Object.assign( cashierDashboardData, {
            isWeekSalesLoading          :   true,
            isRegisterHistoryLoading    : true,
            card    :   {
                totalSales: 0,
                complete: 0,
                unpaid: 0,
                partial: 0
            }
        }),
        methods : {
            /**
             * @return color hex
             */
            getRandomColor() {
                var o = Math.round, r = Math.random, s = 255;
                return 'rgba(' + o(r()*s) + ',' + o(r()*s) + ',' + o(r()*s) + ',' + r().toFixed(1) + ')';
            },
              
            treatDailySales( sales ) {
                this.card.totalSales    =   0;
                this.card.complete      =   0;
                this.card.unpaid        =   0;
                this.card.partial       =   0;
                Object.values( sales ).filter( ( sale, dayOfWeek ) => ( dayOfWeek + 1 ) === this.date.dayOfWeek ).forEach( ( sales, dayOfWeek ) => {
                    this.card.totalSales  =   sales.length;
                    sales.forEach( sale => {
                        if ( sale.TYPE === 'nexo_order_comptant' ) {
                            this.card.complete += parseFloat( sale.TOTAL )
                        } else if ( sale.TYPE === 'nexo_order_devis' ) {
                            this.card.unpaid    +=  parseFloat( sale.TOTAL );
                        } else { // for partial
                            this.card.partial   +=  parseFloat( sale.SOMME_PERCU );
                        }
                    });
                });
            },

            /**
             * Load week sales
             * @return Promise
             */
            loadWeekSales() {
                this.isWeekSalesLoading     =   true;
                return new Promise( ( resolve, reject ) => {
                    HttpRequest.post( `api/nexopos/cashiers/week-sales/${this.cashierId}?store_id=${this.storeId}`, {
                        ...this.date
                    }).then( result => {
                        this.isWeekSalesLoading     =   false;
                        this.treatDailySales( result.data.sales );
                        this.treatWeekSales( result.data.sales );
                        resolve();
                    })
                })
            },

            /**
             * Treat WeekSales
             * @return void
             */
            treatWeekSales( days ) {
                ctx     =   document.getElementById( 'barChart' );

                let totalTurnOver   =   Object.values( days ).map( sales => {
                    return sales.length > 0 ? sales.map( sale => {
                        if ( sale.TYPE === 'nexo_order_comptant' ) {
                            return parseFloat( sale.TOTAL )
                        } else if ( sale.TYPE === 'nexo_order_devis' ) {
                            return parseFloat( sale.TOTAL );
                        } else { // for partial
                            return parseFloat( sale.SOMME_PERCU );
                        }
                    }).reduce( ( total1, total2 ) => {
                        return total1 + total2;
                    }, 0) : 0;
                });
                // .map( turnOver => {
                //     return this.$options.filters.currency( turnOver );
                // });

                console.log( totalTurnOver );
                
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.date.daysOfWeek,
                        datasets: [{
                            label: this.locale.weekSales,
                            data: totalTurnOver,
                            backgroundColor: [
                                'rgba(255,99,132,0.2)',
                                'rgba(54, 162, 235, 0.2)',
                                'rgba(255, 206, 86, 0.2)',
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(153, 102, 255, 0.2)',
                                'rgba(255, 159, 64, 0.2)',
                                'rgba(200, 109, 34, 0.2)'
                            ],
                            borderColor: [
                                'rgba(255,99,132,1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)',
                                'rgba(200, 109, 34, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero:true
                                }
                            }]
                        }
                    }
                });
            },

            loadRegisterHistory() {
                // this.isRegisterHistoryLoading           =   true;
                // return new Promise( ( resolve, reject ) => {
                //     HttpRequest.post( `api/nexopos/cashiers/register-history/${this.cashierId}`, {
                //         ...this.date
                //     }).then( results => {
                //         this.isRegisterHistoryLoading   =   false;
                //         console.log( results );
                //     });
                // })
            }
        },
        mounted() {
            console.log( tendooOptions );
            this.loadWeekSales().then( () => {
                this.loadRegisterHistory();
            })
        }
    })
});
