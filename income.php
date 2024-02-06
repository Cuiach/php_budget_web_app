<?php
function floatvalue($val){
            $val = str_replace(",",".",$val);
            $val = preg_replace('/\.(?=.*\.)/', '', $val);
            return floatval($val);
}

session_start();

if (!isset($_SESSION['logged_id'])) 
{
	header('Location: index.php');
	exit();
}

if (isset($_POST['amount']))
{

	$allRight=true;	
	$amount_as_text = $_POST['amount'];
	$amount = 0;

	try
	{
		$rawamount = floatvalue($amount_as_text);
		$amount = floatval(number_format($rawamount, 2, '.', ''));		
	}
	catch(pdoexception $e)
	{
		echo $e->getmessage();
	}
		
	if ($amount == 0)
	{
		$allRight = false;
		$_SESSION['e_amount']="Correct the amount, please.";
	}
	
	$date_from_user = $_POST['income_date'];
	($date_from_user > date('Y-m-d') || $date_from_user < '2020-01-01') ? $allRight = false : 1;
	
	$category = $_POST['category']; 
	//zamiast tego bedzie dropdown
	
	$comment = $_POST['comment'];
	
	$_SESSION['fr_amount'] = $amount_as_text;
	$_SESSION['fr_date'] = $date_from_user;
	$_SESSION['fr_category'] = $category;
	$_SESSION['fr_comment'] = $comment;
	
	require_once 'database.php';

	if ($allRight)
	{
		try
		{
			$sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment) VALUES (:user_id, :income_category_assigned_to_user_id, :amount, :date_of_income, :income_comment)';

			$dbquery = $db->prepare($sql);
			$dbquery->bindValue(':user_id', $_SESSION['logged_id'], PDO::PARAM_INT);
			$dbquery->bindValue(':income_category_assigned_to_user_id', $category, PDO::PARAM_INT);
			$dbquery->bindValue(':amount', $amount, PDO::PARAM_STR);
			$dbquery->bindValue(':date_of_income', $date_from_user, PDO::PARAM_STR);
			$dbquery->bindValue(':income_comment', $comment, PDO::PARAM_STR);
			$dbquery->execute();
			
			header('Location: app.php');
			exit();
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}

	} else
	{
		echo "Something went wrong";
	}
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
	
	<form method="post">
	
		Amount: <br /> <input type="number_format" value="<?php
			if (isset($_SESSION['fr_amount']))
			{
				echo $_SESSION['fr_amount'];
				unset($_SESSION['fr_amount']);
			}
		?>" name="amount" /><br />
		
		<?php
			if (isset($_SESSION['e_amount']))
			{
				echo '<div class="error">'.$_SESSION['e_amount'].'</div>';
				unset($_SESSION['e_amount']);
			}
		?>
		
		Date: <br /> <input type="date" value="<?php
			if (isset($_SESSION['fr_date']))
			{
				echo $_SESSION['fr_date'];
				unset($_SESSION['fr_date']);
			}
		?>" name="income_date" /><br />
		
		<?php
			if (isset($_SESSION['e_date']))
			{
				echo '<div class="error">'.$_SESSION['e_date'].'</div>';
				unset($_SESSION['e_date']);
			}
		?>
		
		Category: (tylko liczba odpowiadająca ..._id z bazy danych)<br /> <input type="number_format"  value="<?php
			if (isset($_SESSION['fr_category']))
			{
				echo $_SESSION['fr_category'];
				unset($_SESSION['fr_category']);
			}
		?>" name="category" /><br />
		
		<?php
			if (isset($_SESSION['e_category']))
			{
				echo '<div class="error">'.$_SESSION['e_category'].'</div>';
				unset($_SESSION['e_category']);
			}
		?>

		Comment: <br /> <input type="text"  value="<?php
			if (isset($_SESSION['fr_comment']))
			{
				echo $_SESSION['fr_comment'];
				unset($_SESSION['fr_comment']);
			}
		?>" name="comment" /><br />
		
		<?php
			if (isset($_SESSION['e_comment']))
			{
				echo '<div class="error">'.$_SESSION['e_comment'].'</div>';
				unset($_SESSION['e_comment']);
			}
		?>			

		<br />
		
		<input type="submit" value="Add income" />
		
	</form>

</body>
</html>