<?php 
include './auth/subClass.php'; 
$date = new DateTime("now", new DateTimeZone('Asia/Bangkok'));
$date->setTimestamp(time());
$date->format('H:i');
$SCADA = new SCADA();
$SCADA->OpenDebug();
$TodateXToTime = '';
$cboTemplate = '';
$tabledata = '';
$iCodition = '';
//$SCADA->OpenDebug();
session_start(); if (!$_SESSION["USER"]) header("Location: ../Login.php");
if($_GET['table'] === 'tablescada' && isset($_POST['period'])){
  if($SCADA->CheckDurationMoreThan1min($_POST['Fromdate'],$_POST['Todate']) === true && trim($_POST['period']) == '1 Minute'){
			header("Location: ./Index.php?Morethan=3days");
	}else if($SCADA->CheckDurationMoreThan($_POST['Fromdate'],$_POST['Todate']) === true){
    if($_POST['tagname'] == ''){header("Location: ./Index.php?error=et8c");}
		if(isset($_POST['id_tmp'])) { $cboTemplate = $_POST['id_tmp']; /*id template*/} 
		if(isset($_POST['iCodition'])) {$iCodition = $_POST['iCodition'];}
		$TagRTU = $_POST['tagname'];
		$TagName = $_POST['tagnamescada'];
		$period = $_POST['period'];
		$chtag = $_POST['addtag'];
		$FromdateXFromtime = $_POST['Fromdate'] . ' ' . $_POST['Fromtime'];
		$iDisplay = $_POST['iDisplay'];
		if($_POST['Fromtime'] || $_POST['Totime'] > $date->format('H:i')){
			$TodateXToTime = $_POST['Todate'] . ' ' . $date->format('H:i');
		}else{
			$TodateXToTime = $_POST['Todate'] . ' ' . $_POST['Totime'];
		}
		if($FromdateXFromtime && $iDisplay  && $period != null){
			$tabledata = $SCADA->TABLE_SCADA(
				$period,$FromdateXFromtime,$TodateXToTime,$cboTemplate,$iDisplay,$iCodition,$TagRTU,$chtag
			);
		}
  }
}
if($_GET['table'] === 'tablegmdr'){
	if($SCADA->CheckDurationMoreThan($_POST['Fromdate-gmdr'],$_POST['Todate-gmdr']) === true){
		if($_POST['cboTemplate2'] == ''){header("Location: ./Index.php?error=et8c");}
		$table = $_POST['GMDRSETTABLE'];
		$TagName = $cboTemplate  = $_POST['cboTemplate2']; //id tag
		$FromtimeGMDR = $_POST['Fromdate-gmdr'] . ' ' . $_POST['Fromtime-gmdr'];
		$ToTimeGMDR = $_POST['Todate-gmdr'] . ' ' . $_POST['Totime-gmdr'];
		if($table && $cboTemplate && $FromtimeGMDR && $ToTimeGMDR != null){
			$tabledata = $SCADA->TABLE_GMDR(
				$table,$cboTemplate ,$FromtimeGMDR,$ToTimeGMDR
			);
		}
	}
}

?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title><?php echo $TagName;?></title>
  <link rel="shortcut icon" href="../img/favicon.png" type="image/gif" sizes="16x16">
<link rel="stylesheet" href="../css/Table.css">
<link rel="stylesheet" href="../vendor/bootstrapTable/bound.css">
<link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
<script src="../js/jquery-3.7.1.min.js"></script>
<script  src="../vendor/bootstrapTable/bound.js"></script>
<script src="../js/Loader.js"></script>
<script type="text/javascript" src="../js/DataTable.js"></script>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css'>
</head>
<style>
.background-loader{
  position: fixed;
  top:0px;
  z-index: 300;
  background-color: rgb(0,0,0,0.8);
  width: 100%;
  height: 100%;
}

.loader{
  position: fixed;
  z-index: 301;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  height: 200px;
  width: 200px;
  overflow: hidden;
  text-align: center;
}

.spinner{
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 303;
  border-radius: 100%;
  border-left-color: transparent !important;
  border-right-color: transparent !important;
}

.spinner1{
  width: 100px;
  height: 100px;
  border: 10px solid #fff;
  animation: spin 1s linear infinite;
}

.spinner2{
  width: 70px;
  height: 70px;
  border: 10px solid #fff;
  animation: negative-spin 2s linear infinite;
}

.spinner3{
  width: 40px;
  height: 40px;
  border: 10px solid #fff;
  animation: spin 4s linear infinite;
}

@keyframes spin {
  0%{
    transform: translate(-50%,-50%) rotate(0deg);
  }
  100%{
    transform: translate(-50%,-50%) rotate(360deg);
  }
}

@keyframes negative-spin {
  0%{
    transform: translate(-50%,-50%) rotate(0deg);
  }
  100%{
    transform: translate(-50%,-50%) rotate(-360deg);
  }
}

.loader-text {
  position: relative;
  top: 75%;
  color: #fff;
  font-weight: bold;
}
</style>
<body>
  <div class="background-loader" id="loading-p">
    <div class="loader">
      <span class="spinner spinner1"></span>
      <span class="spinner spinner2"></span>
      <span class="spinner spinner3"></span>
      <br>
      <span class="loader-text" id="loader-text">LOADING DATA TABLE...</span>
      <div class="counter" style="display:none;"><h1>0</h1></div>
    </div>
  </div>
	<?php if($TagName != ''){?>
	<h2 class="TAG"><B><i class="fas fa-tag"></i>&nbsp;<?php echo $TagName;?></B></h2>
	<?php } ?>
	<div class="table-container" id="loadpage">
		<a class="btn btn-success" style="float:left;margin-right:20px;margin-left:20px;" href="./Index.php">Back to Home page</a>
		<table id="example" class="table table-striped table-bordered">
    <?php
			print_r($tabledata);
			echo $SCADA->getFooter(); 
		?>
		</table>
	</div>
</body>
</html>