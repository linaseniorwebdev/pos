<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="<?php echo module_url( 'self-ordering' ) . '/css/bootstrap.min.css';?>">
    <link rel="stylesheet" href="<?php echo module_url( 'nexo' ) . '/../../../bower_components/sweetalert2/dist/sweetalert2.min.css';?>">
    <link rel="stylesheet" href="<?php echo module_url( 'nexo' ) . '/../../../css/font-awesome.min.css';?>">
    <script src="<?php echo module_url( 'nexo' ) . '/bower_components/axios/dist/axios.min.js';?>"></script>
    <script src="<?php echo module_url( 'nexo' ) . '/bower_components/vue/dist/vue.min.js';?>"></script>
    <script src="<?php echo module_url( 'nexo' ) . '/bower_components/jquery/dist/jquery.min.js';?>"></script>
    <script src="<?php echo module_url( 'nexo' ) . '/bower_components/remarkable-bootstrap-notify/dist/bootstrap-notify.min.js';?>"></script>
    <script src="<?php echo module_url( 'nexo' ) . '/../../../bower_components/sweetalert2/dist/sweetalert2.min.js';?>"></script>
    <script src="<?php echo module_url( 'self-ordering' ) . '/js/bootstrap.min.js';?>"></script>
    <title><?php echo store_option( 'so_homepage_title', __( store_title( __( 'point de vente', 'nexo' ) ), 'self-ordering' ) );?></title>
    <?php include_once( MODULESPATH . '/nexo/views/exposed-http-request.php' );?>
</head>
<body>
    <?php echo $body;?>
</body>