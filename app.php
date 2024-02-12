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
			header('Location: login.php');
			exit();
		}
			
	} else {
		
		header('Location: login.php');
		exit();
	}
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Budget application</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta http-equiv="X-Ua-Compatible" content="IE=edge">

</head>

<body>

    <div class="container">

        <header>
            <h1>Budget app</h1>
        </header>

        <main>
            <article>
			
				<table>
					<thead>
						<tr><th colspan="3">What you want to do is:</th></tr>
						<tr><th><a href="income.php">add income</a></th><th><a href="expense.php">add outcome</a></th><th><a href="balance.php">check balance</a></th></tr>
					</thead>
					<tbody>
						<?php
						?>
					</tbody>
				</table>
				
				<p><a href="logout.php">Log out</a></p>
				
            </article>
        </main>

    </div>

</body>
</html>