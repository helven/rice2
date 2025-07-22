<?php
if(!function_exists('print_a'))
{
    function print_a($a)
    {
        echo '<pre>';
        print_r($a);
        echo '</pre>';
    }
}