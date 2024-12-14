<?php
    require_once 'db.class.php';
    require_once 'common_functions.php';

    session_start();

    initial_commands();

    $database = DB::getConnection();

    if (isset($_POST['(un)follow-button'])) {
        if (empty($_POST['username-input'])) {
            $_SESSION['error_message'] = 'Username cannot be empty.';
        } else {
            if (strpos($_POST['username-input'], '@') === 0)
                $_POST['username-input'] = substr($_POST['username-input'], 1);

            if ($_POST['(un)follow-button'] === 'follow')
                handleFollow();
            else
                handleUnfollow();
        }
    }
    
    $quacks_user_follows_statement = getQuacksUserFollows();
    
    // Dohvaca sve quackove koje su objavili drugi korisnici koje korisnik prati
    function getQuacksUserFollows() {
        $id_user = getUserIdByUsername($_SESSION['username']);

        global $database;
        try {
            $statement = $database->prepare(
                'SELECT username, date, quack 
                FROM dz2_quacks, dz2_users
                WHERE dz2_users.id = dz2_quacks.id_user 
                AND dz2_quacks.id_user IN (
                SELECT DISTINCT id_followed_user 
                FROM dz2_follows 
                WHERE id_user = :id_user
                )
                ORDER BY date DESC;'
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

    // Obradjuje pokusaj followanja drugog korisnika
    function handleFollow() {
        global $database;
        $id_user = getUserIdByUsername($_SESSION['username']);
        $id_followed_user = getUserIdByUsername($_POST['username-input']);
        if ($id_user === $id_followed_user) {
            $_SESSION['error_message'] = 'Cannot follow yourself.';
        } else if (checkForFollow($id_user, $id_followed_user)) {
            $_SESSION['error_message'] = 'Already following user @' . $_POST['username-input'] . '.';
        } else {
            try {
                $statement = $database->prepare(
                    'INSERT INTO dz2_follows (id_user, id_followed_user) 
                    VALUES (:id_user, :id_followed_user);'
                );
    
                $statement->execute(
                    array(
                        'id_user' => $id_user,
                        'id_followed_user' => $id_followed_user
                    )
                );

                $_SESSION['executed_well'] = 'Started following @' . $_POST['username-input'] . '.';
            } catch (PDOException $e) {
                $_SESSION['error_message'] = $e->getMessage();
            }
        }
    }

    // Obradjuje pokusaj unfollowanja drugog korisnika
    function handleUnfollow() {
        global $database;
        $id_user = getUserIdByUsername($_SESSION['username']);
        $id_followed_user = getUserIdByUsername($_POST['username-input']);
        if ($id_user === $id_followed_user) {
            $_SESSION['error_message'] = 'Cannot unfollow yourself.';
        } else if (!checkForFollow($id_user, $id_followed_user)) {
            $_SESSION['error_message'] = 'You are not following user @' . $_POST['username-input'] . '.';
        } else {
            try {
                $statement = $database->prepare(
                    'DELETE FROM dz2_follows 
                    WHERE id_user = :id_user 
                    AND id_followed_user = :id_followed_user;'
                );
    
                $statement->execute(
                    array(
                        'id_user' => $id_user,
                        'id_followed_user' => $id_followed_user
                    )
                );

                $_SESSION['executed_well'] = 'No longer following @' . $_POST['username-input'] . '.';
            } catch (PDOException $e) {
                $_SESSION['error_message'] = $e->getMessage();
            }
        }
    }

    // Provjerava prati li korisnik s ID-jem $id_user korisnika s ID-jem $id_followed_user
    function checkForFollow($id_user, $id_followed_user) {
        global $database;
        try {
            $statement = $database->prepare(
                'SELECT id_followed_user 
                FROM dz2_follows 
                WHERE id_user = :id_user;'
            );

            $statement->execute(
                array(
                    'id_user' => $id_user
                )
            );
        } catch (PDOException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            return;
        }

        foreach ($statement->fetchAll() as $row)
            if ($id_followed_user === $row['id_followed_user'])
                return true;

        return false;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Following</title>

    <link rel="stylesheet" href="quack.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Pacifico">
</head>
<body>
    <?php
        display_header_and_nav();
    ?>

    <?php 
        if (isset($_SESSION['error_message'])) {
            echo '<p id=warning>' . $_SESSION['error_message'] . '</p>';
            unset($_SESSION['error_message']);
        }
        if (isset($_SESSION['executed_well'])) {
            echo '<p id=executed-well>' . $_SESSION['executed_well'] . '</p>';
            unset($_SESSION['executed_well']);
        }
    ?>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label for="username-input">Enter a username: </label>
        <input type="text" name="username-input" id="username-input">
        <button type="submit" value="follow" name="(un)follow-button">Follow</button>
        <button type="submit" value="unfollow" name="(un)follow-button">Unfollow</button>
    </form>

    <h2>Quacks I follow:</h2>
    <?php
        displayQuacks($quacks_user_follows_statement);
    ?>
</body>
</html>