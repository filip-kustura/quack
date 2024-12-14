<?php
    // Vraca ID korisnika na temelju korisnickog imena
    function getUserIdByUsername($username) {
        global $database;
        try {
            $statement = $database->prepare(
                'SELECT id 
                FROM dz2_users 
                WHERE BINARY username = :username'
            );

            $statement->execute(
                array(
                    'username' => $username
                )
            );
        } catch (PDOException $e) {
            unset($_SESSION['username']);
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: login.php');
            exit();
        }

        $row = $statement->fetch();
        if ($row === false) {
            $_SESSION['error_message'] = 'User @' . $_POST['username-input'] . ' does not exist.';
            header('Location: following.php');
            exit();
        } else {
            return $row['id'];
        }
    }

    // Prikazuje quackove na temelju danog statementa
    function displayQuacks($statement) {
        foreach ($statement->fetchAll() as $row) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $row['date']);
            $formattedDate = $date->format('F d, Y \a\t H:i:s');

            $row['quack'] = preg_replace('/#(\w+)/', '<a href="https://rp2.studenti.math.hr/~kustufil/dz2/search.php?tag=$1">$0</a>', $row['quack']);
    
            echo '<p>';
            echo '<span>@' . $row['username'] . ' - ' . $formattedDate . '</span>';
            echo '<br>';
            echo $row['quack'];
            echo '</p>';
        }
    }

    function initial_commands() {
        if (isset($_POST['logout'])) {
            session_unset();
            session_destroy();
            header('Location: login.php');
            exit();
        }
    
        if (!isset($_SESSION['username'])) {
            header('Location: login.php');
            exit();
        }
    }

    function display_header_and_nav() {
        ?>
        <header>
            <div>
                Quack!
            </div>
            <div>
                <span>@<?php echo $_SESSION['username']; ?></span>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <input type="submit" value="logout" name="logout">
                </form>
            </div>
        </header>

        <nav>
            <img src="quack.jpg" alt="Duck">
            <a href="my_quacks.php">My quacks</a>
            <a href="following.php">Following</a>
            <a href="followers.php">Followers</a>
            <a href="mentions.php">quacks @<?php echo $_SESSION['username']; ?></a>
            <a href="search.php">#search</a>
        </nav>
        <?php
    }
?>