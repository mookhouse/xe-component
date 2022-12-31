<?php
if(!is_null($_SERVER['REMOTE_ADDR']) && !is_null($_SERVER['REQUEST_SCHEME']))  // 브라우저 실행 금지
	exit;

ini_set('memory_limit', '2048M');  // php.ini default 512M
set_time_limit(720);  // sec

include '../usr/_config/ip.config.php';

$oDb = mysqli_connect($server["db_host"], $server["db_id"], $server["db_pw"],  $server["db_schema"]);

$sql = "SELECT adm_ad.ada_name, adm_history_join.adu_id, adm_history_join.adhj_date_request, adm_history_join.adhj_answers
		FROM adm_history_join
		INNER JOIN adm_ad ON adm_ad.ada_idx=adm_history_join.ada_idx
		WHERE adm_ad.ada_type='exp' AND ada_name LIKE '%맘박스%'
		ORDER BY adhj_idx;";
$result = mysqli_query($oDb, $sql);

echo $result->num_rows.' rows has been detected'.PHP_EOL;

$nTestCnt = 5;
$nIdx = 0;

$myfile = fopen("mombox_registration_log_array.php", "w") or die("Unable to open file!");
$sData = "<?php
\$aMomboxRegistration = array(\r\n";
fwrite($myfile, $sData);
while($aSingleRow = mysqli_fetch_array($result)) 
{
	$sCleaned = str_replace('\'', '', $aSingleRow['adhj_answers']);
	$sData = "array(
		\"ada_name\" => \"".$aSingleRow['ada_name']."\",
		\"adu_id\" => \"".$aSingleRow['adu_id']."\",
		\"adhj_date_request\" => \"".$aSingleRow['adhj_date_request']."\",
		\"adhj_answers\" => '".$sCleaned."',
		),\r\n";
	//echo PHP_EOL;
	fwrite($myfile, $sData);
	//if($nTestCnt < $nIdx++)
	//	break;
}
$sData = ");
?>";
fwrite($myfile, $sData);
fclose($myfile);
?>