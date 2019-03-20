<?php
use Carbon\Carbon;

if ( ! User::in_group([ 'master', 'store.manager', 'store.demo' ] ) ) {
    return;
}
?>
<div class="container-fluid" ng-controller="dashboardReports">
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h4 style="margin:0" class="box-title"><?php echo sprintf( 
                                __( 'Dernières Ventes : %s à %s', 'nexo' ),
                                Carbon::parse( date_now() )->startOfWeek()->toDateString(),
                                Carbon::parse( date_now() )->endOfWeek()->toDateString()
                            );?></h4>
                            <div class="box-tools pull-right">
                                <button ng-click="getReport(true)" type="button" class="btn btn-box-tool">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <canvas id="dashboard-sales" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h4 style="margin:0" class="box-title"><?php echo sprintf( 
                                __( 'Statistiques de la semaine : %s à %s', 'nexo' ),
                                Carbon::parse( date_now() )->startOfWeek()->toDateString(),
                                Carbon::parse( date_now() )->endOfWeek()->toDateString()
                            );?></h4>
                            <div class="box-tools pull-right">
                                <button ng-click="getReport(true)" type="button" class="btn btn-box-tool">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="nav nav-pills nav-stacked">
                                        <li>
                                            <a href="#">
                                                <?php echo __( 'Payées', 'nexo' );?>
                                                <span class="pull-right text-success">{{ getTotalFor( 'paid' ) | moneyFormat }}</span>
                                            </a>
                                        </li>
                                        <li><a href="#"><?php echo __( 'Partiellement payées', 'nexo' );?> 
                                            <span class="pull-right text-blue">
                                            {{ getTotalFor( 'partially' ) | moneyFormat }}
                                            </span></a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <?php echo __( 'Non payées', 'nexo' );?>
                                                <span class="pull-right text-red">{{ getTotalFor( 'unpaid' ) | moneyFormat }}</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <?php echo __( 'Remboursements', 'nexo' );?>
                                                <span class="pull-right text-red">{{ getTotalFor( 'total_refunds' ) | moneyFormat }}</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <?php echo __( 'Taxes', 'nexo' );?>
                                                <span class="pull-right text-blue">{{ getTotalFor( 'taxes' ) | moneyFormat }}</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="nav nav-pills nav-stacked">
                                        <li>
                                            <a href="#">
                                                <?php echo __( 'Total Complètes', 'nexo' );?>
                                                <span class="pull-right text-blue">{{ getTotalFor( 'paid_nbr' ) }}</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <?php echo __( 'Total Partielles', 'nexo' );?>
                                                <span class="pull-right text-blue">{{ getTotalFor( 'partially_nbr' ) }}</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <?php echo __( 'Totale Impayées', 'nexo' );?>
                                                <span class="pull-right text-blue">{{ getTotalFor( 'unpaid_nbr' ) }}</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <?php echo __( 'Total Remboursement', 'nexo' );?>
                                                <span class="pull-right text-blue">{{ getTotalFor( 'refunds_count' ) }}</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <?php echo __( 'Remises', 'nexo' );?>
                                                <span class="pull-right text-blue">{{ getTotalFor( 'discount' ) | moneyFormat }}</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12"></div>
            </div>
        </div>
    </div>
</div>
<?php 
get_instance()->events->add_action( 'dashboard_footer', function() {
    get_instance()->load->module_view( 'nexo', 'dashboard.charts-script' );
});