<?php

require_once '../includes/imp_files.php';

if (!class_exists('Viv')) {
    return false;
}

$VivClass = new Viv();

if (isset($_POST['job']) && trim($_POST['job']=='loop_it')) {
    if (!isset($_POST['bi']) || $_POST['bi'] < 1) {
        echo 'Invalid block index.';
        return false;
    }

    $blockindex = (int) $_POST['bi'];

    if (dothemagic($blockindex)==true) {
        $VivClass->updateExtra(1, $blockindex);
        echo (int) $blockindex;
        return true;
    }
    return false;
}