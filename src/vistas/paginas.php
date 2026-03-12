<?php
// ---- PUNTO DE ENTRADA DE LAS VISTAS ----
session_start();

require_once __DIR__ . '/../configuracion/config.php';
require_once __DIR__ . '/../controladores/TiempoControlador.php';
require_once __DIR__ . '/../vistas/vistas.php';

// Comprobamos que hay una ciudad en sesión
if (!isset($_SESSION['ciudad_id'])) {
    header('Location: ../index.php');
    exit;
}

$ciudad = [
    'nombre' => $_SESSION['ciudad_nombre'],
    'pais'   => $_SESSION['ciudad_pais'],
    'lat'    => $_SESSION['ciudad_lat'],
    'lon'    => $_SESSION['ciudad_lon'],
];

$controlador = new TiempoControlador();

// Según el parámetro 'vista' de la URL mostramos una página u otra
$vista = $_GET['vista'] ?? 'actual';

switch ($vista) {
    case 'actual':
        $datos = $controlador->obtenerTiempoActual(
            $_SESSION['ciudad_id'],
            $ciudad['lat'],
            $ciudad['lon']
        );
        vistaActual($ciudad, $datos);
        break;

    case 'horas':
        $datos = $controlador->obtenerPrevisionHoras(
            $_SESSION['ciudad_id'],
            $ciudad['lat'],
            $ciudad['lon']
        );
        vistaHoras($ciudad, $datos);
        break;

    case 'semana':
        $datos = $controlador->obtenerPrevisionSemanal(
            $_SESSION['ciudad_id'],
            $ciudad['lat'],
            $ciudad['lon']
        );
        vistaSemana($ciudad, $datos);
        break;

    case 'historial':
        $datos = $controlador->obtenerHistorial();
        vistaHistorial($datos);
        break;

    default:
        header('Location: ../index.php');
        exit;
}