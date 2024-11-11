<?php 
$isMobile = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mobile"));
if ($isMobile) {
    header("Location: ./error.html");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="./css/Login.css" />
    <script src="./js/Main.js"></script>
    <title>Historical Operation Data Web Application</title>
    <link rel="shortcut icon" href="./img/favicon.png" type="image/gif" sizes="16x16">
</head>
<body>
    <div class="form-login">
        <div class="login-container">
            <form action="./src/auth/PostFile.php" method="post" name="frmLogin">
                <?php if(isset($_GET['Incor']) && $_GET['Incor'] === 'Incor'){ ?>
                <script>
                setTimeout(function() {
                  document.getElementById("alert").style.display = 'none';
                  if (typeof window.history.pushState == 'function') {
                    window.history.pushState({}, "Hide", "Login.php");
                  }
                }, 1500);
                </script>
                    <div class="alert alert-danger" role="alert" id="alert">
                        Incorrect username or password.
                    </div>
                <?php 
                    }else if(isset($_GET['Insuff']) && $_GET['Insuff'] === 'Insuff'){ ?>
                    <script>
                    setTimeout(function() {
                      document.getElementById("alert").style.display = 'none';
                      if (typeof window.history.pushState == 'function') {
                        window.history.pushState({}, "Hide", "Login.php");
                      }
                    }, 1500);
                    </script>
                    <div class="alert alert-danger" role="alert" align='center' id="alert">
                        Insufficient privileges. Please contact administrator.
                    </div>
                <?php  } ?>
                <label for="email-field">Username<span style="color: red;">*</span></label>
                <input type="text" placeholder="PTT Employee ID" name="username" required>
                <label for="email-field">Password<span style="color: red;">*</span></label>
                <input type="password" placeholder="Password" name="password" required>
                <br><br>
                <button class="login-button" type="submit">Sign in</button>
            </form>
            <br>
            <hr class="line">
            <div class="footer">
                <p><a href="javascript:void(0);" style="font-size:14px;">กรณี Login ด้วยรหัสพนักงาน ให้ใช้ Password เดียวกับ ESS</a></p>
            </div>
        </div>
        <div>
</body>

</html>