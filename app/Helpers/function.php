<?php
// ------------------------------------------------------------------------

/**
 * Clean json
 *
 * Clean input for json object
 *
 * @access	public
 * @return	string
 */
if ( ! function_exists('json_clean'))
{
    function json_clean($str='')
    {
        $str    = str_replace("\n","",$str);
        $str    = str_replace("\r","",$str);
        $str    = str_replace('"',"\\\"",$str);

        return $str;
    }
}

// ------------------------------------------------------------------------

/**
 * String to slug
 *
 * Convert String to slug
 *
 * @access	public
 * @return	string
 */
if ( ! function_exists('strtoslug'))
{
    function strtoslug($str)
    {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', "-", $str);
        return $str;
    }
}

// ------------------------------------------------------------------------

/**
 * String to slug
 *
 * Convert String to slug
 *
 * @access	public
 * @return	string
 */
if ( ! function_exists('br2nl'))
{
    function br2nl($str)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\r\n", $str);
    }
}


// ------------------------------------------------------------------------

/**
 * Path to url
 *
 * Convert \ to /
 *
 * @access	public
 * @return	string
 */
if ( ! function_exists('path2url'))
{
    function path2url($str)
    {
        return str_replace('\\', '/', $str);
    }
}

// ------------------------------------------------------------------------

/**
 * Datetime difference
 *
 * Find difference between 2 date
 *
 * @access	public
 * @return	string
 * http://www.php.net/manual/en/function.date-diff.php
 */
if ( ! function_exists('date_difference'))
{
    function datetime_difference($start, $end="NOW", $rtnType='string')
    {
        $sdate = strtotime($start);
        $edate = strtotime($end);
        $timeshift  = '';

        $time = $edate - $sdate;
        $a_Timeshift= array(
            'day'   => 0,
            'hour'  => 0,
            'min'   => 0,
            'second'=> 0
        );
        if($time>=0 && $time<=59) {
                // Seconds
                $timeshift = $time.' seconds ';
                $a_Timeshift= array(
                    'day'   => 0,
                    'hour'  => 0,
                    'min'   => 0,
                    'second'=> $time
                );

        } elseif($time>=60 && $time<=3599) {
                // Minutes + Seconds
                $pmin = ($edate - $sdate) / 60;
                $premin = explode('.', $pmin);

                $presec = $pmin-$premin[0];
                $sec = $presec*60;

                $timeshift = $premin[0].' min ';

                $a_Timeshift= array(
                    'day'   => 0,
                    'hour'  => 0,
                    'min'   => $premin[0],
                    'second'=> str_replace('-', '', (date('s', $edate) - date('s')))
                );

        } elseif($time>=3600 && $time<=86399) {
                // Hours + Minutes
                $phour = ($edate - $sdate) / 3600;
                $prehour = explode('.',$phour);

                $premin = $phour-$prehour[0];
                $min = explode('.',$premin*60);

                $presec = '0.'.$min[1];
                $sec = $presec*60;

                $timeshift = $prehour[0].' hrs '.$min[0].' min ';

                $a_Timeshift= array(
                    'day'   => 0,
                    'hour'  => $prehour[0],
                    'min'   => $min[0],
                    'second'=> str_replace('-', '', (date('s', $edate) - date('s')))
                );

        } elseif($time>=86400) {
                // Days + Hours + Minutes
                $pday = ($edate - $sdate) / 86400;
                $preday = explode('.',$pday);

                $phour = $pday-$preday[0];
                $prehour = explode('.',$phour*24);

                $premin = ($phour*24)-$prehour[0];
                $min = explode('.',$premin*60);

                $presec = '0.'.(isset($min[1])?$min[1]:'');
                $sec = $presec*60;

                $timeshift = $preday[0].' days '.$prehour[0].' hrs '.$min[0].' min ';
                $timeshift = $preday[0].(($preday[0] > 1)?' days ':' day ').$prehour[0].(($prehour[0] > 1)?' hrs ':' hr ').$min[0].(($min[0] > 1)?' mins ':' min');

                $a_Timeshift= array(
                    'day'   => $preday[0],
                    'hour'  => $prehour[0],
                    'min'   => $min[0],
                    'second'=> str_replace('-', '', (date('s', $edate) - date('s')))
                );

        }
        if($rtnType == 'string')
        {
            return $timeshift;
        }
        elseif($rtnType == 'array')
        {
            return $a_Timeshift;
        }
    }
}

/**
 * Day difference
 *
 * Find day difference between 2 date
 *
 * @access	public
 * @return	string
 * http://www.php.net/manual/en/function.date-diff.php
 */
if ( ! function_exists('day_difference'))
{
    function day_difference($start, $end="NOW")
    {
        $sdate = strtotime($start);
        $edate = strtotime($end);
        $pday = ($edate - $sdate) / 86400;
        $a_Pday = explode('.',$pday);
        
        return $a_Pday[0];
    }
}

/**
 * Hours difference
 *
 * Find hours difference between 2 date
 *
 * @access	public
 * @return	string
 * http://www.php.net/manual/en/function.date-diff.php
 */
if ( ! function_exists('hour_difference'))
{
    function hour_difference($start, $end="NOW")
    {
        $sdate = strtotime($start);
        $edate = strtotime($end);

        $pday = ($edate - $sdate) / 3600;
        $preday = explode('.',$pday);

        return $preday[0];
    }
}

/**
 * File name
 *
 * Get file name
 *
 * @access	public
 * @return	string
 */
if ( ! function_exists('get_file_name'))
{
    function get_file_name($filename='')
    {
        if($filename == '')
        {
            return '';
        }

        $a_Filename = explode('.', $filename);
        $ext    = $a_Filename[count($a_Filename) - 1];
        $name   = str_replace('.'.$ext, '', $filename);

        return $name;
    }
}

/**
 * File extension
 *
 * Get file extension
 *
 * @access	public
 * @return	string
 */
if ( ! function_exists('get_file_ext'))
{
    function get_file_ext($filename='')
    {
        if($filename == '')
        {
            return '';
        }

        $a_Filename = explode('.', $filename);
        $ext    = $a_Filename[count($a_Filename) - 1];
        $name   = str_replace('.'.$ext, '', $filename);

        return $ext;
    }
}

/**
 * Filter Content
 *
 * Get filter content
 *
 * @access	public
 * @return	string
 */
if ( ! function_exists('filter_content'))
{
    function filter_content($content='')
    {
        if($content == '')
        {
            return '';
        }
        
        //Censor texts for email
        $patternEmail	= "/[^@\s]*@[^@\s]*\.[^@\s]*/";
        $patternMobile	= "/(\(?+[0-9]+[\- ]?+[0-9]?+\)?+)/";
        $patternMobile	= "/(\+?[\d-\(\)\s]{8,20}[0-9]?\d)/";
        
        $replacementText	= '************'; //CENSOREDCONTENT
        
        $content = preg_replace($patternEmail, $replacementText, $content);
        $content = preg_replace($patternMobile, $replacementText, $content);
        
        return $content;
    }
}
/**
 * Time Elapsed
 *
 * Get Time Elapsed
 *
 * @access	public
 * @return	string
 */
if( ! function_exists('time_elapsed_string'))
{
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
    
}

if( ! function_exists('clean_phone_no'))
{
    function clean_phone_no($str)
    {
        $str   = str_replace(array('+6', ' ', '-', '_', '(', ')'), '', $str);

        return $str;
    }
}