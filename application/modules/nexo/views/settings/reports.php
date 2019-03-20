<?php

use Carbon\Carbon;
use Dompdf\Dompdf;

$this->Gui->col_width(1, 2);

$this->Gui->add_meta(array(
     'type'			=>    'unwrapped',
     'col_id'		=>    1,
     'namespace'	=>    'nexo_reports',
     'gui_saver' =>  true,
     'footer'    =>  [
         'submit'    =>  [
             'label' =>  __( 'Sauvegarder', 'nexo' )
         ]
     ]
));

$this->Gui->add_item( array(
    'type' =>    'select',
    'options'      =>   [
    ''        =>   __( 'Veuillez choisir une option', 'nexo' ),
        'detailed_sales'     =>   __( 'Ventes Journalières', 'nexo' )
    ],
    'name' =>	store_prefix() . 'email_reports_digest',
    'label' =>   __( 'Envoyer des rapports par Email', 'nexo' ),
    'description' =>   __( 'Cette fonctionnalité vous permettra d\'envoyer des rapports journalier à une addresse mentionnée dans la zone de texte suivante.', 'nexo' ),
    'placeholder' =>   ''
), 'nexo_reports', 1 );

$this->Gui->add_item([
    'type'  =>  'text',
    'name'  =>  store_prefix() . 'admin_email_for_reports',
    'label' =>  __( 'Email de l\'administrateur', 'nexo' ),
    'description'   =>  __( 'Cet email recevra le rapport journalier', 'nexo' )
], 'nexo_reports', 1 );

$hours  =   [];
for( $i = 0; $i <= 23; $i++ ) {
    $hours[ $i . ':00' ]    =   sprintf( __( '%sh00'), $i );
}

$this->Gui->add_item([
    'type'  =>  'select',
    'options'   =>  $hours,
    'name'  =>  store_prefix() . 'submit_order_hour',
    'label' =>  __( 'Heure d\'envoi', 'nexo' ),
    'description'   =>  
        sprintf( 
            __( 'Definir quand envoyer le rapport durant la journée. 
            Indispensable pour activer la fonctionnalité. 
            Vous pouvez optionnellemenet définir une tâche cron qui 
            pointera vers l\'addresse : <strong>%s</strong> après l\'heure d\'exécution du 
            script (quelques minutes suffiront).', 'nexo' ),
            site_url([ 'cron', 'reports', 'daily-sales' ] ) 
        )
], 'nexo_reports', 1 );

$this->Gui->output();