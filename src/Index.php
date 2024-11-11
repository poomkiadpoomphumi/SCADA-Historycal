<?php
include("./auth/subClass.php");
session_start();
if (!$_SESSION["USER"]) {
    header("Location: ../Login.php");
} else {
    $gUserName = $_SESSION["USER"];
    $tmplID = isset($_POST["cboTemplate"]);
    $lstRTU = isset($_POST["lstRTU"]);
    $SCADA = new SCADA();
    $SCADA->openDB();
    $SCADA->OpenDebug();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Historical Operation Data Web Application</title>
    <link rel="shortcut icon" href="../img/favicon.png" type="image/gif" sizes="16x16">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="../js/jquery-3.5.1.min.js"></script>
    <script src="../vendor/sweetalert/sweetalert2.js"></script>
    <link rel="stylesheet" type="text/css" href="../css/Index.css" />
    <script src="../js/Main.js"></script>
    <link rel="stylesheet" type="text/css" href="../vendor/boostrap4/boostrap.min.css" />
    <script src="../vendor/boostrap4/boostrap.min.js"></script>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <!--     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous" /> -->
    <!--     <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <title>Historical Operation Data Web Application</title> -->
</head>
<style>
/* container */
.container {
  top:25px;
  max-width:95%;
  height:100%;
  margin: auto;
  position: relative;
  background-color:rgba(0, 0, 0, 0.4) !important;
  color:#fff;
  font-family: Arial, sans-serif;
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

.mySlides{
  width: 100%;
  position: absolute;
  left: 0;
  top: 0;
  display: block;
  transition: 0.3s opacity ease-in-out;
}


/* Set height for screens up to 768px (standard desktop) */
@media only screen and (max-width: 768px) {
  .container, .mySlides {
    height: 300px;
  }
}

/* Set height for screens between 768px and 992px (larger desktop) */
@media only screen and (min-width: 769px) and (max-width: 992px) {
  .container, .mySlides {
    height: 400px;
  }
}

/* Set height for screens larger than 992px (larger desktop) */
@media only screen and (min-width: 993px) {
  .container, .mySlides {
    height: 500px;
  }
}
</style>
<script src="../js/Loader.js"></script>
<script>
$(document).ready(function () {
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('Todate').max = today;
  document.getElementById('Todate-gmdr').max = today;
});
document.addEventListener('DOMContentLoaded', function() {
  const inputtime = document.getElementById('Totime');
  const inputtime_gmdr = document.getElementById('Totime-gmdr');
  inputtime.addEventListener('input', function() {
    check(inputtime);
  });
  inputtime_gmdr.addEventListener('input', function() {
    check(inputtime_gmdr);
  });
  const check = async (e) => {
    const now = new Date();
    const hours = now.getHours();
    const minutes = now.getMinutes();
    const current_time = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
    if(e.value > current_time){
      e.value = current_time;
        Swal.fire({
          title: "Max time",
          html: "Time is current. There is no time in the future!",
          icon: "warning",
          confirmButtonColor: "#3085d6",
          confirmButtonText: "Ok"
        })
    }
  }
});



</script>
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
    <?php include('../components/Navbar.php');?>
      <div class="container" id="loadpage">
        <div class="row">
          <div class="col">
          <br><br><br><br><br>
            <p style="padding-top:15px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Wellcome to Historical Operation Data Web Application</b>
            <br>
            It is a website to view past operations and you can create templates as you like. Currently under testing if you have any problems, please email poomkiad@pttplc.com</p>
          </div>
          <div class="col">
              <img class="mySlides" src="../img/BG3.jpg">
              <img class="mySlides" src="../img/BG2.jpg">
              <img class="mySlides" src="../img/BG1.jpg">
              <img class="mySlides" src="../img/BG4.jpg">
              <img class="mySlides" src="../img/BG5.jpg">
              <img class="mySlides" src="../img/BG6.jpg">
          </div>
        </div>
      </div>
    <footer>
        <div class="footer-content">
            <p>
		Copyright © 2023 PTT Public Company Limited. All rights reserved.
    PTT Gas Operation Contact Center Tel. 1540</p>
        </div>
    </footer>
<?php include('../components/Modal.php');?>
</body>
<script>
let myIndex = 0;
carousel();
function carousel() {
  let i;
  let x = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  for (i = 0; i < x.length; i++) {
    x[i].style.opacity = 0;
  }
  myIndex++;
  if (myIndex > x.length) {myIndex = 1}
  x[myIndex - 1].style.opacity = 1;
  setTimeout(carousel, 5000);
}
</script>
</html>
<?php include('../components/Notification.php');