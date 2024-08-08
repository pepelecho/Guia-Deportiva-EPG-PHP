<?php
    
include ("includes/config.php");
require("includes/LibEventos.php");
$RepoEventos = new libEventos();

    // Establecer la zona horaria predeterminada
    date_default_timezone_set('Europe/Madrid'); // Ajusta la zona horaria según tus necesidades

    // Mostrar la página alternativa y detener la ejecución

    $descripcion1 = GenerarDescripcion($RepoEventos->datos, 1, 3, $determinardeportes);
    $descripcion2 = GenerarDescripcion($RepoEventos->datos, 2, 4, $determinardeportes);
    $descripcion3 = GenerarDescripcion($RepoEventos->datos, 3, 5, $determinardeportes);
    

    // Obtener la fecha actual
    $fecha_hoy_formato = date("Ymd");

    // Obtener la fecha de mañana
    $fecha_mañana_formato = date("Ymd", strtotime("+1 day"));

    // Obtener la fecha de pasado mañana
    $fecha_pasado_formato = date("Ymd", strtotime("+2 day"));

    // Obtener la fecha de pasado pasado mañana
    $fecha_pasado_pasado_formato = date("Ymd", strtotime("+3 day"));

    /*Zona horaria */
    // Obtener la fecha y hora actual
    $date = new DateTime(); 
    // Obtener la zona horaria actual
    $timezone = $date->getTimezone(); 
    // Obtener el offset en segundos
    $offsetInSeconds = $timezone->getOffset($date);
    // Convertir el offset a horas y minutos
    $offsetHours = $offsetInSeconds / 3600;
    $offsetMinutes = ($offsetInSeconds % 3600) / 60;
    // Formatear el offset
    $offsetFormatted = sprintf("%+03d%02d", $offsetHours, abs($offsetMinutes));



    // Crear el objeto SimpleXML
    $xml2 = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><tv generator-info-name="Generado por Julio" generator-info-url="none"></tv>');

    // Añadir el canal al XML
    $channel2 = $xml2->addChild('channel');
    $channel2->addAttribute('lang', 'es');
    $channel2->addAttribute('id', 'Guiadeportiva.tv');
    $channel2->addChild('display-name', 'Guía deportiva');
    $channel2->addChild('icon')->addAttribute('src', 'icons/multideporte.png');

    // Añadir el programa de hoy al XML
    $programme2 = $xml2->addChild('programme');
    $programme2->addAttribute('start', $fecha_hoy_formato . "000000 " . $offsetFormatted);
    $programme2->addAttribute('stop', $fecha_mañana_formato . "000000 " . $offsetFormatted);
    $programme2->addAttribute('channel', 'Guiadeportiva.tv');
    $programme2->addChild('title', $titulo);
    $programme2->addChild('desc', htmlspecialchars($descripcion1));

    // Añadir el programa de mañana al XML
    $programme2 = $xml2->addChild('programme');
    $programme2->addAttribute('start', $fecha_mañana_formato . "000000 " . $offsetFormatted);
    $programme2->addAttribute('stop', $fecha_pasado_formato . "000000 " . $offsetFormatted);
    $programme2->addAttribute('channel', 'Guiadeportiva.tv');
    $programme2->addChild('title', $titulo);
    $programme2->addChild('desc', htmlspecialchars($descripcion2));

    // Añadir el programa de pasado mañana al XML
    $programme2 = $xml2->addChild('programme');
    $programme2->addAttribute('start', $fecha_pasado_formato . "000000 " . $offsetFormatted);
    $programme2->addAttribute('stop', $fecha_pasado_pasado_formato . "000000 " . $offsetFormatted);
    $programme2->addAttribute('channel', 'Guiadeportiva.tv');
    $programme2->addChild('title', $titulo);
    $programme2->addChild('desc', htmlspecialchars($descripcion3));

    // Mostrar el XML en el navegador
    header('Content-Type: text/xml');
    echo $xml2->asXML();

    exit;


function ordenarDeportes($deporte, $determinardeportes) {
    // Obtener el índice del deporte en el orden definido
    $indice = array_search($deporte, $determinardeportes);

    // Si el deporte no está en el orden definido, asignar un índice mayor para que aparezca después
    return ($indice === false) ? PHP_INT_MAX : $indice;
}

    
function GenerarDescripcion($eventos, $inicioDia, $finDia, $determinardeportes) {
    $texto = "";
    $grupos = [];
    $diasMostrados = 0; // Variable para contar los días mostrados

    // Organizar eventos por fecha, deportes y liga
    foreach ($eventos as $evento) {
        $fecha = $evento->fechaInicio->format('Ymd');
        $deporte = $evento->deporte;
        $liga = $evento->liga;
        $hora = $evento->fechaInicio->format('Hi');
        $grupos[$fecha][$deporte][$liga][$hora][] = $evento; // Modificado para agrupar por hora
    }

    // Ajustar $inicioDia si es 0
    if ($inicioDia == 0) {
        $inicioDia = 0;
    }

    // Filtrar eventos dentro del rango de días especificado
    $grupos = array_slice($grupos, $inicioDia - 1, $finDia - $inicioDia + 1, true);

    // Ordenar el array multidimensional por las claves (fechas)
    ksort($grupos);

    foreach ($grupos as $fecha => $deportes) {
         // Ordenar deportes
         uksort($deportes, function ($a, $b) use ($determinardeportes) {
            return ordenarDeportes($a, $determinardeportes) - ordenarDeportes($b, $determinardeportes);
        });

        // Convertir la fecha a formato legible
        $date = new DateTime($fecha);
        $textoFecha = "";

        // Determinar si es hoy, mañana o fecha normal
        $hoy = new DateTime('today');
        $manana = new DateTime('tomorrow');

        if ($date->format('Y-m-d') === $hoy->format('Y-m-d')) {
            $textoFecha = "📅 Hoy, " . lcfirst(nombreDiaSemana($date->format('w'))) . " " . $date->format('j') . " de " . nombreMes($date->format('n')) . " de " . $date->format('Y');
        } elseif ($date->format('Y-m-d') === $manana->format('Y-m-d')) {
            $textoFecha = "📅 Mañana, " . lcfirst(nombreDiaSemana($date->format('w'))) . " " . $date->format('j') . " de " . nombreMes($date->format('n')) . " de " . $date->format('Y');
        } else {
            $textoFecha = "📅 " . nombreDiaSemana($date->format('w')) . " " . $date->format('j') . " de " . nombreMes($date->format('n')) . " de " . $date->format('Y');
        }

        $texto .= ($diasMostrados > 0) ? "----------------------------------------------------------------------------------\n\n\n" : "";
        $texto .= $textoFecha . "\n\n\n";

        foreach ($deportes as $deporte => $ligas) {
            $texto .= "#$deporte\n\n";

            foreach ($ligas as $liga => $horas) {
                $texto .= "□ $liga\n";

                // Ordenar las horas de los eventos
                 ksort($horas);

                foreach ($horas as $hora => $eventos) { // Iterar sobre cada hora
                    foreach ($eventos as $evento) {
                        // Verificar si el separador está presente en el evento
                        if (strpos($evento->evento, '{separador fusion1}') !== false) {
                            // Dividir la cadena en dos partes
                            $partes = explode('{separador fusion1}', $evento->evento, 2);
                
                            // Extraer la parte antes del separador
                            $titulo = trim($partes[0]);
                
                            // Añadir al texto
                            $texto .= "• " .  $titulo . "\n";
                        } else {
                            // Si no hay separador, usar el evento completo
                            $texto .= "• " . $evento->fechaInicio->format("H:i") . ". " . $evento->evento . " en " . $evento->canal . "\n";
                        }
                    }
                }

                $texto .= "\n"; // Salto de línea entre los eventos de cada liga
            }
        }

        $diasMostrados++; // Incrementamos el contador de días mostrados
    }

    return $texto;
}
    
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
    
