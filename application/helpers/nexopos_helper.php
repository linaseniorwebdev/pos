<?php
/**
 * NexoPOS helper
 * ---------------
 *
 * All useful function to help build faster
**/

use Carbon\Carbon;
use \Pecee\SimpleRouter\Route\RouteUrl;

if (! function_exists('nexo_permission_check')) {
    /**
     * Permission Tester
     *
     * Check whether for Ajax action an user can perform requested action
     *
     * @param string permission
     * @return void
    **/

    function nexo_permission_check($permission)
    {
        if (! User::can($permission)) {
            echo json_encode(array(
                'error_message'    =>   get_instance()->lang->line('permission-denied'),
                'success'        =>    false
            ));
            die;
        }
    }
}

if (! function_exists('nexo_availability_check')) {

    /**
     * Check Availability of item
     * Item in use can't be deleted
     *
     * @param string/int item filter
     * @param Array table where to check availability with this for array( array( 'col'=> 'id', 'table'	=> 'users' ) );
    **/

    function nexo_availability_check($item, $tables)
    {
        if (is_array($tables)) {
            foreach ($tables as $table) {
                $query    =    get_instance()->db->where(@$table[ 'col' ], $item)->get(@$table[ 'table' ]);
                if ($query->result_array()) {
                    echo json_encode(array(
                        'error_message'    =>   get_instance()->lang->line('cant-delete-used-item'),
                        'success'        =>    false
                    ));
                    die;
                }
            }
        }
    }
}

/**
 * Compare Two value and print arrow
 *
 * @param int
 * @param int
 * @param bool invert ?
 * @return string
**/

if (! function_exists('nexo_compare_card_values')) {
    function nexo_compare_card_values($start, $end, $invert = false)
    {
        if (intval($start) < intval($end)):
            return '<span class="ar-' . ($invert == true ? 'invert-up' : 'down') . '"></span>'; elseif (intval($start) > intval($end)):
            return '<span class="ar-' . ($invert == true ? 'invert-down' : 'up') . '"></span>';
        endif;
        return '';
    }
}

/**
 * Float val for NexoPOS numeric values
 * @param float/int
 * @return float/int
**/

if (! function_exists('__floatval')) {
    function __floatval($val)
    {
        return round(floatval($val), 2);
    }
}

/**
 * Store Name helper
 * @param string page title
 * @return string
**/

if( ! function_exists( 'store_title' ) ) {
	function store_title( $title ) {
		global $CurrentStore;

		if( $CurrentStore != null ) {
			return sprintf( __( '%s &rsaquo; %s &mdash; %s', 'nexo' ), xss_clean( @$CurrentStore[0][ 'NAME' ] ), $title, store_option( 'site_name', __( 'NexoPOS' ) ) );
		} else {
            global $Options;
			return sprintf( __( '%s &rsaquo; %s', 'nexo' ), @$Options[ 'site_name' ] != null ? $Options[ 'site_name' ] : 'NexoPOS', $title );
		}
	}
}

/**
 * Store Prefix
 * @return string store prefix
**/

if( ! function_exists( 'store_prefix' ) ) {
	function store_prefix( $store_id = null ) {
        if( $store_id == null ) {
            global $store_id;
        }
		$prefix		=	$store_id != null ? 'store_' . $store_id . '_' : '';
		$prefix		=	( $prefix == '' && intval( get_instance()->input->get( 'store_id' ) ) > 0 ) ? 'store_' . get_instance()->input->get( 'store_id' ) . '_' : $prefix;
        $prefix     =   get_instance()->uri->segment( 2 ) == 'stores' && get_instance()->uri->segment( 3 ) != null ? 'store_' . get_instance()->uri->segment( 3 ) . '_' : $prefix;
		return $prefix;
	}
}

/**
 * Store Slug
**/

if( ! function_exists( 'store_slug' ) ) {
	function store_slug( $store_id = null ) {
        if( $store_id == null ) {
            global $store_id;
            $store_id   =   $store_id ?: get_store_id();
        }
		return	$store_id != null ? 'stores/' . $store_id : '';
	}
}

/**
 * Get Store Id
**/

if( ! function_exists( 'get_store_id' ) ) {
	function get_store_id() {
        global $store_id;

		if( $store_id != null ) {
			return $store_id;
		} else if( intval( get_instance()->input->get( 'store_id' ) ) > 0 ) {
			return intval( get_instance()->input->get( 'store_id' ) );
		} else {
			return 0;
		}
	}
}

/**
 * Store Upload Path
**/

if( ! function_exists( 'get_store_upload_path' ) ) {
	function get_store_upload_path( $id = null ) {

		global $store_id;

		if( $id != null ) {
			return 'public/upload/store_' . $id;
		}

		if( $store_id != null ) {
			return 'public/upload/store_' . $store_id;
		}

		return 'public/upload';

	}
}

/**
 * Store URL
 * @param int store id
 * @return string store URL
**/

if( ! function_exists( 'get_store_upload_url' ) ) {
	function get_store_upload_url( $id = null ) {

		global $store_id;

		if( $id != null ) {
			return base_url() . 'public/upload/store_' . $id . '/';
		}

		if( $store_id != null ) {
			return base_url() . 'public/upload/store_' . $store_id . '/';
		}

		return base_url() . 'public/upload/';

	}
}

/**
 * Store Get param
**/

if( ! function_exists( 'store_get_param' ) ) {
	function store_get_param( $prefix = '?' ) {

		if( store_prefix() != '' ) {
			return $prefix . 'store_id=' . get_store_id();
		}
		return $prefix;
	}
}

/**
 * Is MultiStore
**/

if( ! function_exists( 'is_multistore' ) ) {
	function is_multistore() {

		if( store_prefix() != '' ) {
			return true;
		}
		return false;
	}
}

/**
 * Check Whether a multistore is enabled
**/

if( ! function_exists( 'multistore_enabled' ) ) {
	function multistore_enabled() {

		global $Options;

		if( @$Options[ 'nexo_store' ] == 'enabled' ) {
			return true;
		}
		return false;

	}
}

/**
 * Zero Fill
**/

if( ! function_exists( 'zero_fill' ) ) {
    function zero_fill( $int, $zeros = 3 ) {
        $pr_id = sprintf("%0". $zeros . "d", $int);
        return $pr_id;
    }
}

/**
 *  Cart Gross Value.
 *
 *  @param  object order
 *  @param  object order items
 *  @return float/int
**/

if( ! function_exists( 'nexoCartValue' ) ) {
    function nexoCartGrossValue( $items ) {
        $value      =      0;
        foreach( $items as $item ) {
            if( $item[ 'DISCOUNT_TYPE' ] == 'percentage' && $item[ 'DISCOUNT_PERCENT'] != '0' ) {
                $percent    =   floatval( $item[ 'DISCOUNT_PERCENT' ] ) * floatval( $item[ 'PRIX' ] ) / 100;
                $discount   =   ( floatval( $item[ 'PRIX' ] ) - $percent ) * intval( $item[ 'QUANTITE' ] );
                $value      +=  $discount;
            } else if( $item[ 'DISCOUNT_TYPE' ] == 'flat' ) {
                $discount   =   ( floatval( $item[ 'PRIX' ] ) -  floatval( $item[ 'DISCOUNT_AMOUNT' ] ) ) * intval( $item[ 'QUANTITE' ] ) ;
                $value      +=  $discount;
            } else {
                $value      +=  floatval( $item[ 'PRIX' ] ) * intval( $item[ 'QUANTITE' ] );
            }
        }
        return $value;
    }
}

/**
 *  Percentage Discount
 *  @param  array  order
 *  @return int/float
**/

if( ! function_exists( 'nexoCartPercentageDiscount' ) ) {
    function nexoCartPercentageDiscount( $items, $order ) {
        return ( nexoCartGrossValue( $items ) * floatval( $order[ 'REMISE_PERCENT' ] ) ) / 100;
    }
}

/**
 * Store Option
 * @param string option
 * @return array/bool/string
**/

if( ! function_exists( 'store_option' ) ) {
    function store_option( $option, $default = null ) {
        return get_option( store_prefix() . $option, $default );
    }
}

/**
 * Set Store Option
 * @param string option
 * @return array/bool/string
**/

if( ! function_exists( 'set_store_option' ) ) {
    function set_store_option( $option, $value ) {
        return set_option( store_prefix() . $option, $value );
    }
}

/**
 * Date format
 * @param string date
 * @param string format
 * @return string formated date
**/

if( ! function_exists( 'nexo_date_format' ) ) {
    function nexo_date_format( $date, $format = null ) {
        if( $format == null ) {
            $format     =   store_option( 'nexo_date_format', 'Y-m-d' );
        }

        return Carbon::parse( $date )->format( $format );
    }
}

/**
 * UI Notices
**/

if( ! function_exists( 'nexo_notices' ) ) {
    function nexo_notices( $data ) {
        get_instance()->Nexo_Notices->add( $data );
    }
}

/**
 * Raw to options
 * @param array raw entries
 * @param string key
 * @param string value
 * @return array key value
**/

function nexo_convert_raw( $raw, $key, $value = null) {
    $new    =   [];
    foreach( $raw as $data ) {
        $new[ $data[ $key ] ]      =   ( $value == null ) ? $data : $data[ $value ];
    }
    return $new;
}

function register_store_route( $path, $callback ) {
    $current_path 	=	substr( request()->getHeader( 'script-name' ), 0, -10 ) . '/dashboard/stores/' . get_store_id() . '/';
    $final_path     =   ! empty( $path ) ? rtrim( $current_path . $path ) : rtrim( $current_path . $path );
    return new RouteUrl( $final_path, $callback );
}

/**
 * Return dashboard URL
 * @param array current route schem
 * @return string;
 */

function dashboard_url( $url ) {
    if( is_multistore() ) {
        return site_url( array_merge([ 'dashboard', store_slug(), 'nexo' ], $url ) );
    }
    return site_url( array_merge([ 'dashboard', store_slug(), 'nexo' ], $url ) );
}

/**
 * Return api URL
 * @param array current route schem
 * @return string;
 */

function api_url( $url ) {
    return site_url( array_merge([ 'api' ], $url ) ) . store_get_param('?');
}

/**
 * Array in 
 * @param array
 * @param array
 * @return boolean
 */
function array_in( array $needle, array $array ) {
    $isFound  = false;
    foreach ( $needle as $need ) {
        if ( in_array( $need, $array ) ) {
           $isFound  = true;
        }
    }
    return $isFound;
}

function nexting( $values, $replacement = ' ', $limit = null, $ratio = 1 ) {
    if ( $limit == null ) {
        $limit  =   store_option( 'nps_width', 48 );
    }

    $length         =   0;
    $countString    =   count( $values );

    foreach( $values as $val ) {
        $length     +=  ( count( preg_split( '//u', $val, null, PREG_SPLIT_NO_EMPTY ) ) * $ratio );
        // $length     +=  ( count( str_split( $val ) ) * $ratio );
    }

    $fill    =   '';
    for( $i = 0; $i < $limit - $length; $i++ ) {
        $fill    .=  $replacement;
    }

    if ( count( $values ) == 0 ) {
        return $fill;
    }

    $spaceBetweenValues     =   floor( $length / count( $values ) );

    $finalString    =   '';
    foreach( $values as $index => $value ) {
        if ( $index == $countString - 1 ) {
            $finalString   .=  $value;
        } else {
            $finalString    .=  $value . $fill;
        }
    }

    return $finalString;
}

function buildingLines( $col1, $col2 ) {
    $col1_lines     =   preg_split ('/$\R?^/m', $col1);
    $col2_lines     =   preg_split ('/$\R?^/m', $col2);
    $finalBuild     =   [];
    
    /**
     * We would like to use the hight table number
     */
    for( $i = 0; $i < ( count( $col1_lines ) > count( $col2_lines ) ? count( $col1_lines ) : count( $col2_lines ) ); $i++ ) {
        $finalBuild[]   =   [ trim( @$col1_lines[$i] ), trim( @$col2_lines[$i] ) ];
    }

    return $finalBuild;
}

/** 
 * Text to EsCText
 * @param string
 * @return string[][]
 */
function textToEsc( $string )
{
    $col1_lines     =   preg_split ('/$\R?^/m', $string);
    $finalBuild     =   [];

    /**
     * We would like to use the hight table number
     */
    for( $i = 0; $i < count( $col1_lines ) ; $i++ ) {
        $finalBuild[]   =   trim( @$col1_lines[$i] );
    }

    return $finalBuild;
}

function __fill( $char = '-', $maxLetter ) {

    $finalString    =   '';

    for( $i = 0; $i < $maxLetter; $i++ ) {
        $finalString    .=  $char;
    }

    return $finalString;
}

/**
 * Populate a line with a string and fill 
 * with the place holder
 * @param string string to fill
 * @param int maxium letter
 * @param array config
 * @return string;
 */
function __populate( $string, $max, $config = [
    'align' =>  'left',
    'fill'  =>  ' '
]) {
    extract( $config );
    $strLen         =   strlen( $string );
    $toPopulate     =   $max - $strLen;
    return $string . __fill( $fill, $toPopulate );
}

/**
 * Check if a string or an array
 * of string will overflow the provided with
 * @param array of row
 * @param int width per column
 * @param int max letter
 * @return int maximum row overflow
 */
function __willOverFlow( $row, $widthPerColumn, $maxLetter ) {
    /**
     * let's check if string
     * will overflow
     */
    $maximumRowOverflow     =   0;

    foreach( $row as $__index => $col ) {
        
        if( is_array( $col ) ) {
            $col    =   __getRealColString( compact( 'col', '__index', 'widthPerColumn', 'maxLetter' ) );
        }

        /**
         * Make the placeholder length 
         * per column automatic
         */
        $placeholderLengthPerColumn     =   floor( ( $widthPerColumn[ $__index ] * $maxLetter ) / 100 );
        $maximumLines                   =   round( strlen( $col ) / $placeholderLengthPerColumn );

        
        /**
         * Reassign the maxium line only
         * if it's greater
         */
        $maximumRowOverflow     =   $maximumLines > $maximumRowOverflow ? $maximumLines : $maximumRowOverflow; 
    }
    
    return $maximumRowOverflow;
}

/**
 * Get Real row string, including extrat fields
 * @return string
 */
function __getRealColString( $data )
{
    extract( $data );

    $resultString   =   '';
    foreach( $col as $colString ) {
        $result     =   __populate( $colString, floor( ( $widthPerColumn[ $__index ] * $maxLetter ) / 100 ), [
            'align'     =>  'left',
            'fill'      =>  isset( $fillWith ) ? $fillWith : ' ',
        ]);

        $resultString    .=      $result;
    }
    return $resultString;
}

/**
 * render lines
 * @return string;
 */
function __renderLines( $data )
{
    extract( $data );
    
    $colString  =   isset( $col ) ? $col : $colString;

    /**
     * Make the placeholder length 
     * per column automatic
     */
    $placeholderLengthPerColumn     =   floor( ( $widthPerColumn[ $__index ] * $maxLetter ) / 100 );

    if( strlen( $colString ) > $placeholderLengthPerColumn ) {
        $rawStr     =   ( substr( $colString, $rowId * $placeholderLengthPerColumn, $placeholderLengthPerColumn ) );
    } else if( $rowId === 0 && strlen( $colString ) <= $placeholderLengthPerColumn ) {
        $rawStr     =   trim( $colString );
    } else {
        $rawStr     =   '';
    }

    $str            =    __populate( $rawStr, $placeholderLengthPerColumn, [
        'align'     =>  'left',
        'fill'      =>  ' '
    ]);
    return $str;
}

/**
 * Create toEscTable
 * @param array
 * @return string
 */
function toEscTable( $rawTable, $config = [
    'bodyLines'     =>  true,
    'maxLetter'     =>  150,
    'fillWith'      =>  ' ',
]) 
{
    extract( $config );

    $totalColumns                   =   count( $rawTable[0] );
    $placeholderLengthPerColumn     =   floor( $maxLetter / $totalColumns );
    $finalString                    =   '';
    $widthPerColumn                =   [];

    foreach( $rawTable as $index => $row ) {

        
        /**
         * first row is the header
         */
        if( $index === 0 ) {
            
            $finalString    .=  __fill( '-', $maxLetter ) . "\r\n";

            $totalStringPerCol  =   array_map( function( $col ) {
                return strlen( $col[ 'title' ] );
            }, $row );

            $totalUsedString    =   array_sum( $totalStringPerCol );
            $maxDefinedWidth    =   0;
            $totalAutoWidth     =   0;

            foreach( $row as $__index => $col ) {

                /**
                 * Save defined width 
                 * or count auto columns
                 */
                if( is_numeric( @$col[ 'width' ] ) ) {
                    $maxDefinedWidth    +=  $col[ 'width' ];
                } else {
                    $totalAutoWidth++;
                }
            }

            /**
             * let's calculate the auto
             * width for columns
             */
            $availableAutoWidth     =   100 - $maxDefinedWidth;
            $autoWidth              =   $totalAutoWidth === 0 ? 0 : floor( $availableAutoWidth / $totalAutoWidth );

            foreach( $row as $__index => $col ) {
                if( $col[ 'width' ] === 'auto' ) {
                    $widthPerColumn[]   =   $autoWidth;
                } else {
                    $widthPerColumn[]   =   $col[ 'width' ];
                }
            }
            
            foreach( $row as $__index => $col ) {
                $str            =    __populate( $col[ 'title' ], ( $widthPerColumn[ $__index ] * $maxLetter ) / 100, [
                    'align'     =>  'left',
                    'fill'      =>  $fillWith
                ]);

                $finalString    .=    $str;
            }

            $finalString .=  "\r\n";

            $finalString    .=  __fill( '-', $maxLetter ) . "\r\n";
            
        } else {
            
            /**
             * let's check if string
             * will overflow
             */
            $maximumRowOverflow     =   __willOverFlow( $row, $widthPerColumn, $maxLetter );

            /**
             * According to the defined overflow
             * let's populate the row
             */
            for( $rowId = 0; $rowId <= $maximumRowOverflow; $rowId++ ) {
                
                $rendered   =   false;

                foreach( $row as $__index => $col ) {

                    /**
                     * let's render each column and make sure 
                     * a column with an array is also rendered
                     */
                    if( is_array( $col ) ) {
                        $col    =   __getRealColString( compact( 'col', 'widthPerColumn', 'maxLetter', 'fillWith', '__index' ) );
                    }
                    
                    $rendered   =   __renderLines( compact( 'widthPerColumn', 'maxLetter', 'col', '__index', 'rowId', 'finalString' ) );

                    if( $rendered !== false ) {
                        $finalString    .=  $rendered ;
                    }
                }
    
                if( $rendered ) {
                    $finalString .= "\r\n";
                }
            }

            if( $bodyLines ) {
                $finalString    .=  __fill( '-', $maxLetter ) . "\r\n";
            }

        }

        /**
         * if were closing the table
         * checking the last index
         */
        if( $index == count( $rawTable ) - 1 ) {
            $finalString    .=  __fill( '-', $maxLetter ) . "\r\n";
        }
    }

    return $finalString;
}