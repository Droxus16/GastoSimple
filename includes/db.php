<?php
class DB {
    private static $host = "localhost";
    private static $dbname = "gastosimple";
    private static $username = "root";
    private static $password = "ccbfc13e-c31d-42ce-8939-3c7e63ed5417";
    private static $conexion = null;

    public static function conectar() {
        if (self::$conexion === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4";
                self::$conexion = new PDO($dsn, self::$username, self::$password);
                self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$conexion;
    }
}
?>
