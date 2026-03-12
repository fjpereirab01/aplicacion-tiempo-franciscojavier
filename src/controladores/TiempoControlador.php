<?php
// ---- CONTROLADOR - LÓGICA DE NEGOCIO Y LLAMADAS A LA API ----
// Se encarga de obtener los datos de la API y prepararlos para las vistas

require_once __DIR__ . '/../configuracion/config.php';
require_once __DIR__ . '/../modelos/Modelo.php';

class TiempoControlador {

    private $modelo;

    public function __construct() {
        $this->modelo = new Modelo();
    }

    // ---- GEOCODIFICACIÓN ----

    // Busca ciudades por nombre y devuelve hasta 5 resultados
    public function buscarCiudades($nombreCiudad) {
        $url = API_BASE_URL . '/geo/1.0/direct?q=' . urlencode($nombreCiudad) . '&limit=5&appid=' . API_KEY;

        $respuesta = $this->llamarApi($url);
        if (!$respuesta) return null;

        $datos = json_decode($respuesta, true);
        if (empty($datos)) return null;

        // Tabla de traducción de códigos de país al español
        $paises = [
            'ES' => 'España',   'FR' => 'Francia',
            'IT' => 'Italia',   'DE' => 'Alemania',
            'GB' => 'Reino Unido', 'US' => 'Estados Unidos',
            'PT' => 'Portugal', 'MX' => 'Mexico',
            'AR' => 'Argentina', 'CO' => 'Colombia',
            'CL' => 'Chile',    'PE' => 'Peru',
            'VE' => 'Venezuela', 'BR' => 'Brasil',
            'CN' => 'China',    'JP' => 'Japon',
            'RU' => 'Rusia',    'IN' => 'India',
            'AU' => 'Australia', 'CA' => 'Canada',
        ];

        $ciudades = [];
        foreach ($datos as $item) {
            $nombre     = $item['local_names']['es'] ?? $item['name'];
            $codigoPais = $item['country'];
            $ciudades[] = [
                'nombre'      => $nombre,
                'pais'        => $paises[$codigoPais] ?? $codigoPais,
                'codigo_pais' => $codigoPais,
                'estado'      => $item['state'] ?? '',
                'lat'         => $item['lat'],
                'lon'         => $item['lon'],
            ];
        }
        return $ciudades;
    }

    // Guarda la ciudad en la base de datos y devuelve su id
    public function guardarCiudad($ciudad) {
        return $this->modelo->guardarCiudad($ciudad);
    }

    // ---- TIEMPO ACTUAL ----

    public function obtenerTiempoActual($ciudadId, $lat, $lon) {
        $url       = API_BASE_URL . '/data/2.5/weather?lat=' . $lat . '&lon=' . $lon . '&appid=' . API_KEY . '&units=' . API_UNITS . '&lang=' . API_LANG;
        $respuesta = $this->llamarApi($url);
        $datos     = json_decode($respuesta, true);

        // Guardamos la consulta en la base de datos
        $this->modelo->guardarConsulta($ciudadId, 'actual', $respuesta);

        return [
            'temperatura' => round($datos['main']['temp']),
            'sensacion'   => round($datos['main']['feels_like']),
            'temp_min'    => round($datos['main']['temp_min']),
            'temp_max'    => round($datos['main']['temp_max']),
            'humedad'     => $datos['main']['humidity'],
            'descripcion' => ucfirst($datos['weather'][0]['description']),
            'icono'       => 'https://openweathermap.org/img/wn/' . $datos['weather'][0]['icon'] . '@2x.png',
            'viento'      => round($datos['wind']['speed'] * 3.6),
            'nubes'       => $datos['clouds']['all'],
            'visibilidad' => isset($datos['visibility']) ? round($datos['visibility'] / 1000, 1) : 'N/D',
        ];
    }

    // ---- PREVISIÓN POR HORAS ----

    public function obtenerPrevisionHoras($ciudadId, $lat, $lon) {
        $url       = API_BASE_URL . '/data/2.5/forecast?lat=' . $lat . '&lon=' . $lon . '&appid=' . API_KEY . '&units=' . API_UNITS . '&lang=' . API_LANG . '&cnt=8';
        $respuesta = $this->llamarApi($url);
        $datos     = json_decode($respuesta, true);

        // Guardamos la consulta en la base de datos
        $this->modelo->guardarConsulta($ciudadId, 'horas', $respuesta);

        $horas = [];
        foreach ($datos['list'] as $prevision) {
            $horas[] = [
                'hora'        => date('H:i', $prevision['dt']),
                'temperatura' => round($prevision['main']['temp']),
                'sensacion'   => round($prevision['main']['feels_like']),
                'descripcion' => ucfirst($prevision['weather'][0]['description']),
                'icono'       => $prevision['weather'][0]['icon'],
                'viento'      => round($prevision['wind']['speed'] * 3.6),
                'humedad'     => $prevision['main']['humidity'],
            ];
        }
        return $horas;
    }

    // ---- PREVISIÓN SEMANAL ----

    public function obtenerPrevisionSemanal($ciudadId, $lat, $lon) {
        $url       = API_BASE_URL . '/data/2.5/forecast?lat=' . $lat . '&lon=' . $lon . '&appid=' . API_KEY . '&units=' . API_UNITS . '&lang=' . API_LANG;
        $respuesta = $this->llamarApi($url);
        $datos     = json_decode($respuesta, true);

        // Guardamos la consulta en la base de datos
        $this->modelo->guardarConsulta($ciudadId, 'semana', $respuesta);

        // Agrupamos por día
        $diasAgrupados = [];
        foreach ($datos['list'] as $prevision) {
            $fecha = date('Y-m-d', $prevision['dt']);
            $diasAgrupados[$fecha][] = $prevision;
        }

        // Traducción de días de la semana
        $traduccion = [
            'Monday' => 'Lunes', 'Tuesday'  => 'Martes',
            'Wednesday' => 'Miercoles', 'Thursday' => 'Jueves',
            'Friday' => 'Viernes', 'Saturday' => 'Sabado',
            'Sunday' => 'Domingo',
        ];

        $dias = [];
        foreach ($diasAgrupados as $fecha => $previsiones) {
            $temps     = array_column(array_column($previsiones, 'main'), 'temp');
            $humedades = array_column(array_column($previsiones, 'main'), 'humidity');
            $vientos   = array_column(array_column($previsiones, 'wind'), 'speed');
            $medio     = intval(count($previsiones) / 2);
            $nombreDia = date('l', strtotime($fecha));

            $dias[] = [
                'fecha'       => date('d/m/Y', strtotime($fecha)),
                'dia'         => $traduccion[$nombreDia] ?? $nombreDia,
                'temp_max'    => round(max($temps)),
                'temp_min'    => round(min($temps)),
                'humedad'     => round(array_sum($humedades) / count($humedades)),
                'viento'      => round((array_sum($vientos) / count($vientos)) * 3.6),
                'descripcion' => ucfirst($previsiones[$medio]['weather'][0]['description']),
                'icono'       => $previsiones[$medio]['weather'][0]['icon'],
            ];
        }
        return $dias;
    }

    // ---- HISTORIAL ----

    public function obtenerHistorial() {
        return $this->modelo->obtenerTodasConsultas();
    }

    // ---- MÉTODO PRIVADO PARA LLAMAR A LA API ----

    private function llamarApi($url) {
        $contexto  = stream_context_create(['http' => ['timeout' => 10]]);
        $respuesta = file_get_contents($url, false, $contexto);
        return $respuesta === false ? null : $respuesta;
    }
}