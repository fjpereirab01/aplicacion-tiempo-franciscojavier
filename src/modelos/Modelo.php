<?php
// ---- MODELO - ACCESO A LA BASE DE DATOS ----
// Contiene todas las operaciones con la base de datos:
// ciudades y consultas meteorologicas

require_once __DIR__ . '/../configuracion/config.php';

class Modelo {

    private $conexion;

    // Constructor: establece la conexión con la base de datos
    public function __construct() {
        try {
            $this->conexion = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
                DB_USER,
                DB_PASS
            );
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Error al conectar con la base de datos: ' . $e->getMessage());
        }
    }

    // ---- CIUDADES ----

    // Guarda una ciudad si no existe y devuelve su id
    public function guardarCiudad($ciudad) {
        // Comprobamos si ya existe
        $sql  = 'SELECT id FROM ciudades WHERE nombre = :nombre AND pais = :pais';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':nombre' => $ciudad['nombre'],
            ':pais'   => $ciudad['pais']
        ]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado) return $resultado['id'];

        // Si no existe la insertamos
        $sql  = 'INSERT INTO ciudades (nombre, pais, lat, lon) VALUES (:nombre, :pais, :lat, :lon)';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':nombre' => $ciudad['nombre'],
            ':pais'   => $ciudad['pais'],
            ':lat'    => $ciudad['lat'],
            ':lon'    => $ciudad['lon']
        ]);
        return $this->conexion->lastInsertId();
    }

    // ---- CONSULTAS ----

    // Guarda una consulta meteorologica en la base de datos
    public function guardarConsulta($ciudadId, $tipo, $respuestaJson) {
        $sql  = 'INSERT INTO consultas (ciudad_id, tipo, respuesta_json)
                 VALUES (:ciudad_id, :tipo, :respuesta_json)';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':ciudad_id'      => $ciudadId,
            ':tipo'           => $tipo,
            ':respuesta_json' => $respuestaJson
        ]);
        return $this->conexion->lastInsertId();
    }

    // Obtiene todas las consultas realizadas ordenadas por fecha
    public function obtenerTodasConsultas() {
        $sql  = 'SELECT consultas.*, ciudades.nombre, ciudades.pais
                 FROM consultas
                 JOIN ciudades ON consultas.ciudad_id = ciudades.id
                 ORDER BY consultas.created_at DESC';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}