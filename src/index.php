<?php
// ---- PUNTO DE ENTRADA DE LA APLICACIÓN ----
session_start();

require_once __DIR__ . '/configuracion/config.php';
require_once __DIR__ . '/controladores/TiempoControlador.php';

$controlador = new TiempoControlador();
$error       = '';
$ciudad      = null;
$ciudades    = null;

if (isset($_GET['ciudad']) && isset($_SESSION['ciudad_id'])) {
    $ciudad = [
        'nombre' => $_SESSION['ciudad_nombre'],
        'pais'   => $_SESSION['ciudad_pais'],
        'lat'    => $_SESSION['ciudad_lat'],
        'lon'    => $_SESSION['ciudad_lon'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seleccion'])) {
    $datos    = json_decode($_POST['seleccion'], true);
    $ciudadId = $controlador->guardarCiudad($datos);

    $_SESSION['ciudad_id']     = $ciudadId;
    $_SESSION['ciudad_nombre'] = $datos['nombre'];
    $_SESSION['ciudad_pais']   = $datos['pais'];
    $_SESSION['ciudad_lat']    = $datos['lat'];
    $_SESSION['ciudad_lon']    = $datos['lon'];

    header('Location: index.php?ciudad=' . urlencode($datos['nombre']));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ciudad'])) {
    $nombreCiudad = trim($_POST['ciudad']);

    if (empty($nombreCiudad)) {
        $error = 'Por favor, introduce el nombre de una ciudad.';
    } else {
        $ciudades = $controlador->buscarCiudades($nombreCiudad);

        if (!$ciudades) {
            $error = 'Ciudad no encontrada. Por favor, intentalo de nuevo.';
        } elseif (count($ciudades) === 1) {
            $ciudadId = $controlador->guardarCiudad($ciudades[0]);

            $_SESSION['ciudad_id']     = $ciudadId;
            $_SESSION['ciudad_nombre'] = $ciudades[0]['nombre'];
            $_SESSION['ciudad_pais']   = $ciudades[0]['pais'];
            $_SESSION['ciudad_lat']    = $ciudades[0]['lat'];
            $_SESSION['ciudad_lon']    = $ciudades[0]['lon'];

            header('Location: index.php?ciudad=' . urlencode($ciudades[0]['nombre']));
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplicacion del Tiempo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: #f0e6d3;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: #c0392b;
            padding: 15px 40px;
        }

        header h1 {
            font-size: 20px;
            color: white;
            letter-spacing: 1px;
        }

        .contenedor {
            max-width: 500px;
            margin: 80px auto;
            padding: 0 20px;
            width: 100%;
        }

        .titulo {
            font-size: 28px;
            color: #e67e22;
            margin-bottom: 8px;
        }

        .subtitulo {
            font-size: 14px;
            color: #888;
            margin-bottom: 30px;
        }

        /* ---- FORMULARIO ---- */
        form {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        input[type="text"] {
            flex: 1;
            padding: 12px 15px;
            background-color: #222;
            border: 1px solid #333;
            border-radius: 3px;
            font-size: 15px;
            color: #f0e6d3;
        }

        input[type="text"]::placeholder { color: #555; }
        input[type="text"]:focus {
            outline: none;
            border-color: #e67e22;
        }

        button {
            padding: 12px 20px;
            background-color: #c0392b;
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover { background-color: #e74c3c; }

        .error {
            color: #e74c3c;
            font-size: 13px;
            margin-bottom: 15px;
        }

        /* ---- LISTA DE CIUDADES ---- */
        .lista-ciudades { margin-top: 20px; }

        .lista-ciudades p {
            font-size: 13px;
            color: #888;
            margin-bottom: 10px;
        }

        .ciudad-opcion {
            display: block;
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 6px;
            background-color: #222;
            border: 1px solid #333;
            border-radius: 3px;
            font-size: 14px;
            cursor: pointer;
            text-align: left;
            color: #f0e6d3;
        }

        .ciudad-opcion:hover {
            border-color: #e67e22;
            background-color: #2a2a2a;
        }

        .ciudad-opcion .pais {
            font-size: 12px;
            color: #666;
            margin-left: 8px;
        }

        /* ---- RESULTADO ---- */
        .resultado {
            margin-top: 25px;
            padding: 20px;
            background-color: #222;
            border-left: 3px solid #e67e22;
            border-radius: 3px;
        }

        .resultado h2 {
            font-size: 18px;
            color: #e67e22;
            margin-bottom: 15px;
        }

        .opciones {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .opciones a {
            display: block;
            padding: 12px 15px;
            background-color: #1a1a1a;
            color: #f0e6d3;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
            border: 1px solid #333;
        }

        .opciones a:hover {
            border-color: #e67e22;
            color: #e67e22;
        }

        .enlace-historial {
            display: inline-block;
            margin-top: 20px;
            color: #666;
            font-size: 13px;
            text-decoration: none;
        }

        .enlace-historial:hover { color: #e67e22; }
    </style>
</head>
<body>

<header>
    <h1>Aplicacion del Tiempo - Francisco Javier</h1>
</header>

<div class="contenedor">
    <p class="titulo">Consulta el tiempo</p>
    <p class="subtitulo">Introduce el nombre de una ciudad</p>

    <form method="POST" action="">
        <input
            type="text"
            name="ciudad"
            placeholder="Madrid, Londres, Paris..."
            value="<?php echo htmlspecialchars($_GET['ciudad'] ?? $_POST['ciudad'] ?? ''); ?>"
            autofocus
        >
        <button type="submit">Buscar</button>
    </form>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($ciudades && count($ciudades) > 1): ?>
        <div class="lista-ciudades">
            <p>Se han encontrado varias ciudades, selecciona una:</p>
            <form method="POST" action="">
                <?php foreach ($ciudades as $c): ?>
                    <button
                        type="submit"
                        name="seleccion"
                        value="<?php echo htmlspecialchars(json_encode($c)); ?>"
                        class="ciudad-opcion"
                    >
                        <strong><?php echo htmlspecialchars($c['nombre']); ?></strong>
                        <span class="pais">
                            <?php echo htmlspecialchars($c['pais']); ?>
                            <?php if ($c['estado']): ?>
                                — <?php echo htmlspecialchars($c['estado']); ?>
                            <?php endif; ?>
                        </span>
                    </button>
                <?php endforeach; ?>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($ciudad): ?>
        <div class="resultado">
            <h2>
                <?php echo htmlspecialchars($ciudad['nombre']); ?>,
                <?php echo htmlspecialchars($ciudad['pais']); ?>
            </h2>
            <div class="opciones">
                <a href="vistas/paginas.php?vista=actual">Tiempo actual</a>
                <a href="vistas/paginas.php?vista=horas">Prevision por horas</a>
                <a href="vistas/paginas.php?vista=semana">Prevision semanal</a>
            </div>
        </div>
    <?php endif; ?>

    <a class="enlace-historial" href="vistas/paginas.php?vista=historial">Ver historial de consultas</a>
</div>

</body>
</html>