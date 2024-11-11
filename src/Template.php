<?php session_start(); if (!$_SESSION["USER"]) header("Location: ../Login.php"); ?>
<!DOCTYPE html>
<html lang="en">
<?php include ('../components/Header.php'); ?>
<style>
/* container */
.container {
  border-radius:3px !important;
  max-width:98%;
  height: 510px !important;
  margin: auto;
  position: relative;
  background-color:rgba(0, 0, 0, 0.4) !important;
  color:#fff;
}
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
<script src="../js/Loader.js"></script>
<body>
  <div class="background-loader" id="loading-p">
    <div class="loader">
      <span class="spinner spinner1"></span>
      <span class="spinner spinner2"></span>
      <span class="spinner spinner3"></span>
      <br>
      <span class="loader-text" id="loader-text" style="width:100%;"></span>
      <div class="counter" style="display:none;"><h1>0</h1></div>
    </div>
  </div>
    <h2 class="TAG"><B>TEMPLATE MANAGEMENT</B></h2>
    <div class="table-container" id="loadpage">
        <a class="btn btn-success" style="float:left;margin-right:20px;margin-left:20px;" href="./Index.php">
        <i class="fa fa-home"></i> Back to Home page</a>
        <?php
        include './auth/subClass.php';
        $SCADA = new SCADA();
        if($SCADA->checkRoles("2,4") == 'error'){
            header("Location: ./Index.php?PermissionError=403");
        }else{
            echo $SCADA->getTemplate();
        }
        ?>
    </div>
</body>

</html>