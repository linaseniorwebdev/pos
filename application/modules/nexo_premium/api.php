<?php
$Routes->post( 'nexopos/cashiers_performance', 'NexoPremiumReportController@cashierPerformance' );
$Routes->post( 'nexopos/raw_stock_tracking', 'NexoPremiumReportController@stockTracking' );
$Routes->post( 'nexopos/reports/registers/sessions', 'ApiNexoRegistersReports@sessions' );