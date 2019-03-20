<script>
    const DailyRportVue     =   new Vue({
        el: '#daily-report',
        data: {

        },
        mounted() {
            // this.boot();
        },
        methods : {
            boot() {
                $( '.sale_report_field' ).datepicker();
            }
        }
    })
</script>