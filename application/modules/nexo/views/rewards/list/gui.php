<?php
$this->Gui->col_width( 1, 4 );

$this->Gui->add_meta( array(
	'namespace'	=>	'reward.list',
	'type'		=>	'unwrapped'
) );

$this->Gui->add_item( array(
	'type'		=>	'dom',
	'content'	=>	$this->load->module_view( 'nexo', 'rewards.list.dom', null, true )
), 'reward.list', 1 );

$this->Gui->output();