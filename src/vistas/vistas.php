<?php
// ---- VISTAS - TODAS LAS PÁGINAS DE LA APLICACIÓN ----

function mostrarCabecera($titulo) { ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - Aplicacion del Tiempo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: #f0e6d3;
            min-height: 100vh;
        }

        header {
            background-color: #c0392b;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 20px;
            color: white;
            letter-spacing: 1px;
        }

        header a {
            color: white;
            text-decoration: none;
            font-size: 13px;
            opacity: 0.8;
        }

        header a:hover { opacity: 1; }

        .contenedor {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .titulo-pagina {
            font-size: 22px;
            color: #e67e22;
            margin-bottom: 5px;
        }

        .subtitulo {
            font-size: 13px;
            color: #888;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #c0392b;
            color: white;
            padding: 10px 15px;
            font-size: 13px;
            text-align: left;
            font-weight: normal;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #2a2a2a;
            font-size: 14px;
            color: #f0e6d3;
        }

        tr:hover td { background-color: #222; }

        .temp-max { color: #e67e22; font-weight: bold; }
        .temp-min { color: #e74c3c; font-weight: bold; }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-actual  { background-color: #27ae60; color: white; }
        .badge-horas   { background-color: #e67e22; color: white; }
        .badge-semana  { background-color: #8e44ad; color: white; }

        .volver {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 20px;
            background-color: #c0392b;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
        }

        .volver:hover { background-color: #e74c3c; }

        .sin-datos {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 14px;
        }

        .contador {
            font-size: 13px;
            color: #888;
            margin-bottom: 10px;
            text-align: right;
        }

        /* ---- GRÁFICA ---- */
        .grafica {
            margin-top: 35px;
            padding: 20px;
            background-color: #222;
            border-radius: 5px;
        }

        .grafica h2 {
            font-size: 15px;
            color: #e67e22;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
<header>
    <h1>Aplicacion del Tiempo - Francisco Javier</h1>
    <a href="/index.php">Volver a la busqueda</a>
</header>
<?php } ?>

<?php
function mostrarPiePagina() { ?>
</body>
</html>
<?php } ?>

<?php
// ---- VISTA TIEMPO ACTUAL ----
function vistaActual($ciudad, $datos) {
    mostrarCabecera('Tiempo actual');
?>
<div class="contenedor">
    <p class="titulo-pagina"><?php echo $ciudad['nombre']; ?>, <?php echo $ciudad['pais']; ?></p>
    <p class="subtitulo">Tiempo actual</p>

    <table>
        <tbody>
            <tr>
                <th>Estado</th>
                <td><?php echo $datos['descripcion']; ?></td>
            </tr>
            <tr>
                <th>Temperatura</th>
                <td><?php echo $datos['temperatura']; ?>°C</td>
            </tr>
            <tr>
                <th>Sensacion termica</th>
                <td><?php echo $datos['sensacion']; ?>°C</td>
            </tr>
            <tr>
                <th>Temperatura minima</th>
                <td class="temp-min"><?php echo $datos['temp_min']; ?>°C</td>
            </tr>
            <tr>
                <th>Temperatura maxima</th>
                <td class="temp-max"><?php echo $datos['temp_max']; ?>°C</td>
            </tr>
            <tr>
                <th>Humedad</th>
                <td><?php echo $datos['humedad']; ?>%</td>
            </tr>
            <tr>
                <th>Viento</th>
                <td><?php echo $datos['viento']; ?> km/h</td>
            </tr>
            <tr>
                <th>Nubosidad</th>
                <td><?php echo $datos['nubes']; ?>%</td>
            </tr>
            <tr>
                <th>Visibilidad</th>
                <td><?php echo $datos['visibilidad']; ?> km</td>
            </tr>
        </tbody>
    </table>

    <a class="volver" href="/index.php">Volver</a>
</div>
<?php mostrarPiePagina(); } ?>

<?php
// ---- VISTA PREVISIÓN POR HORAS ----
function vistaHoras($ciudad, $horas) {
    mostrarCabecera('Prevision por horas');
?>
<div class="contenedor">
    <p class="titulo-pagina"><?php echo $ciudad['nombre']; ?>, <?php echo $ciudad['pais']; ?></p>
    <p class="subtitulo">Prevision meteorologica por horas</p>

    <table>
        <thead>
            <tr>
                <th>Hora</th>
                <th>Estado</th>
                <th>Temperatura</th>
                <th>Sensacion</th>
                <th>Humedad</th>
                <th>Viento</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($horas as $hora): ?>
            <tr>
                <td><?php echo $hora['hora']; ?></td>
                <td><?php echo $hora['descripcion']; ?></td>
                <td><?php echo $hora['temperatura']; ?>°C</td>
                <td><?php echo $hora['sensacion']; ?>°C</td>
                <td><?php echo $hora['humedad']; ?>%</td>
                <td><?php echo $hora['viento']; ?> km/h</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a class="volver" href="/index.php">Volver</a>
</div>
<?php mostrarPiePagina(); } ?>

<?php
// ---- VISTA PREVISIÓN SEMANAL ----
function vistaSemana($ciudad, $dias) {
    mostrarCabecera('Prevision semanal');

    // Preparamos los datos para la gráfica
    $etiquetas = array_column($dias, 'dia');
    $tempsMax  = array_column($dias, 'temp_max');
    $tempsMin  = array_column($dias, 'temp_min');
?>
<div class="contenedor">
    <p class="titulo-pagina"><?php echo $ciudad['nombre']; ?>, <?php echo $ciudad['pais']; ?></p>
    <p class="subtitulo">Prevision meteorologica semanal</p>

    <table>
        <thead>
            <tr>
                <th>Dia</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Max</th>
                <th>Min</th>
                <th>Humedad</th>
                <th>Viento</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dias as $dia): ?>
            <tr>
                <td><?php echo $dia['dia']; ?></td>
                <td><?php echo $dia['fecha']; ?></td>
                <td><?php echo $dia['descripcion']; ?></td>
                <td class="temp-max"><?php echo $dia['temp_max']; ?>°C</td>
                <td class="temp-min"><?php echo $dia['temp_min']; ?>°C</td>
                <td><?php echo $dia['humedad']; ?>%</td>
                <td><?php echo $dia['viento']; ?> km/h</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Gráfica de temperaturas semanales -->
    <div class="grafica">
        <h2>Temperaturas de la semana</h2>
        <canvas id="graficaSemana"></canvas>
    </div>

    <a class="volver" href="/index.php">Volver</a>
</div>

<script>
    const ctx = document.getElementById('graficaSemana').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($etiquetas); ?>,
            datasets: [
                {
                    label: 'Maxima (°C)',
                    data: <?php echo json_encode($tempsMax); ?>,
                    borderColor: '#e67e22',
                    backgroundColor: 'rgba(230, 126, 34, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#e67e22'
                },
                {
                    label: 'Minima (°C)',
                    data: <?php echo json_encode($tempsMin); ?>,
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#e74c3c'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { color: '#f0e6d3' }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#f0e6d3' },
                    grid: { color: '#2a2a2a' }
                },
                y: {
                    ticks: { color: '#f0e6d3' },
                    grid: { color: '#2a2a2a' },
                    title: {
                        display: true,
                        text: 'Temperatura (°C)',
                        color: '#888'
                    }
                }
            }
        }
    });
</script>

<?php mostrarPiePagina(); } ?>

<?php
// ---- VISTA HISTORIAL ----
function vistaHistorial($consultas) {
    mostrarCabecera('Historial');

    $tiposTraduccion = [
        'actual' => 'Tiempo actual',
        'horas'  => 'Prevision por horas',
        'semana' => 'Prevision semanal',
    ];
?>
<div class="contenedor">
    <p class="titulo-pagina">Historial de consultas</p>
    <p class="subtitulo">Todas las consultas meteorologicas realizadas</p>

    <?php if (empty($consultas)): ?>
        <div class="sin-datos">No hay consultas registradas todavia.</div>
    <?php else: ?>
        <p class="contador">Total: <strong><?php echo count($consultas); ?></strong> consultas</p>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ciudad</th>
                    <th>Pais</th>
                    <th>Tipo</th>
                    <th>Fecha y hora</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consultas as $i => $consulta): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo htmlspecialchars($consulta['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($consulta['pais']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $consulta['tipo']; ?>">
                            <?php echo $tiposTraduccion[$consulta['tipo']]; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i:s', strtotime($consulta['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a class="volver" href="/index.php">Volver</a>
</div>
<?php mostrarPiePagina(); } ?>