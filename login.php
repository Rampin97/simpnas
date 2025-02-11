<?php 
  
$config = include("config.php");
include("simple_vars.php");

session_start();

if(isset($_POST['login'])){
  
  $username = $_POST['username'];
  $password = $_POST['password'];

  $logged_in = exec("bash /simpnas/verify.sh $username $password");

  if($logged_in == 1){
    $_SESSION['username'] = $username;
    $_SESSION['logged'] = TRUE;
    header("Location: dashboard.php");
  }else{
    $response = "
      <div class='alert alert-danger'>
        Incorrect username or password.
        <button class='close' data-dismiss='alert'>&times;</button>
      </div>
    ";
  }
}

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo gethostname(); ?> login</title>

    <!-- Bootstrap core CSS -->
    <link href="src/public/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="src/public/assets/plugins/fontawesome-free/css/all.min.css">

    <!-- Custom styles for this template -->
    <link href="src/public/assets/css/signin.css" rel="stylesheet">
  </head>

  <body class="text-center">
    <form class="form-signin" method="post">
      <h1 class="mb-3"><i class="fa fa-cube"></i> SimpNAS<br><small class="text-secondary"><?php echo gethostname(); ?></small></h1>
      <div id ="alert">
        <?php 
        if(!empty($response)){
          echo $response;
        }
        ?>
      </div>
      <input type="text" id="inputUsername" name="username" class="form-control" placeholder="Username" required autofocus>
      <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" required>
      <button type="submit" class="btn btn-primary p-2 btn-block" name="login">Login</button>
      <a href="http://<?php echo $config_primary_ip; ?>:82" class="btn btn-secondary p-2 btn-block"><i class="fa fa-folder"></i> File Manager</a>
    </form>
    </form>
  
<?php include("footer.php"); ?>
