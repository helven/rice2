<?php
if(!function_exists('format_date'))
{
    function format_date($date)
    {
        return date('Y-m-d', strtotime($date));
    }
}

if(!function_exists('format_time'))
{
    function format_time($date)
    {
        return date('H:i:s', strtotime($date));
    }
}

if(!function_exists('format_datetime'))
{
    function format_datetime($date)
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }
}

/**
 * Format
 *
 * Format invoice
 *
 * @access	public
 * @return	string
 */
if(!function_exists('format_invoice'))
{
    function format_invoice($str='')
    {
        if($str == '')
        {
            return '';
        }


        return str_pad ($str, 10, 0, STR_PAD_LEFT);
    }
}

/**
 * Format
 *
 * Format currency
 *
 * @access	public
 * @return	string
 */
if(!function_exists('format_currency'))
{
    function format_currency($str='', $decimal=2)
    {
        if($str == '' || $str == NULL)
        {
            $str	= 0;
        }
        $currency   = env('CURRENCY');
        return $currency.' '.number_format($str, $decimal);
    }
}

/**
 * Format
 *
 * Format filesize
 *
 * @access	public
 * @return	string
 */
if(!function_exists('format_filesize'))
{
    function format_filesize($bytes) { 
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                'unit'	=> 'TB',
                'value'	=> pow(1024, 4)
            ),
            1 => array(
                'unit'	=> 'GB',
                'value'	=> pow(1024, 3)
            ),
            2 => array(
                'unit'	=> 'MB',
                'value'	=> pow(1024, 2)
            ),
            3 => array(
                'unit'	=> 'KB',
                'value'	=> 1024
            ),
            4 => array(
                'unit'	=> 'B',
                'value'	=> 1
            ),
        );

        foreach($arBytes as $arItem)
        {
            if($bytes >= $arItem['value'])
            {
                $result	= $bytes / $arItem['value'];
                $result	= strval(round($result, 2)).' '.$arItem['unit'];
                break;
            }
        }
        return $result;
    }
}
