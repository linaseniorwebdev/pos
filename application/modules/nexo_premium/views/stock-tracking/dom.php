<?php 
use Carbon\Carbon;
?>
<div id="stock-tracking">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <div class="input-group date">
                    <div class="input-group-addon">
                    <?php echo __( 'Date de départ', 'nexo_premium' );?>
                    <i class="fa fa-calendar"></i>
                    </div>
                    <input value="<?php echo Carbon::parse( date_now() )->startOfMonth()->toDateString();?>" type="text" name="startDate" class="form-control date-control pull-right" id="start-date">
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="input-group date">
                    <div class="input-group-addon">
                        <?php echo __( 'Date de fin', 'nexo_premium' );?>
                        <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" value="<?php echo Carbon::parse( date_now() )->endOfMonth()->toDateString();?>" name="endDate" class="form-control date-control pull-right" id="end-date">
                    <div class="input-group-btn hidden-print">
                        <button @click="generateReport()" class="btn btn-primary">
                            <?php echo __( 'Générer le rapport', 'nexo_premium' );?></button>
                        <button print-item="#stock-tracking" class="btn btn-default">
                            <i class="fa fa-print"></i>
                            <?php echo __( 'Imprimer', 'nexo_premium' );?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php echo tendoo_info( __( 'Ce rapport vous permet d\'afficher le flux du stock entrant et sortant pendant une période précise avec la valeur de celle-ci.', 'nexo_premium' ) );?>
            <div class="box">
                <div class="box-header with-border"><?php echo __( 'Suivi de l\'inventaire par catégorie', 'nexo_premium' );?></div>
                <div class="box-body no-padding">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <td><?php echo __( 'Categorie', 'nexo_premium' );?></td>
                                <td><?php echo __( 'Stock Entrant', 'nexo_premium' );?></td>
                                <td><?php echo __( 'Valeur Stock Entrant ', 'nexo_premium' );?></td>
                                <td><?php echo __( 'Stock Sortant', 'nexo_premium' );?></td>
                                <td><?php echo __( 'Valeur Stock Sortant', 'nexo_premium' );?></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="category in categories">
                                <td>{{ category.NOM }}</td>
                                <td>{{ sumEntries( category ) }}</td>
                                <td>{{ sumEntriesValue( category ) | currency }}</td>
                                <td>{{ sumOuting( category ) }}</td>
                                <td>{{ sumOutingValues( category ) | currency }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="overlay" v-if="isLoading">
                    <i class="fa fa-refresh fa-spin"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo module_url( 'nexo' ) . '/js/vue.currency.filter.js';?>"></script>
<script>
const url = {
    categories: '<?php echo site_url([ 'api', 'nexopos', 'categories' ]);?>'
}
</script>
<script>
const StockTracking    =   new Vue({
    el: '#stock-tracking', 
    data: { 
        url,
        rawCategories   :   [],
        rawItems        :   [],
        startDate: '',
        isLoading: true,
        endDate: ''
    },
    mounted() {

        $('#start-date').datepicker({
            format: 'mm/dd/yyyy'
        });

        $('#end-date').datepicker({
            format: 'mm/dd/yyyy'
        });

        $( '.date-control' ).change( function() {
            let attribute   =   $( this ).attr( 'name' );
            Vue.set(StockTracking, attribute, $( this ).val() );
        })

        $( document ).ready( function() {
            $( '.date-control' ).each( function() {
                $( this ).trigger( 'change' );
            })
        })
        
        this.loadCategories();
    },
    computed: {
        categories() {
            return this.rawCategories;
        }
    },
    methods: {
        sumEntries( category ) {
            if ( this.rawItems.length > 0 ) {
                return this.rawItems.filter( item => item.CATEGORY_ID === category.ID && item.TYPE === 'supply' )
                    .map( item => item.QUANTITE )
                    .reduce( ( a, b ) => parseFloat( a ) + parseFloat( b ), 0 );
            }
            return 0;
        },

        sumEntriesValue( category ) {
            if ( this.rawItems.length > 0 ) {
                return this.rawItems.filter( item => item.CATEGORY_ID === category.ID && item.TYPE === 'supply' )
                    .map( item => item.TOTAL_PRICE )
                    .reduce( ( a, b ) => parseFloat( a ) + parseFloat( b ), 0 );
            }
            return 0;
        },

        sumOuting( category ) {
            if ( this.rawItems.length > 0 ) {
                return this.rawItems.filter( item => item.CATEGORY_ID === category.ID && item.TYPE === 'sale' )
                    .map( item => item.QUANTITE )
                    .reduce( ( a, b ) => parseFloat( a ) + parseFloat( b ), 0 );
            }
            return 0;
        },

        sumOutingValues( category ) {
            if ( this.rawItems.length > 0 ) {
                return this.rawItems.filter( item => item.CATEGORY_ID === category.ID && item.TYPE === 'sale' )
                    .map( item => item.TOTAL_PRICE )
                    .reduce( ( a, b ) => parseFloat( a ) + parseFloat( b ), 0 );
            }
            return 0;
        },

        loadCategories() {
            console.log( this.url.categories );
            HttpRequest.get( 'api/nexopos/categories<?php echo store_get_param('?');?>' ).then( result => {
                this.rawCategories  =   result.data;
                this.isLoading      =   false;
            }).catch( err => {
                console.log( err );
                this.isLoading      =   false;
            })
        },

        generateReport() {
            this.isLoading      =   true;
            HttpRequest.post( 'api/nexopos/raw_stock_tracking<?php echo store_get_param('?');?>', {
                start_date : this.startDate,
                end_date: this.endDate
            }).then( result => {
                this.isLoading  =   false;
                this.rawItems   =   result.data;
            }).catch( err => {
                this.isLoading  =   false;
            })
        }
    }
})
</script>