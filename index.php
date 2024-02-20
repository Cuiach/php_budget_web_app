<?php

session_start();
	
if (isset($_SESSION['logged_id'])) 
{
	header('Location: app.php');
	exit();
}
	
if (isset($_POST['email']))
{

	$allRight=true;
	
	$username = $_POST['username'];
	
	if ((strlen($username)<3) || (strlen($username)>50))
	{
		$allRight=false;
		$_SESSION['e_username']="User name or nick should have 3 to 50 signs!";
	}
	
	if (ctype_alnum($username)==false)
	{
		$allRight=false;
		$_SESSION['e_username']="User name or nick can exclusively have Latin letters or digits";
	}
	
	$email = $_POST['email'];
	$emailB = filter_var($email, FILTER_SANITIZE_EMAIL);
	
	if ((filter_var($emailB, FILTER_VALIDATE_EMAIL)==false) || ($emailB!=$email))
	{
		$allRight=false;
		$_SESSION['e_email']="Put correct e-mail";
	}

	$pass1 = $_POST['pass1'];
	$pass2 = $_POST['pass2'];
	
	if ((strlen($pass1)<3) || (strlen($pass1)>20))
	{
		$allRight=false;
		$_SESSION['e_pass']="Password should have 3 to 20 letters";
	}
	
	if ($pass1!=$pass2)
	{
		$allRight=false;
		$_SESSION['e_pass']="Passwords are not identical";
	}	

	$pass_hash = password_hash($pass1, PASSWORD_DEFAULT);
	
	if (!isset($_POST['terms_of_service']))
	{
		$allRight=false;
		$_SESSION['e_terms_of_service']="Make sure you accept the terms of service";
	}				
	
	include "config1.php";	
	$check_it = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$sekret.'&response='.$_POST['g-recaptcha-response']);

	$reply = json_decode($check_it);
	
	if ($reply->success==false)
	{
		$allRight=false;
		$_SESSION['e_bot']="Confirm you are not a bot :)";
	}		


	$_SESSION['fr_username'] = $username;
	$_SESSION['fr_email'] = $email;
	$_SESSION['fr_pass1'] = $pass1;
	$_SESSION['fr_pass2'] = $pass2;
	if (isset($_POST['terms_of_service'])) $_SESSION['fr_terms_of_service'] = true;		
	
	require_once 'database.php';

	if($allRight)
	{
		try
		{
			$userQuery = $db->prepare('INSERT INTO users (username, password, email) VALUES (:username, :pass1, :email)');
			$userQuery->bindValue(':username', $username, PDO::PARAM_STR);
			$userQuery->bindValue(':pass1', $pass_hash, PDO::PARAM_STR);
			$userQuery->bindValue(':email', $email, PDO::PARAM_STR);
			$userQuery->execute();
			

			$userQuery = $db->prepare('SELECT * FROM users ORDER BY id DESC LIMIT 1');
			$userQuery->execute();
			
			$row = $userQuery->fetch();
			
			$user_id = $row['id'];


			$userQuery = $db->prepare('INSERT INTO incomes_category_assigned_to_users (name, user_id) SELECT incomes_category_default.name, users.id FROM incomes_category_default, users WHERE users.id = :user_id');
			$userQuery->bindValue(':user_id', $user_id, PDO::PARAM_STR);
			$userQuery->execute();
			
			$userQuery = $db->prepare('INSERT INTO expenses_category_assigned_to_users (name, user_id) SELECT expenses_category_default.name, users.id FROM expenses_category_default, users WHERE users.id = :user_id');
			$userQuery->bindValue(':user_id', $user_id, PDO::PARAM_STR);
			$userQuery->execute();
			

			header('Location: index.php');
			exit();
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}

	} else
	{
		echo "Signing up was not successful";
	}
}	
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register or login choice</title>
	<script src='https://www.google.com/recaptcha/api.js'></script>
    <meta name="description" content="budget app">
    <meta name="keywords" content="budget app">
    <meta http-equiv="X-Ua-Compatible" content="IE=edge">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <header>
        <div class="row p-4 pb-0 pe-lg-0 pt-lg-5 align-items-center rounded-3" >        
            <h1 class="display-4 fw-bold lh-1 text-center mb-0">Home budget app</h1>
        </div>
    </header>
    <section>
        <div class="row p-4 pb-0 pe-lg-0 pt-lg-5 align-items-center rounded-3" >
            <div class="col-lg p-3 p-lg-5 pt-lg-3">
                <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-4 mb-lg-3">
                    <button type="button" class="btn btn-primary btn-lg px-4 me-md-2 buttonColors"  
                    data-bs-toggle="modal" data-bs-target="#modalSignin" id="experimental">Register</button>                    
                    <button type="button" class="btn btn-primary btn-lg px-4 me-md-2 buttonColors"
                    data-bs-toggle="modal" data-bs-target="#modalLogin" id="experimental">Log into</button>
                </div>
                <div class="modal fade" id="modalSignin" tabindex="-1" aria-labelledby="signup" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <button type="button" class="btn-close position-sticky top-0 start-100 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button> <!-- float-end would work too-->
                            </div>
                            <div class="modal-body pt-1">
                                <form class="" method="post">
                                    
									<div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-3" id="floatingInput1" placeholder="User" type="submit" action="page_submission_URL" required value="<?php
										if (isset($_SESSION['fr_username']))
										{
											echo $_SESSION['fr_username'];
											unset($_SESSION['fr_username']);
										}
									?>" name="username" /><br />
									
									<?php
										if (isset($_SESSION['e_username']))
										{
											echo '<div class="error">'.$_SESSION['e_username'].'</div>';
											unset($_SESSION['e_username']);
										}
									?>
									
									<label for="floatingInput">User nick 
									
									</label>
                                    </div>
									
									<div class="form-floating mb-3">
                                    <input type="email" class="form-control rounded-3" id="floatingInput1" placeholder="name@example.com" type="submit" action="page_submission_URL" required value="<?php
										if (isset($_SESSION['fr_email']))
										{
											echo $_SESSION['fr_email'];
											unset($_SESSION['fr_email']);
										}
									?>" name="email" /><br />
									
									<?php
										if (isset($_SESSION['e_email']))
										{
											echo '<div class="error">'.$_SESSION['e_email'].'</div>';
											unset($_SESSION['e_email']);
										}
									?>
									
                                    <label for="floatingInput">Email address 
									
									</label>
                                    </div>
                                    
									<div class="form-floating mb-3">
                                    <input type="password" class="form-control rounded-3" id="floatingPassword1" placeholder="Password" value="<?php
										if (isset($_SESSION['fr_pass1']))
										{
											echo $_SESSION['fr_pass1'];
											unset($_SESSION['fr_pass1']);
										}
									?>" name="pass1" /><br />
									
									<?php
										if (isset($_SESSION['e_pass']))
										{
											echo '<div class="error">'.$_SESSION['e_pass'].'</div>';
											unset($_SESSION['e_pass']);
										}
									?>
									
                                    <label for="floatingPassword">Password</label>
                                    </div>
                                    
									<div class="form-floating mb-3">
                                    <input type="password" class="form-control rounded-3" id="floatingPassword2" placeholder="Password" value="<?php
										if (isset($_SESSION['fr_pass2']))
										{
											echo $_SESSION['fr_pass2'];
											unset($_SESSION['fr_pass2']);
										}
									?>" name="pass2" /><br />
									
                                    <label for="floatingPassword">Repeat password</label>
                                    </div>
									
									<div>
									<input type="checkbox" name="terms_of_service" <?php
										if (isset($_SESSION['fr_terms_of_service']))
										{
											echo "checked";
											unset($_SESSION['fr_terms_of_service']);
										}
									?>/> I accept the terms of service.
									</div>
									
									<?php
										if (isset($_SESSION['e_terms_of_service']))
										{
											echo '<div class="error">'.$_SESSION['e_terms_of_service'].'</div>';
											unset($_SESSION['e_terms_of_service']);
										}
									?>	
									
									<div class="g-recaptcha" data-sitekey="6LeqSHApAAAAAOo_F_g5oqewMCabB8O3WUc0G30k"></div> <!--//web-->
							<!--		<div class="g-recaptcha" data-sitekey="6LcDaVEpAAAAABQePcL_MWsNmm6PlUd88oF_ogzU"></div> <!--//localhost-->
									
									<?php
										if (isset($_SESSION['e_bot']))
										{
											echo '<div class="error">'.$_SESSION['e_bot'].'</div>';
											unset($_SESSION['e_bot']);
										}
									?>
									
									<button class="w-100 mb-2 btn btn-lg rounded-3 btn-primary buttonColors" type="submit" id="experimental" value="Sign up">Sign up</button>
                                    <small class="text-body-secondary">By clicking Sign up, you agree to the terms of use.</small>
		
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="signup" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <button type="button" class="btn-close position-sticky top-0 start-100 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button> <!-- float-end would work too-->
                            </div>
                            <div class="modal-body pt-1">
                                <form class="" method="post" action="app.php">
                                    <div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-3" id="floatingInput2" placeholder="User" required name="login">
                                    <label for="floatingInput">Login</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                    <input type="password" class="form-control rounded-3" id="floatingPassword3" placeholder="Password" required name="pass">
                                    <label for="floatingPassword">Password</label>
                                    </div>
                                    <button class="w-100 mb-2 btn btn-lg rounded-3 btn-primary buttonColors" type="submit" id="experimental" value="Log in!">Log in</button>
									
									<?php
									if (isset($_SESSION['bad_attempt'])) 
									{
										echo '<p>Login or password is not valid!</p>';
										unset($_SESSION['bad_attempt']);
									}
									?>
									
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>