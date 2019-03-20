<?php
$this->Gui->col_width(1, 4);

$this->Gui->add_meta(array(
     'type'			=>    'unwrapped',
     'col_id'		=>    1,
     'namespace'	=>    'np-stock-tracking'
));

$this->events->add_action('dashboard_header', function () { ?>
	<script type="text/javascript" src="<?php echo module_url('nexo');?>/bower_components/moment/min/moment.min.js"></script>
	<script type="text/javascript" src="<?php echo module_url('nexo');?>/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
	<script type="text/javascript" src="<?php echo module_url('nexo');?>/bower_components/underscore/underscore-min.js"></script>
	<link rel="stylesheet" href="<?php echo module_url('nexo');?>/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />
	<?php
});


$this->Gui->add_item( array(
     'type'          =>    'dom',
     'content'       =>    $this->load->module_view( 'nexo_premium', 'stock-tracking.dom', null, true )
), 'np-stock-tracking', 1 );

$this->Gui->output();