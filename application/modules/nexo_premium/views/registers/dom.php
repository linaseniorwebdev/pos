<?php use Carbon\Carbon;?>
<div id="registers-sessions">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <div class="input-group date">
                    <div class="input-group-addon">
                    <?php echo __( 'Caissier', 'nexo_premium' );?>
                    <i class="fa fa-user"></i>
                    </div>
                    <select v-model="cashier" class="form-control date-control pull-right">
                        <?php foreach( $users as $user ):?>
                        <option value="<?php echo $user->user_id;?>"><?php echo $user->user_name;?></option>
                        <?php endforeach;?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <div class="input-group date">
                    <div class="input-group-addon">
                    <?php echo __( 'Caisse Enregistreuse', 'nexo_premium' );?>
                    <i class="fa fa-desktop"></i>
                    </div>
                    <select v-model="register" class="form-control date-control pull-right">
                        <?php foreach( $registers as $register ):?>
                        <option value="<?php echo $register[ 'ID' ];?>"><?php echo $register[ 'NAME' ];?></option>
                        <?php endforeach;?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-3">
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
        <div class="col-md-3">
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
        <div class="col-md-12">
            <?php echo tendoo_info( __( 'Ce rapport est expérimentale. Ses données ne devrait être utilisées qu\'a des fins de tests. Vous pouvez donc partager vos avis, en envoyant un email à notre addresse : contact@nexopos.com', 'nexo_premium' ) );?>
            <div class="row">
                <div v-for="card in cards" class="col-md-3">
                    <div class="box">
                        <div class="box-header with-border">
                            <span class="box-heading">{{ card.start_date }}</span>
                            <span class="box-tools">
                                {{ card.end_date }}
                            </span>
                        </div>
                        <div class="box-body no-padding">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <td><?php echo __( 'Opération', 'nexo_premium' );?></td>
                                        <td class="text-right"><?php echo __( 'Détails', 'nexo_premium' );?></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php echo __( 'Durée de session', 'nexo_premium' );?></td>
                                        <td class="text-right">{{ showTime(card.session_minutes) }}</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __( 'Durée d\'inactivitée', 'nexo_premium' );?></td>
                                        <td class="text-right">{{ showTime(card.idle_minutes) }}</td>
                                    </tr>
                                    <tr class="success">
                                        <td><?php echo __( 'Temps de travail', 'nexo_premium' );?></td>
                                        <td class="text-right">{{ showTime(card.working_minutes) }}</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __( 'Montant à l\'ouverture', 'nexo_premium' );?></td>
                                        <td class="text-right">{{ card.opening_amount | currency }}</td>
                                    </tr>
                                    <tr class="success">
                                        <td><?php echo __( 'Montant à la fermeture', 'nexo_premium' );?></td>
                                        <td class="text-right">{{ card.closing_amount | currency }}</td>
                                    </tr>
                                    <tr :class="{ 'info' : card.expected_balance === card.closing_amount, 'danger' : card.expected_balance !== card.closing_amount }">
                                        <td><?php echo __( 'Montant Attendu', 'nexo_premium' );?></td>
                                        <td class="text-right">{{ card.expected_balance | currency }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" v-if="cards.length === 0">
                    <?php echo tendoo_info( __( 'Aucune information à afficher. Veuillez choisir un caissier, une caisse enregistreuse et un intervalle de temps.', 'nexo_premium' ) );?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const RegisterSessionData   =   {
    textDomain  :   {
        error: `<?php echo __( 'Une erreur s\'est produite !', 'nexo_premium' );?>`,
        cashierRegisterRequired: `<?php echo __( 'Vous devez choisir un caissier et une caisse enregistreuse avant d\'afficher le rapport', 'nexo_premium' );?>`
    }
};
</script>
<script src="<?php echo module_url( 'nexo' ) . '/js/vue.currency.filter.js';?>"></script>
<script>
const RegisterSessionVue    =   new Vue({
    el: '#registers-sessions',
    data: Object.assign({
        startDate: '',
        endDate: '',
        register: '',
        cashier: '',
        cards   :   []
    }, RegisterSessionData ),
    mounted() {
        $('#start-date').datepicker({
            format: 'mm/dd/yyyy'
        });

        $('#end-date').datepicker({
            format: 'mm/dd/yyyy'
        });

        $( '.date-control' ).change( function() {
            let attribute   =   $( this ).attr( 'name' );
            Vue.set( RegisterSessionVue, attribute, $( this ).val() );
        })

        $( document ).ready( function() {
            $( '.date-control' ).each( function() {
                $( this ).trigger( 'change' );
            })
        })
    },
    methods: {
        generateReport() {

            if ( this.cashier === '' || this.register === '' ) {
                return swal({
                    position: 'top-end',
                    type: 'error',
                    title: this.textDomain.error,
                    text: this.textDomain.cashierRegisterRequired,
                    showConfirmButton: true,
                    timer: 3000
                });
            }

            HttpRequest.post( 'api/nexopos/reports/registers/sessions<?php echo store_get_param('?');?>' , {
                start_date  :   this.startDate,
                end_date    :   this.endDate,
                cashier     :   this.cashier,
                register    :   this.register
            }).then( result => {
                if ( this.parseResponse( result.data ) ) {
                    this.cards  =   result.data;
                }
            })
        },

        showTime( minutes ) {
            return this.hhmmss( parseInt( minutes ) * 60 );
        },

        parseResponse( response ) {
            if ( response.status !== undefined ) {
                swal({
                    title: this.textDomain.error,
                    text: response.message,
                    type: 'error',
                    showConfirmButton: true
                });
                return false;
            }
            return true;
        },

        pad(num) {
            return ( "0" + num ).slice( -2 );
        },

        hhmmss(secs) {
            var minutes = Math.floor(secs / 60);
            secs        = secs%60;
            var hours   = Math.floor(minutes/60)
            minutes     = minutes%60;
            return this.pad( hours ) + ":" + this.pad( minutes ) + ":" + this.pad( secs );
        }
    }
})
</script>