<?php
    session_start();

    // U db_settings.php su definirani $host, $database_name, $database_username i $database_password
    require_once 'db.class.php';

    if (isset($_SESSION['username'])) {
        header('Location: my_quacks.php');
        exit();
    }

    if (isset($_POST['login'])) {
        if (empty($_POST['username']) || empty($_POST['password']))
            $error_message = 'Please enter the login credentials.';

        if (!isset($error_message)) {
            handleLoginAttempt();
        } else {
            displayLoginForm($error_message);
            return;
        }
    } else {
        $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
        unset($_SESSION['error_message']);

        displayLoginForm($error_message);
        return;
    }

    // Obradjuje pokusaj korisnikovog ulogiravanja
    function handleLoginAttempt() {
        $database = DB::getConnection();

        try {
            $statement = $database->prepare(
                'SELECT username, password_hash 
                FROM dz2_users 
                WHERE username = :username;'
            );

            $statement->execute(
                array(
                    'username' => $_POST['username']
                )
            );
        } catch (PDOException $e) {
            displayLoginForm($e->getMessage());
            return;
        }

        $row = $statement->fetch();

        if ($row === false) {
            displayLoginForm('Non-existent user.');
            return;
        } else {
            if ($_POST['username'] !== $row['username']) {
                displayLoginForm('Non-existent user.');
                return;
            }
            $hash = $row['password_hash'];

            if (password_verify($_POST['password'], $hash)) {
                $_SESSION['username'] = $_POST['username'];
                header('Location: my_quacks.php');
                exit();
            } else {
                displayLoginForm('Invalid password.');
                return;
            }
        }
    }

    // Prikazuje formu za ulogiravanje
    function displayLoginForm($error_message = '') {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">

            <link rel="stylesheet" href="login.css" />
            <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Pacifico">
        </head>
        <body>
            <header>
                <div>
                    Quack!
                </div>
            </header>

            <?php
                if (!empty($error_message)) echo '<p id=warning>' . $error_message . '</p>';
            ?>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <p>
                    <label for="username-input">Username:</label> 
                    <input type="text" name="username" id="username-input">
                </p>
                <p>
                    <label for="password-input">Password:</label>
                    <input type="password" name="password" id="password-input">
                </p>
                <p>
                    <input type="submit" value="Login" name="login">
                </p>
            </form>
        </body>
        </html>
        <?php
    }
?>