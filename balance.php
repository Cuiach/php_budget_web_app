<?php
session_start();

if (!isset($_SESSION['logged_id'])) 
{
	header('Location: index.php');
	exit();
}
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Budget app</title>
	
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
	
	<form method="post" action="bshow.php">
		
		Date from: <br /> <input type="date" value="<?php
			if (isset($_SESSION['fr_date_from']))
			{
				echo $_SESSION['fr_date_from'];
				unset($_SESSION['fr_date_from']);
			}
		?>" name="date_from" /><br />
		
		<?php
			if (isset($_SESSION['e_date_from']))
			{
				echo '<div class="error">'.$_SESSION['e_date_from'].'</div>';
				unset($_SESSION['e_date_from']);
			}
		?>

		Date to: <br /> <input type="date" value="<?php
			if (isset($_SESSION['fr_date_to']))
			{
				echo $_SESSION['fr_date_to'];
				unset($_SESSION['fr_date_to']);
			}
		?>" name="date_to" /><br />
		
		<?php
			if (isset($_SESSION['e_date_to']))
			{
				echo '<div class="error">'.$_SESSION['e_date_to'].'</div>';
				unset($_SESSION['e_date_to']);
			}
		?>

		<br />
		
		<input type="submit" value="Show balance" />
		
	</form>
	
	<p><a href="app.php">Go back to the main page</a></p>

</body>
</html>