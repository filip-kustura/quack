<?php
    require_once 'db.class.php';
    require_once 'common_functions.php';

    session_start();

    initial_commands();

    $database = DB::getConnection();
    
    $mentions_statement = getQuacksThatMentionUser();
    
    // Dohvaca sve quackove u kojima se pojavljuje korisnikov username
    function getQuacksThatMentionUser() {
        global $database;
        try {
            $statement = $database->prepare(
                'SELECT username, date, quack 
                FROM dz2_quacks, dz2_users 
                WHERE dz2_users.id = dz2_quacks.id_user 
                AND BINARY quack LIKE :username
                ORDER BY date DESC;'
            );
    
            $statement->execute(
                array(
                    'username' => '%@' . $_SESSION['username'] . '%'
                )
            );
        } catch (PDOException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: my_quacks.php');
            exit();
        }

        return $statement;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentions</title>

    <link rel="stylesheet" href="quack.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Pacifico">
</head>
<body>
    <?php
        display_header_and_nav();
    ?>

    <h2>Quacks mentioning me:</h2>
    <?php
        displayQuacks($mentions_statement);
    ?>
</body>
</html>