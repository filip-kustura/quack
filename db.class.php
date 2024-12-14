<?php
    // U db_settings.php su definirani $host, $database_name, $database_username i $database_password
    require_once '../../../db_settings.php';

    class DB {
        // Interna staticka varijabla koja cuva konekciju na bazu
        private static $database = null;

        // Zabranimo new DB() i kloniranje
        final private function __construct() { }
        final private function __clone() { }

        // Statica funkcija za pristup bazi
        public static function getConnection() {
            // Spoji se samo ako vec nisi nekad ranije
            if (DB::$database === null) {
                // u globalnim varijablama su parametri za spajanje
                global $host, $database_name, $database_username, $database_password;
                try {
                    DB::$database = new PDO(
                        'mysql:host=' . $host . ';dbname=' . $database_name . ';charset=utf8',
                        $database_username, $database_password
                    );
    
                    DB::$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    DB::$database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                } catch (PDOException $e) {
                    echo 'ERROR: ' . $e->getMessage(); exit();
                }
            }

            return DB::$database;
        }
    }
?>