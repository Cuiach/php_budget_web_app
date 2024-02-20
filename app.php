<?php
session_start();

require_once 'database.php';

if (!isset($_SESSION['logged_id'])) {

	if (isset($_POST['login'])) {
		
		$login = filter_input(INPUT_POST, 'login');
		$password = filter_input(INPUT_POST, 'pass');
		
		$userQuery = $db->prepare('SELECT id, password FROM users WHERE username = :login');
		$userQuery->bindValue(':login', $login, PDO::PARAM_STR);
		$userQuery->execute();
		
		$user = $userQuery->fetch();
		
		if ($user && password_verify($password, $user['password'])) {
			$_SESSION['logged_id'] = $user['id'];
			unset($_SESSION['bad_attempt']);
		} else {
			$_SESSION['bad_attempt'] = true;
			header('Location: index.php');
			exit();
		}
			
	} else {
		
		header('Location: index.php');
		exit();
	}
}


function floatvalue($val){
            $val = str_replace(",",".",$val);
            $val = preg_replace('/\.(?=.*\.)/', '', $val);
            return floatval($val);
}

// add income
if (isset($_POST['income_amount']))
{

	$allRight=true;	
	$income_amount_as_text = $_POST['income_amount'];
	$income_amount = 0;

	try
	{
		$raw_income_amount = floatvalue($income_amount_as_text);
		$income_amount = floatval(number_format($raw_income_amount, 2, '.', ''));		
	}
	catch(pdoexception $e)
	{
		echo $e->getmessage();
	}
		
	if ($income_amount == 0)
	{
		$allRight = false;
		$_SESSION['e_income_amount']="Correct the amount, please.";
	}
	
	$date_from_user = $_POST['income_date'];
	($date_from_user > date('Y-m-d') || $date_from_user < '2020-01-01') ? $allRight = false : 1;
	
	$category = $_POST['category']; 
	//zamiast tego bedzie dropdown
	
	$comment = $_POST['comment'];
	
	$_SESSION['fr_income_amount'] = $income_amount_as_text;
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
			$dbquery->bindValue(':amount', $income_amount, PDO::PARAM_STR);
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

//add expense
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
	
	$date_from_user = $_POST['expense_date'];
	($date_from_user > date('Y-m-d') || $date_from_user < '2020-01-01') ? $allRight = false : 1;
	
	$category = $_POST['category']; 
	//zamiast tego bedzie dropdown

	$payment = $_POST['payment'];
	//zamiast tego bedzie dropdown

	$comment = $_POST['comment'];
	
	$_SESSION['fr_amount'] = $amount_as_text;
	$_SESSION['fr_date'] = $date_from_user;
	$_SESSION['fr_category'] = $category;
	$_SESSION['fr_payment'] = $payment;
	$_SESSION['fr_comment'] = $comment;
	
	require_once 'database.php';

	if ($allRight)
	{
		try
		{
			$sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment) VALUES (:user_id, :expense_category_assigned_to_user_id, :payment_method_assigned_to_user_id, :amount, :date_of_expense, :expense_comment)';

			$dbquery = $db->prepare($sql);
			$dbquery->bindValue(':user_id', $_SESSION['logged_id'], PDO::PARAM_INT);
			$dbquery->bindValue(':expense_category_assigned_to_user_id', $category, PDO::PARAM_INT);
			$dbquery->bindValue(':payment_method_assigned_to_user_id', $payment, PDO::PARAM_INT);
			$dbquery->bindValue(':amount', $amount, PDO::PARAM_STR);
			$dbquery->bindValue(':date_of_expense', $date_from_user, PDO::PARAM_STR);
			$dbquery->bindValue(':expense_comment', $comment, PDO::PARAM_STR);
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
        <div class="row p-4 pb-0 pe-lg-0 pt-lg-5 rounded-3">
            <div class="col-lg p-3 p-lg-5 pt-lg-3">
                <nav class="navbar navbar-expand-lg pt-0">
                    <div class="mx-auto d-block flex-sm-nowrap mt-0">
                        <button class="navbar-toggler buttonColors m-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                <li class="nav-item">
                                    <a class="nav-link active" aria-current="page" href="./app.php">
                                        <button type="button" class="btn btn-primary btn-lg buttonColors text-nowrap" id="experimental">Main page</button>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <div class="nav-link">
                                        <button type="button" class="btn btn-primary btn-lg buttonColors text-nowrap"  
                                        data-bs-toggle="modal" data-bs-target="#modalAddIncome" id="experimental">Add income</button>
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <div class="nav-link">
                                        <button type="button" class="btn btn-primary btn-lg buttonColors text-nowrap me-2"  
                                        data-bs-toggle="modal" data-bs-target="#modalAddOutcome" id="experimental">Add outcome</button>
                                    </div>
                                </li>
                                <li class="nav-item dropdown">
                                        <button type="button" class="btn btn-primary btn-lg buttonColors text-nowrap dropdown-toggle me-3 my-2" data-bs-toggle="dropdown" aria-expanded="false"  
                                        data-bs-toggle="modal" data-bs-target="#modalSignin" id="experimental">Check balance</button>
                                    <ul class="dropdown-menu" data-bs-theme="light" id="experimental">
                                    <li><a href="bshow.php">
										<form method="post" action="bshow.php" class="dropdown-item">
											<button name="this_month" style="border:none; background-color:transparent; padding-left: 0">This month</button>
										</form></a></li>

                                    <li><a href="bshow.php">
										<form method="post" action="bshow.php" class="dropdown-item" >
											<button type="hidden" name="last_month" style="border:none; background-color:transparent; padding-left: 0">
												Last month
											</button>
										</form></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li> <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#dateRangePicker">Choose date range</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <button type="button" class="btn btn-primary btn-lg buttonColors text-nowrap dropdown-toggle my-2" data-bs-toggle="dropdown" aria-expanded="false"  
                                    data-bs-toggle="modal" data-bs-target="#modalSignin" id="experimental">Settings</button>
                                    <ul class="dropdown-menu" data-bs-theme="light" id="experimental">
                                    <li><a class="dropdown-item" href="#">Change password</a></li>
                                    <li><a class="dropdown-item" href="logout.php">Log out</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </section>

    <section data-bs-theme="light">
<!--add income-->
        <article>
            <div class="modal fade" id="modalAddIncome" tabindex="-1" aria-labelledby="signup" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body p-0">
                            <button type="button" class="btn-close position-sticky top-0 start-100 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button> <!-- float-end would work too-->
                        </div>
                        <div class="modal-body pt-1">
                            <form class="" method="post">
                                
								<div class="form-floating mb-3">
                                    <input type="number" step="0.01" class="form-control rounded-3" id="floatingAmount" placeholder="Amount" min="0.01" required value="<?php
										if (isset($_SESSION['fr_income_amount']))
										{
											echo $_SESSION['fr_income_amount'];
											unset($_SESSION['fr_income_amount']);
										}
									?>" name="income_amount" /><br />
									
									<?php
										if (isset($_SESSION['e_income_amount']))
										{
											echo '<div class="error">'.$_SESSION['e_income_amount'].'</div>';
											unset($_SESSION['e_income_amount']);
										}
									?>
                                    <label for="floatingAmount">Amount</label>
                                </div>
								
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-3" id="floatingText" placeholder="Text" required value="<?php
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
                                    <label for="floatingText">Category</label>
                                </div>
								
								<div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-3" id="floatingText" placeholder="Text" value="<?php
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
                                    <label for="floatingText">Comment</label>
                                </div>
								
                                <div class="form-left mb-3">
                                    <input type="date" class="form-control rounded-3" id="floatingDate" placeholder="Date" required value="<?php
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
                                    <label for="floatingDate">Date</label>
                                </div>
								
                                <button class="w-100 mb-2 btn btn-lg rounded-3 btn-primary buttonColors" type="submit" id="experimental" value="Add income">Add</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </article>
<!--add expense-->
        <article>
            <div class="modal fade" id="modalAddOutcome" tabindex="-1" aria-labelledby="signup" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body p-0">
                            <button type="button" class="btn-close position-sticky top-0 start-100 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button> <!-- float-end would work too-->
                        </div>    
                        <div class="modal-body pt-1">
                            <form method="post">
                                <div class="form-floating mb-3">
                                    <input type="number" step="0.01" class="form-control rounded-3" id="floatingAmount" placeholder="Amount" min="0.01" required value="<?php
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
                                    <label for="floatingAmount">Amount</label>
                                </div>
								
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-3" id="floatingText" placeholder="Text" required value="<?php
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
                                    <label for="floatingText">Category</label>
                                </div>
								
								<div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-3" id="floatingText" placeholder="Text" required value="<?php
									if (isset($_SESSION['fr_payment']))
										{
											echo $_SESSION['fr_payment'];
											unset($_SESSION['fr_payment']);
										}
									?>" name="payment" /><br />
									
									<?php
										if (isset($_SESSION['e_payment']))
										{
											echo '<div class="error">'.$_SESSION['e_payment'].'</div>';
											unset($_SESSION['e_payment']);
										}
									?>
                                    <label for="floatingText">Payment type</label>
                                </div>
								
								<div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-3" id="floatingText" placeholder="Text" value="<?php
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
                                    <label for="floatingText">Comment</label>
                                </div>

                                <div class="form-floating mb-3" data-mdb-datepicker-init data-mdb-input-init data-mdb-inline="true">
                                    <input type="date" class="form-control rounded-3" id="floatingDate" placeholder="Date" required value="<?php
									if (isset($_SESSION['fr_date']))
										{
											echo $_SESSION['fr_date'];
											unset($_SESSION['fr_date']);
										}
									?>" name="expense_date" /><br />
									
									<?php
										if (isset($_SESSION['e_date']))
										{
											echo '<div class="error">'.$_SESSION['e_date'].'</div>';
											unset($_SESSION['e_date']);
										}
									?>
                                    <label for="exampleDatepicker2" class="form-label">Select a date</label>
                                </div>
								
                                <button class="w-100 mb-2 btn btn-lg rounded-3 btn-primary buttonColors" type="submit" id="experimental" value="Add expense">Add</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </article>
<!-- check date range balance -->
        <article>
            <div class="modal fade" id="dateRangePicker" tabindex="-1" aria-labelledby="signup" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body p-0">
                            <button type="button" class="btn-close position-sticky top-0 start-100 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-1">
                            <form class="" method="post" action="bshow.php">
                                <div class="form-floating mb-3" data-mdb-datepicker-init data-mdb-input-init data-mdb-inline="true">
                                    <input type="date" class="form-control rounded-3" id="floatingDate" placeholder="Date" required value="<?php
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
                                    <label for="exampleDatepicker2" class="form-label">Balance from:</label>
                                </div>
                                <div class="form-floating mb-3" data-mdb-datepicker-init data-mdb-input-init data-mdb-inline="true">
                                    <input type="date" class="form-control rounded-3" id="floatingDate" placeholder="Date" required value="<?php
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
                                    <label for="exampleDatepicker2" class="form-label">Balance to:</label>
                                </div>
                                <button class="w-100 mb-2 btn btn-lg rounded-3 btn-primary buttonColors" type="submit" id="experimental">Check balance</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </section>
</body>