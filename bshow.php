<?php
session_start();

if (!isset($_SESSION['logged_id'])) 
{
	header('Location: index.php');
	exit();
}

if (isset($_POST['date_from']))
{
	$allRight=true;	

	$date_from = $_POST['date_from'];
	($date_from > date('Y-m-d') || $date_from < '2020-01-01') ? $allRight = false : 1;
		
	$date_to = $_POST['date_to'];
	($date_to > date('Y-m-d') || $date_to < '2020-01-01') ? $allRight = false : 1;

	($date_from > $date_to) ? $allRight = false : 1;

	$_SESSION['fr_date_from'] = $date_from;
	$_SESSION['fr_date_to'] = $date_to;

	require_once 'database.php';

	if ($allRight)
	{
		try
		{
			$sql = 'SELECT
				incomes_category_assigned_to_users.name AS category, 
				SUM(incomes.amount) AS amount 
				FROM 
				incomes_category_assigned_to_users 
				INNER JOIN 
				incomes ON 
				incomes.income_category_assigned_to_user_id 
				= incomes_category_assigned_to_users.id 
				WHERE 
				incomes.user_id = :user_id
				AND 
				incomes.date_of_income BETWEEN :date_begin AND :date_end
				GROUP BY incomes.income_category_assigned_to_user_id 
				ORDER BY amount DESC';

			$dbquery = $db->prepare($sql);
			$dbquery->bindValue(':user_id', $_SESSION['logged_id'], PDO::PARAM_INT);
			$dbquery->bindValue(':date_begin', $date_from, PDO::PARAM_INT);
			$dbquery->bindValue(':date_end', $date_to, PDO::PARAM_INT);
			$dbquery->execute();
			
			$rows = $dbquery->fetchAll();
			
			$sql_expenses = 'SELECT
				expenses_category_assigned_to_users.name AS category, 
				SUM(expenses.amount) AS amount 
				FROM 
				expenses_category_assigned_to_users 
				INNER JOIN 
				expenses ON 
				expenses.expense_category_assigned_to_user_id 
				= expenses_category_assigned_to_users.id 
				WHERE 
				expenses.user_id = :user_id
				AND 
				expenses.date_of_expense BETWEEN :date_begin AND :date_end
				GROUP BY expenses.expense_category_assigned_to_user_id 
				ORDER BY amount DESC';

			$dbquery_expenses = $db->prepare($sql_expenses);
			$dbquery_expenses->bindValue(':user_id', $_SESSION['logged_id'], PDO::PARAM_INT);
			$dbquery_expenses->bindValue(':date_begin', $date_from, PDO::PARAM_INT);
			$dbquery_expenses->bindValue(':date_end', $date_to, PDO::PARAM_INT);
			$dbquery_expenses->execute();

			$rows_expenses = $dbquery_expenses->fetchAll();
			
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
		
    <div class="container">

        <header>
            <h1>Table</h1>
        </header>

        <main>
            <article>
			
				<table>
					<thead>
						<tr><th>category of incomes</th><th>amount</th></tr>
					</thead>
					<tbody>
						<?php
						$sum_of_incomes = 0;
						foreach ($rows as $row) {
							echo "<tr><td>{$row['category']}</td><td>{$row['amount']}</td></tr>";
							$sum_of_incomes+=$row['amount'];
						}
						echo "<tr><td>sum:</td><td>{$sum_of_incomes}</td></tr>";
						?>
					</tbody>
					<thead>
						<tr><th>category of expenses</th><th>amount</th></tr>
					</thead>
					<tbody>
						<?php
						$sum_of_expenses = 0;
						foreach ($rows_expenses as $row) {
							echo "<tr><td>{$row['category']}</td><td>{$row['amount']}</td></tr>";
							$sum_of_expenses+=$row['amount'];
						}
						echo "<tr><td>sum:</td><td>{$sum_of_expenses}</td></tr>";
						echo "<tr><td>---</td><td>---</td></tr>";
						$diff = $sum_of_incomes-$sum_of_expenses;
						echo "<tr><td>BALANCE:</td><td>{$diff}</td></tr>";
						?>
					</tbody>
				</table>
				
				<p><a href="app.php">Go back to the main page</a></p>
				
            </article>
        </main>

    </div>

</body>
</html>
