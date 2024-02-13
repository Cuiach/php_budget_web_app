<?php

	session_start();
	
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
		
		$sekret = "6LeqSHApAAAAAM3a_xtNMDJTum4X4NX-DMXnBRn_"; //secret for web
		 
//		$sekret = "6LcDaVEpAAAAAGaZ-sXXhuG0gDHnSzsoqLYrYqT2"; //secret for localhost
		
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
				

				header('Location: login.php');
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

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Budget app</title>
	<script src='https://www.google.com/recaptcha/api.js'></script>
	
	<style>
		.error
		{
			color:blue;
			margin-top: 10px;
			margin-bottom: 10px;
		}
	</style>
</head>

<body>
	
	<form method="post">
	
		Your user name: <br /> <input type="text" value="<?php
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
		
		E-mail: <br /> <input type="text" value="<?php
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
		
		Password: <br /> <input type="password"  value="<?php
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
		
		Repeat password: <br /> <input type="password" value="<?php
			if (isset($_SESSION['fr_pass2']))
			{
				echo $_SESSION['fr_pass2'];
				unset($_SESSION['fr_pass2']);
			}
		?>" name="pass2" /><br />
		
		<label>
			<input type="checkbox" name="terms_of_service" <?php
			if (isset($_SESSION['fr_terms_of_service']))
			{
				echo "checked";
				unset($_SESSION['fr_terms_of_service']);
			}
				?>/> I accept the terms of service.
		</label>
		
		<?php
			if (isset($_SESSION['e_terms_of_service']))
			{
				echo '<div class="error">'.$_SESSION['e_terms_of_service'].'</div>';
				unset($_SESSION['e_terms_of_service']);
			}
		?>	
		
	<div class="g-recaptcha" data-sitekey="6LeqSHApAAAAAOo_F_g5oqewMCabB8O3WUc0G30k"></div> <!--//web-->
<!--	<div class="g-recaptcha" data-sitekey="6LcDaVEpAAAAABQePcL_MWsNmm6PlUd88oF_ogzU"></div> <!--//localhost-->
		
		<?php
			if (isset($_SESSION['e_bot']))
			{
				echo '<div class="error">'.$_SESSION['e_bot'].'</div>';
				unset($_SESSION['e_bot']);
			}
		?>	
		
		<br />
		
		<input type="submit" value="Sign up" />
		
	</form>

</body>
</html>