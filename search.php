<?php
    require_once 'db.class.php';
    require_once 'common_functions.php';

    session_start();

    initial_commands();

    $database = DB::getConnection();

    if (isset($_GET['tag']))
        $_POST['tag'] = $_GET['tag'];
    
    if (isset($_POST['tag'])) {
        if (empty($_POST['tag'])) {
            $_SESSION['error_message'] = 'Search bar cannot be left empty.';
        } else {
            if (strpos($_POST['tag'], '#') === 0)
                $_POST['tag'] = substr($_POST['tag'], 1);

            $searched_tag_statement = getQuacksWithCertainTag();
        }
    }
    
    // Dohvaca sve quackove u kojima se pojavljuje odredjeni tag
    function getQuacksWithCertainTag() {
        global $database;
        try {
            $statement = $database->prepare(
                'SELECT username, date, quack 
                FROM dz2_quacks, dz2_users 
                WHERE dz2_users.id = dz2_quacks.id_user 
                AND quack LIKE :tag
                ORDER BY date DESC;'
            );
    
            $statement->execute(
                array(
                    'tag' => '%#' . $_POST['tag'] . '%'
                )
            );

            $_SESSION['executed_well'] = 'Quacks which contain tag #' . $_POST['tag'] . ':';
        } catch (PDOException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: search.php');
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
    <title>Search</title>
    
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
    ?>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="text" name="tag" id="tag-input" placeholder="Search Quack">
        <input type="submit" value="Search" name="search-button">
    </form>

    <?php
        if (isset($_SESSION['executed_well'])) {
            echo '<h2>' . $_SESSION['executed_well'] . '</h2>';
            unset($_SESSION['executed_well']);
        }

        if (isset($searched_tag_statement))
            displayQuacks($searched_tag_statement);
    ?>
</body>
</html>