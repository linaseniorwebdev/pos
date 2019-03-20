<?php
global $Options;
$this->load->config( 'rest' );
use Carbon\Carbon;

include_once( dirname( __FILE__ ) . '/profit-and-lost-vars.php' ); ?>
<div ng-controller="profitAndLosses" ng-cloak>
    <div class="row hidden-print">
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="input-group">
                <span class="input-group-addon"><?php echo __( 'Date de départ', 'nexo_premium' );?></span>
                <input ng-model="startDate" type="text" class="form-control start_date" placeholder="">

            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="input-group">
                <span class="input-group-addon"><?php echo __( 'Date de fin', 'nexo_premium' );?></span>
                <input ng-model="endDate" type="text" class="form-control end_date" placeholder="">
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="btn-group btn-group-md">
                <button type="button" name="name"class="btn btn-default" ng-click="getSales()">
                    <i class="fa fa-refresh"></i>
                    <?php echo __( 'Charger', 'nexo_premium' );?>
                </button>
                <button type="button" print-item=".content-wrapper" name="name"class="btn btn-default" ng-click="printReport()">
                    <i class="fa fa-print"></i>
                    <?php echo __( 'Imprimer', 'nexo_premium' );?>
                </button>
                <button type="button" name="name"class="btn btn-default" ng-click="doExportCSV()">
                    <i class="fa fa-file"></i>
                    <?php echo __( 'Exporter CSV', 'nexo_premium' );?>
                </button>
            </div>
        </div>
    </div>
    <br>

    <table class="table table-bordered table-striped box report_box">
        <thead>
            <tr style="font-weight:600">
                <?php foreach( $table_head as $row ):?>
                    <td width="<?php echo @$row[ 'width' ]; ?>" class="<?php echo @$row[ 'class' ]; ?>">
                      <?php echo @$row[ 'text' ] ?>
                    </td>
                <?php endforeach;?>

            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="item in items | orderBy : 'DATE_CREATION' : false">
                <?php foreach( $table_column as $row ):?>
                    <td class="<?php echo @$row[ 'class' ];?>" <?php echo @$row[ 'attr' ];?>>
                      <?php echo @$row[ 'text' ];?>
                    </td>
                <?php endforeach;?>
            </tr>
            <tr ng-show="items.length == 0" class="hidden-print">
                <td colspan="{{ columnNbr }}" class="text-center">
                    <?php echo __( 'Aucun résultat à afficher. Veuillez choisir un interval de temps différent.', 'nexo_premium' ); ?>
                </td>
            </tr>
            <tr ng-show="items.length > 0">
                <?php foreach( $table_total as $row ):?>
                    <td class="<?php echo @$row[ 'class' ]; ?>" <?php echo @$row[ 'attr' ]; ?> colspan="<?php echo @$row[ 'colspan' ] ?>">
                      <?php echo @$row[ 'text' ];?>
                    </td>
                <?php endforeach;?>
            </tr>
        </tbody>
    </table>
    <style media="print">
    @media print{
        table {
            font-size: 12px;
        }
        h1 {
            font-size: 16px;
            text-align: center;
        }
    }
    </style>
</div>
