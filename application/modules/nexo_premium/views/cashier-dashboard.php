<?php 
use Carbon\Carbon;

$CarbonNow  =   Carbon::parse( date_now() );
?>
<div class="container-fluid" id="cashier-dashboard">
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ card.totalSales }}</h3>

                    <p><?php echo __( 'Ventes Journalières', 'nexo_premium' );?></p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
                <!-- <a href="#" class="small-box-footer">More info
                    <i class="fa fa-arrow-circle-right"></i>
                </a> -->
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ card.complete | currency }}
                        <sup style="font-size: 20px"></sup>
                    </h3>

                    <p><?php echo __( 'Commands complètes', 'nexo_premium' );?></p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <!-- <a href="#" class="small-box-footer">More info
                    <i class="fa fa-arrow-circle-right"></i>
                </a> -->
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ card.partial | currency }}</h3>

                    <p><?php echo __( 'Commandes Partielles', 'nexo_premium' );?></p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <!-- <a href="#" class="small-box-footer">More info
                    <i class="fa fa-arrow-circle-right"></i>
                </a> -->
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ card.unpaid | currency }}</h3>

                    <p><?php echo __( 'Commandes Impayées', 'nexo_premium' );?></p>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <!-- <a href="#" class="small-box-footer">More info
                    <i class="fa fa-arrow-circle-right"></i>
                </a> -->
            </div>
        </div>
        <!-- ./col -->
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                <h3 class="box-title"><?php echo __( 'Ventes de la semaine', 'nexo_premium' );?></h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
                </div>
                <div class="box-body">
                    <div class="chart">
                        <canvas id="barChart" style="height:230px"></canvas>
                    </div>
                </div>
                <div v-if="isWeekSalesLoading" class="overlay">
                    <i class="fa fa-refresh fa-spin"></i>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
        <!-- <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                <h3 class="box-title"><?php echo __( 'Historique des caisses', 'nexo_premium' );?></h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
                </div>
                <div class="box-body">
                    <div class="chart">
                        <canvas id="barChart" style="height:230px"></canvas>
                    </div>
                </div>
                <div v-if="isRegisterHistoryLoading" class="overlay">
                    <i class="fa fa-refresh fa-spin"></i>
                </div>
            </div>
        </div> -->
    </div>
</div>
<script>
const cashierDashboardData    =   {
    date: {
        startOfWeek: '<?php echo $CarbonNow->copy()->startOfWeek()->toDateTimeString();?>',
        endofWeek: '<?php echo $CarbonNow->copy()->endOfWeek()->toDateTimeString();?>',
        dayOfWeek: <?php echo $CarbonNow->dayOfWeekIso;?>,
        daysOfWeek: [ 
            '<?php echo __( 'Lundi', 'nexo_premium' );?>',
            '<?php echo __( 'Mardi', 'nexo_premium' );?>',
            '<?php echo __( 'Mercredi', 'nexo_premium' );?>',
            '<?php echo __( 'Jeudi', 'nexo_premium' );?>',
            '<?php echo __( 'Vendredi', 'nexo_premium' );?>',
            '<?php echo __( 'Samedi', 'nexo_premium' );?>',
            '<?php echo __( 'Dimanche', 'nexo_premium' );?>',
        ]
    },
    locale: {
        weekSales : '<?php echo __( 'Ventes de la semaine', 'nexo_premium' );?>'
    },
    cashierId     :   '<?php echo User::id();?>',
    storeId     :   '<?php echo get_store_id();?>'
}
</script>
<script src="<?php echo module_url( 'nexo' ) . 'js/cashier-dashboard.js';?>"></script>
<script src="<?php echo module_url( 'nexo' ) . 'js/vue.currency.filter.js';?>"></script>
