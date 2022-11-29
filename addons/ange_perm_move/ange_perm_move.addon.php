<?php
/* Copyright (C) singleview.co.kr <http://singleview.co.kr> */

if(!defined('__XE__'))
	exit();

/**
 * @file ange_perm_move.addon.php
 * @author singleview.co.kr (root@singleview.co.kr)
 * @brief ange_perm_move add-on
 */
// Execute if called_position is before_display_content
if(!defined( '__ZBXE__' ) )
	exit();

$sMid = Context::get('mid');
if($sMid != 'usr' && $sMid != 'mobile')
	return;

// https://ange.co.kr/usr/?menu=story201&submenu=story_detail&NO=9226
// $sMenu = Context::get('menu');
// $sSubmenu = Context::get('submenu');
$sNo = Context::get('NO');
if(strlen($sNo))
{
	$aPermanentMove = ['9226'=>'maternity/308',];
	$sDestUrl = '';
	if(array_key_exists($sNo, $aPermanentMove))
		$sDestUrl = $aPermanentMove[$sNo];

	header('HTTP/1.1 301 Moved Permanently');
	header('Location: https://ange.co.kr/'.$sDestUrl);
	exit;
}
/* End of file ange_perm_move.addon.php */
/* Location: ./addons/ange_perm_move/ange_perm_move.addon.php */