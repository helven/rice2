<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$src  = '/home/u119812537/domains/rice2.pixelstail.com/public_html/storage/app/public';
$link = '/home/u119812537/domains/rice2.pixelstail.com/public_html/public/storage';

echo "Creating symlink...\n";
var_dump(symlink($src, $link));

echo "Checking link existence...\n";
var_dump(file_exists($link));
var_dump(is_link($link));

echo "Link target:\n";
if (is_link($link)) {
    echo readlink($link) . "\n";
} else {
    echo "Link not found or not a symlink.\n";
}