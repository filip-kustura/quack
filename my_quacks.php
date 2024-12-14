<?php
    require_once 'db.class.php';
    require_once 'common_functions.php';
    
    session_start();
    
    initial_commands();

    $database = DB::getConnection();

    if (isset($_POST['post-button'])) {
        if (empty($_POST['new-quack']))
            $error_message = 'Quack cannot be empty.';
        else if (strlen($_POST['new-quack']) <= 140)
            handleNewQuack();
        else
            $error_message = 'Unexcpected error occurred while posting a new quack.';
    } else {
        $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
        unset($_SESSION['error_message']);
    }
    
    $user_quacks_statement = getUserQuacks();
    
    // Dohvaca korisnikove quackove koje je dosad objavio
    function getUserQuacks() {
        $id_user = getUserIdByUsername($_SESSION['username']);

        global $database;
        try {
            $statement = $database->prepare(
                'SELECT username, date, quack 
                FROM dz2_quacks, dz2_users
                WHERE id_user = :id_user 
                AND dz2_users.id = dz2_quacks.id_user
                ORDER BY date DESC;'
            );
    
            $statement->execute(
                array(
                    'id_user' => $id_user
                )
            );
        } catch (PDOException $e) {
            unset($_SESSION['username']);
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: login.php');
            exit();
        }

        return $statement;
    }

    // Obradjuje pokusaj objave novog quacka
    function handleNewQuack() {
        global $database;
        $id_user = getUserIdByUsername($_SESSION['username']);
        
        try {
            $statement = $database->prepare(
                'INSERT INTO dz2_quacks (id_user, quack, date) 
                VALUES (:id_user, :quack, :date);'
            );
            
            $date = date('Y-m-d H:i:s');
            $date = date('Y-m-d H:i:s', strtotime($date . '+2 hours'));
            $statement->execute(
                array(
                    'id_user' => $id_user, 
                    'quack' => $_POST['new-quack'], 
                    'date' => $date
                )
            );

            unset($_POST['new-quack']);
            $_SESSION['executed_well'] = 'New quack posted!';
        } catch (PDOException $e) {
            $error_message = $e->getMessage();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Quacks</title>

    <link rel="stylesheet" href="quack.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Pacifico">
</head>
<body>
    <?php
        display_header_and_nav();
    ?>

    <?php 
        if (!empty($error_message)) echo '<p id=warning>' . $error_message . '</p>';

        if (isset($_SESSION['executed_well'])) {
            echo '<p id=executed-well>' . $_SESSION['executed_well'] . '</p>';
            unset($_SESSION['executed_well']);
        }
    ?>
    
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <textarea maxlength="140" name="new-quack" id="new-quack-textarea" cols="60" rows="5" placeholder="What's on your mind, @<?php echo $_SESSION['username']; ?>?"><?php if (isset($_POST['new-quack'])) echo $_POST['new-quack']; ?></textarea>
        <input type="submit" value="Post" name="post-button">
    </form>

    <h2>My quacks:</h2>
    <?php
        displayQuacks($user_quacks_statement);
    ?>
</body>
</html>