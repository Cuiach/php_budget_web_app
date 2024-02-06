<?php
session_start();

if (isset($_SESSION['logged_id'])) 
{
	header('Location: app.php');
	exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Administration panel</title>
    <meta name="description" content="Log in to admin panel">
    <meta name="keywords" content="budget app">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

</head>

<body>
    <div class="container">

        <header>
            <h1>Admin</h1>
        </header>

        <main>
            <article>
                <form method="post" action="app.php">
                    <label>Login <input type="text" name="login"></label>
                    <label>Password <input type="password" name="pass"></label>
                    <input type="submit" value="Log in!">
					
					<?php
					if (isset($_SESSION['bad_attempt'])) 
					{
						echo '<p>Login or password is not valid!</p>';
						unset($_SESSION['bad_attempt']);
					}
					?>
					
                </form>
            </article>
        </main>

    </div>
</body>
</html>