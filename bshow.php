<?php
session_start();

if (!isset($_SESSION['logged_id'])) 
{
	header('Location: index.php');
	exit();
}

if (isset($_POST['date_from']) || isset($_POST['this_month']) || isset($_POST['last_month']))
{	
	$allRight = true;	

	if (isset($_POST['date_from']))
	{
		$date_from = $_POST['date_from'];
		$date_to = $_POST['date_to'];
	} else if (isset($_POST['this_month']))
	{
		$date_from = date('Y-m-01');
		$date_to = date('Y-m-d');
	} else
	{
		$date_from = date("Y-m-d", mktime(0, 0, 0, date("m")-1, 1));
		$date_to = date("Y-m-d", mktime(0, 0, 0, date("m"), 0));
	}

	($date_from > date('Y-m-d') || $date_from < '2020-01-01') ? $allRight = false : 1;
		
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
			
			$title = "Date range from: {$date_from} to: {$date_to}";
			
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}

	} else
	{
		echo "Something went wrong";
		exit();
	}	
} else
{
}
	
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Home budget application</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
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
	<!--range date balance-->			
		<div class="modal fade" id="rangeDateBalance" tabindex="-1" aria-labelledby="signup" aria-hidden="true" <!--data-bs-keyboard="false" data-bs-backdrop="static"-->>
			<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-body p-0">
						<a href="app.php"><button type="button" class="btn-close position-sticky top-0 start-100 mt-2 me-2" aria-label="Close"></button></a>
					</div>
					<div class="modal-body pt-1">
						<form class="">
							<div class="form-floating mb-3">
								<output class="rounded-3">
									<div mt-0 pt-0>
										
										<table>
											<thead>
												<?php 
													echo "$title";
												?>
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
									</div>
								</output>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
    </section>
	
<script src="bshow.js" charset="utf-8"></script>

</body>
</html>
