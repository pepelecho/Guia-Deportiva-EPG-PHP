<?php

require("includes/LibEventos.php");
$RepoEventos = new libEventos();

// Establecer la zona horaria predeterminada
date_default_timezone_set('Europe/Madrid'); // Ajusta la zona horaria según tus necesidades


// Definir datos de ejemplo
$datosEjemplo = $RepoEventos->datos;

// Generar XML

header('Content-Type: text/xml');
$xml = generarXML($datosEjemplo);
echo $xml->asXML();

// Función para obtener el nombre del día de la semana en español
function nombreDiaSemana($numero) {
    $dias = array(
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado'
    );

    return $dias[$numero];
}

// Función para obtener el nombre del mes en español
function nombreMes($numero) {
    $meses = array(
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    );

    return $meses[$numero];
}

// Función para generar XML con los eventos
function generarXML($eventos)
{
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data></data>');

    foreach ($eventos as $evento) {
        $item = $xml->addChild('item');
        $fecha = $evento->fechaInicio;
        $nombreDia = nombreDiaSemana($fecha->format('w')); // Obtener el nombre del día de la semana
        $nombreMes = nombreMes($fecha->format('n')); // Obtener el nombre del mes en español
        $fechaFormateada = $nombreDia . ' ' . $fecha->format('j') . ' de ' . $nombreMes . ' de ' . $fecha->format('Y'); // Agregar el nombre del día de la semana y del mes al formato de fecha
        $item->addChild('fecha', $fechaFormateada);
        $item->addChild('deporte', $evento->deporte);
        $item->addChild('liga', $evento->liga);
        $item->addChild('evento', $evento->evento);
        $item->addChild('canal', $evento->canal);
        $item->addChild('hora', $fecha->format('H:i') . 'h');
    }


    return $xml;
}
?>
