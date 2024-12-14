<?php
    require_once 'db.class.php';
    require_once 'common_functions.php';
    
    session_start();
    
    initial_commands();

    $database = DB::getConnection();


    $user_followers_statement = getUserFollowers();
    
    // Dohvaca korisnikove pratitelje
    function getUserFollowers() {
        $id_user = getUserIdByUsername($_SESSION['username']);

        global $database;
        try {
            $statement = $database->prepare(
                'SELECT username
                FROM dz2_follows, dz2_users
                WHERE dz2_follows.id_followed_user = :id_user 
                AND dz2_follows.id_user = dz2_users.id;'
            );
    
            $statement->execute(
                array(
                    'id_user' => $id_user
                )
            );
        } catch (PDOException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: my_quacks.php');
            exit();
        }

        return $statement;
    }

    function displayFollowers($statement) {
        echo '<ul>';
        foreach ($statement->fetchAll() as $row) {
            echo '<li>';
            echo '@' . $row['username'];
            echo '</li>';
        }
        echo '</ul>';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Followers</title>

    <link rel="stylesheet" href="quack.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Pacifico">
</head>
<body>
    <?php
        display_header_and_nav();
    ?>

    <h2>My followers:</h2>
    <?php
        displayFollowers($user_followers_statement);
    ?>
</body>
</html>