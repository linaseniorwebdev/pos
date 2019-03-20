﻿<?php if( User::in_group( 'store.manager' ) || User::in_group( 'master' ) || User::in_group( 'store.demo' ) ):?>
<br /> 
<div class="container-fluid">
    <div class="row">
    	<div class="col-md-3 col-sm-6 col-xs-12">
        	<div class="small-box bg-green">
                <div class="inner">
                <h4><span class="fa fa-line-chart"></span> <?php _e('Ventes réalisées', 'nexo_premium');?>
                </h4> 
                <h5><?php _e('Aujourd\'hui', 'nexo_premium');?>
                	<span class="pull-right">
                    	<?php echo nexo_compare_card_values($Cache->get( 'sales_number_today'), $Cache->get( 'sales_number_yesterday'));?>
                        
                        <?php echo $Cache->get( 'sales_number_today');?>
                    </span>
                </h5>
                <h5><?php _e('Hier', 'nexo_premium');?>
                	<span class="pull-right">
                        <?php echo $Cache->get( 'sales_number_yesterday');?>
                    </span>
                </h5>
                <h5><?php _e('Total', 'nexo_premium');?>
                	<span class="pull-right">
                    	<?php echo $Cache->get( 'sales_number');?>
                    </span>
                </h5>
                </div>
                <div class="icon">
                  <i class="ion ion-stats-bars"></i>
                </div>
                <a href="<?php echo dashboard_url([ 'reports', 'daily-sales' ]);?>" class="small-box-footer">
                  <?php _e('Plus de détails', 'nexo_premium');?> <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            <!-- /.info-box --> 
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
        	<div class="small-box bg-blue">
                <div class="inner">
                <h4><span class="fa fa-money"></span> <?php _e('Chiffre d\'affaire globale', 'nexo_premium');?>
                </h4> 
                <h5><?php _e('Aujourd\'hui', 'nexo_premium');?>
                	<span class="pull-right">
                    	
                        <?php echo nexo_compare_card_values($Cache->get( 'net_sales_today'), $Cache->get( 'net_sales_yesterday'));?>
                        
                        <?php echo $this->Nexo_Misc->cmoney_format($Cache->get( 'net_sales_today'));?>
                    </span>
                </h5>
                <h5><?php _e('Hier', 'nexo_premium');?>
                	<span class="pull-right">
                        <?php echo $this->Nexo_Misc->cmoney_format($Cache->get( 'net_sales_yesterday'));?>
                    </span>
                </h5>
                <h5><?php _e('Total', 'nexo_premium');?>
                	<span class="pull-right">
                    	<?php echo $this->Nexo_Misc->cmoney_format($Cache->get( 'net_sales'));?>
                    </span>
                </h5>
                </div>
                <div class="icon">
                  <i class="ion ion-stats-bars"></i>
                </div>
                <a href="<?php echo dashboard_url([ 'reports', 'sales-stats' ]);?>" class="small-box-footer">
                  <?php _e('Plus de détails', 'nexo_premium');?> <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            <!-- /.info-box --> 
        </div>
        <!-- /.col --> 
        <div class="col-md-3 col-sm-6 col-xs-12">
        	<div class="small-box bg-purple">
                <div class="inner">
                <h4><span class="fa fa-users"></span> <?php _e('Clients', 'nexo_premium');?>
                </h4> 
                <h5><?php _e('Aujourd\'hui', 'nexo_premium');?>
                	<span class="pull-right">
                    	<?php echo nexo_compare_card_values($Cache->get( 'customers_number_today'), $Cache->get( 'customers_number_yesterday'));?>
                        <?php echo $Cache->get('customers_number_today');?>
                    </span>
                </h5>
                <h5><?php _e('Hier', 'nexo_premium');?>
                	<span class="pull-right">
                        <?php echo $Cache->get( 'customers_number_yesterday');?>
                    </span>
                </h5>
                <h5><?php _e('Total', 'nexo_premium');?>
                	<span class="pull-right">
                    	<?php echo $Cache->get( 'customers_number');?>
                    </span>
                </h5>
                </div>
                <div class="icon">
                  <i class="ion ion-stats-bars"></i>
                </div>
                <a href="<?php echo dashboard_url([ 'customers' ]);?>" class="small-box-footer">
                  <?php _e('Plus de détails', 'nexo_premium');?> <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            <!-- /.info-box --> 
        </div>
        <!-- /.col --> 
        <div class="col-md-3 col-sm-6 col-xs-12">
        	<div class="small-box bg-red">
                <div class="inner">
                <h4><span class="fa fa-meh-o"></span> <?php _e('Créances', 'nexo_premium');?></h4> 
                <h5><?php _e('Aujourd\'hui', 'nexo_premium');?>
                	<span class="pull-right">
                    	<?php echo nexo_compare_card_values($Cache->get( 'creances_today'), $Cache->get( 'creances_yesterday'), true);?>
                        <?php echo $this->Nexo_Misc->cmoney_format($Cache->get( 'creances_today'));?>
                    </span>
                </h5>
                <h5><?php _e('Hier', 'nexo_premium');?>
                	<span class="pull-right">
                        <?php echo $this->Nexo_Misc->cmoney_format($Cache->get( 'creances_yesterday'));?>
                    </span>
                </h5>
                <h5><?php _e('Total', 'nexo_premium');?>
                	<span class="pull-right">
                    	<?php echo $this->Nexo_Misc->cmoney_format($Cache->get( 'creances'));?>
                    </span>
                </h5>
                </div>
                <div class="icon">
                  <i class="ion ion-stats-bars"></i>
                </div>
                <a href="<?php echo dashboard_url([ 'reports', 'expenses' ]);?>" class="small-box-footer">
                  <?php _e('Plus de détails', 'nexo_premium');?> <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            <!-- /.info-box --> 
        </div>
        <!-- /.col --> 
    </div>
</div>
<?php elseif( User::in_group( 'store.cashier' ) ):?>
<?php include_once( dirname( __FILE__ ) . '/cashier-dashboard.php' );?>
<?php endif;?>