<?php
$this->Gui->col_width( 1, 4 );

$this->Gui->add_meta( array(
	'namespace'	=>	'reward.create',
	'type'		=>	'unwrapped'
) );

$this->Gui->add_item( array(
	'type'		=>	'dom',
	'content'	=>	$this->load->module_view( 'nexo', 'rewards.create.dom', null, true )
), 'reward.create', 1 );

$this->Gui->output();