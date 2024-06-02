<?php

// Configuración para mostrar todos los errores excepto los de nivel de advertencia
error_reporting(E_ALL & ~E_WARNING);

// Configuración para mostrar los errores en pantalla
ini_set('display_errors', 1);

// Establecer la zona horaria predeterminada
date_default_timezone_set('Europe/Madrid'); // Ajusta la zona horaria según tus necesidades

    
define("ENDEBUG", FALSE); // poner aquí false para mostrar los datos en xml y true para desarrollo;
include ("includes/config.php");
require("includes/LibEventos.php");
$RepoEventos = new libEventos();

if (ENDEBUG) {
    echo "<h1>DATOS RECIBIDOS</h1><br>";
    debug($RepoEventos->datos);
}

// 1. Filtramos los eventos que nos interesan mostrar.
$evFiltrados = FiltroDeEventosTimeLine($RepoEventos->datos, $config['favoritos']);
if (empty($evFiltrados)) {
    // Mostrar la página alternativa y detener la ejecución

    $descripcion1 = GenerarDescripcion($RepoEventos->datos, 1, 3, $determinardeportes);
    $descripcion2 = GenerarDescripcion($RepoEventos->datos, 2, 4, $determinardeportes);
    $descripcion3 = GenerarDescripcion($RepoEventos->datos, 3, 5, $determinardeportes);
    $descripcion4 = GenerarDescripcion($RepoEventos->datos, 4, 6, $determinardeportes);

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
    $date1 = new DateTime(); 
    // Obtener la zona horaria actual
    $timezone = $date1->getTimezone(); 
    // Obtener el offset en segundos
    $offsetInSeconds = $timezone->getOffset($date1);
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
    $channel2->addChild('icon')->addAttribute('src', 'http://192.168.86.213:12345/icons%2Fmultideporte.png');

    // Añadir el programa de hoy al XML
    $programme2 = $xml2->addChild('programme');
    $programme2->addAttribute('start', $fecha_hoy_formato . "000000 " . $offsetFormatted);
    $programme2->addAttribute('stop', $fecha_mañana_formato . "000000 " . $offsetFormatted);
    $programme2->addAttribute('channel', 'Guiadeportiva.tv');
    $programme2->addChild('title', "$titulo");
    $programme2->addChild('desc', $descripcion1);

    // Añadir el programa de mañana al XML
    $programme2 = $xml2->addChild('programme');
    $programme2->addAttribute('start', $fecha_mañana_formato . "000000 " . $offsetFormatted);
    $programme2->addAttribute('stop', $fecha_pasado_formato . "000000 " . $offsetFormatted);
    $programme2->addAttribute('channel', 'Guiadeportiva.tv');
    $programme2->addChild('title', "$titulo");
    $programme2->addChild('desc', $descripcion2);

    // Mostrar el XML en el navegador
    header('Content-Type: text/xml');
    echo $xml2->asXML();

    exit;
}

// 2. Fusionamos eventos con inicio similar
$evFusionados = FusionarEventos($evFiltrados);
if (ENDEBUG) {
    echo "<br><br><br><h1>DATOS FUSIONADOS</h1><br>";
    debug($evFusionados);
}

// 3. Se rellenan los huecos y ajustan horas
$epg = RellenarHuecos($evFusionados);
if (ENDEBUG) {
    echo "<br><br><br><h1>TIME LINE FINAL</h1><br>";
    debug($epg);
}

// 4. Mostrar el XML
if (!ENDEBUG) {


    header('Content-type: text/xml');
    echo GenerarXml($epg, $titulo, GenerarDescripcion($RepoEventos->datos, 1, 3, $determinardeportes), GenerarDescripcion($RepoEventos->datos, 2, 4, $determinardeportes), GenerarDescripcion($RepoEventos->datos, 3, 5, $determinardeportes),GenerarDescripcion($RepoEventos->datos, 4, 6, $determinardeportes))->asXML();
    exit();

}

function debug($datosEventos)
{// morta los datos
    foreach ($datosEventos as $evento) {
        echo $evento->evento . " - " . $evento->fechaInicio->format('Y-m-d H:i:s') . " - " . $evento->fechaFin->format('Y-m-d H:i:s') . "<br>";
    }
}


// archivo_principal.php

include 'config.php';

function FiltroDeEventosTimeLine($eventos, $filtros){
    $EventosDestacados = [];
    $ultimaHora = (((new DateTime())->modify('+2 day'))->setTime(23, 59, 0));

    foreach($eventos as $evento){
        if($evento->fechaInicio < $ultimaHora){
            $destacado = false;

            foreach ($filtros as $filtro) {
                // Verificamos si el evento cumple con al menos uno de los criterios del filtro actual
                if ($filtro($evento)) {
                    $destacado = true;
                    break;
                }
            }

            if ($destacado) {
                array_push($EventosDestacados, $evento);
            }
        }
    }

    return $EventosDestacados;
}




    function FusionarEventos($eventos){


        // Fusionar eventos con una hora de comienzo aproximada 30 minutos cambiar en la linea 63
        $countEventos = count($eventos);
        
        for($i=0; $i < count($eventos); $i++){

        // Ordenar eventos por hora de inicio
        usort($eventos, function($a, $b) {
            return $a->fechaInicio <=> $b->fechaInicio;
        });
        
            $eliminacionIndices = [];
            $eventosFusion = [];
            if(array_key_exists($i, $eventos)){    //hay un problema con el algoritmo esto evita problemas.
                $evento = $eventos[$i];
                
                for($b=$i+1; $b < count($eventos); $b++){
                    if(array_key_exists($b, $eventos)){ 
                        $lstEventos = $eventos[$b];
                        $intervalo = abs($evento->fechaInicio->getTimestamp() - $lstEventos->fechaInicio->getTimestamp()) / 60;
                        //Todos los eventos con una diferencia de 30 miinutos se fusionan.
                        if($intervalo < 30)
                        {   
                            array_push($eliminacionIndices, $b);
                            array_push($eventosFusion, $lstEventos);
                        }  
                    }
                }

                //Formato de como se pone aqui puedes ponerlo en la descripción o dejarlo en el titulo
                $evento->evento = $evento->fechaInicio->format("H:i") . ". " . $evento->evento . " en " . $evento->canal . " {separador fusion1}";

                //Fusión de eventos
                foreach($eventosFusion as $eventF){
                    if (!$eventF->evento==NULL) {
                        $evento->evento .=  "{separador eventosfusionados}" . $eventF->fechaInicio->format("H:i"). ". " . $eventF->evento . " en " . $eventF->canal;
                    }
                }

                $delCount=0;
                foreach($eliminacionIndices as $key){
                    unset($eventos[$key - $delCount]);
                }
            }
        }

         return $eventos;

    }

    
    function RellenarHuecos($eventos) {
      // Ordenamos los eventos por fecha de inicio
      usort($eventos, function($a, $b) {
        return $a->fechaInicio <=> $b->fechaInicio;
    });

    $nuevosEventos = [];
    
        foreach ($eventos as $key => $evento) {
            if ($key == count($eventos) - 1) {
                // Último evento, no hay siguiente evento
                $nuevosEventos[] = $evento;
            } else {
                $siguienteEvento = $eventos[$key + 1];
                if ($evento->fechaInicio > $evento->fechaFin) {
                    // Si la fecha de inicio es mayor que la fecha de fin, ajusta la fecha de fin
                    $evento->fechaFin = $evento->fechaInicio;
                } elseif ($evento->fechaFin > $siguienteEvento->fechaInicio) {
                    // Si la fecha de fin del evento actual es mayor que la fecha de inicio del siguiente evento
                    // Ajustamos la fecha de fin del evento actual
                    $evento->fechaFin = $siguienteEvento->fechaInicio;
                }
    
                // Agregar el evento ajustado a $nuevosEventos
                $nuevosEventos[] = $evento;
    
            }
        }
    

        // Eliminar eventos duplicados
        $nuevosEventos = array_unique($nuevosEventos, SORT_REGULAR);
    
        // Reordenar los eventos por fecha de inicio
        usort($nuevosEventos, function($a, $b) {
            return $a->fechaInicio <=> $b->fechaInicio;
        });
    
        $today = new DateTime('today');
        $primeraHora = (new DateTime($today->format('Y-m-d')))->setTime(0, 0, 0);
        $ultimaHora = (new DateTime($nuevosEventos[count($nuevosEventos) - 1]->fechaFin->format('Y-m-d')))->setTime(23, 59, 59);
    

        
        if ($nuevosEventos[0]->fechaInicio > $primeraHora) {
            $fechaInicio = ($nuevosEventos[0]->fechaInicio->format('Y-m-d') == $today->format('Y-m-d')) ? $nuevosEventos[0]->fechaInicio->modify('-1 second') : (clone $primeraHora)->modify('+1 day')->setTime(00, 00, 00);
            
        
            array_unshift($nuevosEventos, new Evento(
                "Evento Inicial",
                "Sin Liga",
                "Sin Deporte",
                "Sin Canal",
                $primeraHora,
                $fechaInicio
            ));
        }
        
            
        if ($nuevosEventos[count($nuevosEventos) - 1]->fechaFin < $ultimaHora) {
            array_push($nuevosEventos, new Evento(
                "Evento Final",
                "Sin Liga",
                "Sin Deporte",
                "Sin Canal",
                $evento->fechaFin->modify('+1 second'), // Fecha inicio un segundo después del último evento
                $ultimaHora
            ));
        }
    
        // Rellenamos los huecos entre eventos ajustando las horas
        for ($i = 0; $i < count($nuevosEventos) - 1; $i++) {
            $evento = $nuevosEventos[$i];
            $siguienteEvento = $nuevosEventos[$i + 1];
        
            // Verificamos si hay espacio entre el evento actual y el siguiente evento
            if ($evento->fechaFin < $siguienteEvento->fechaInicio) {


                // Si la fecha de inicio del siguiente evento cambia de día
                if ($evento->fechaFin->format('Ymd') != $siguienteEvento->fechaInicio->format('Ymd')) {
                    // Insertar evento "Sin Evento" hasta la medianoche del día del evento actual
                    
                    $ini = (clone $evento->fechaFin)->modify('+1 second'); // Fecha de inicio un segundo después del evento actual     
                    $fin = (new DateTime($evento->fechaFin->format('Y-m-d')))->modify('+1 second')->setTime(23, 59, 59); // Medianoche del día del evento actual


                        
                    array_splice($nuevosEventos, $i + 1, 0, [
                        new Evento(
                            "Sin Evento",
                            "Sin Liga",
                            "Sin Deporte",
                            "Sin Canal",
                            $ini,
                            $fin
                        )
                    ]);
               

                    // Insertar evento "Sin Evento" desde la medianoche del día siguiente hasta la fecha de inicio del siguiente evento
                    $ini = (new DateTime($siguienteEvento->fechaInicio->format('Y-m-d')))->setTime(0, 0, 0); // Medianoche del día siguiente
                    $fin = (clone $siguienteEvento->fechaInicio)->modify('-1 second'); // Fecha fin un segundo antes del inicio del siguiente evento
                    
                    if ($fin < $ini){$i += 1;}else{
                    array_splice($nuevosEventos, $i + 2, 0, [
                        new Evento(
                            "Sin Evento",
                            "Sin Liga",
                            "Sin Deporte",
                            "Sin Canal",
                            $ini,
                            $fin
                        )
                    ]);
                    $i += 2; // Saltar dos posiciones porque hemos insertado dos eventos nuevos
                }
                } else {
                    // Si la fecha de inicio del siguiente evento no cambia de día, insertamos un solo evento "Sin Evento" entre los eventos
                    $ini = (clone $evento->fechaFin)->modify('+1 second'); // Fecha de inicio un segundo después del evento actual
                    $fin = (clone $siguienteEvento->fechaInicio)->modify('-1 second'); // Fecha fin un segundo antes del inicio del siguiente evento
                    array_splice($nuevosEventos, $i + 1, 0, [
                        new Evento(
                            "Sin Evento",
                            "Sin Liga",
                            "Sin Deporte",
                            "Sin Canal",
                            $ini,
                            $fin
                        )
                    ]);
                    $i++; // Saltar una posición porque hemos insertado un evento nuevo
                }
            }

        }
        
    
        return $nuevosEventos;
    }
    

//Determinar Orden de los deportes
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

    
    
    function GenerarXml($eventos, $titulo, $descripcion1, $descripcion2, $descripcion3, $descripcion4) {
        // Crear el objeto SimpleXML
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><tv generator-info-name="Generado por Julio" generator-info-url="none"></tv>');
    
        // Añadir el canal al XML
        $channel = $xml->addChild('channel');
        $channel->addAttribute('lang', 'es');
        $channel->addAttribute('id', 'Guiadeportiva.tv');
        $channel->addChild('display-name', 'Guía deportiva');
        $channel->addChild('icon')->addAttribute('src', 'http://192.168.86.213:12345/icons%2Fmultideporte.png');
    
        // Crear los elementos XML a partir de los programas ordenados
        foreach ($eventos as $evento) {
            $programme = $xml->addChild('programme');
            $programme->addAttribute('start', $evento->fechaInicio->format('YmdHis O'));
            $programme->addAttribute('stop', $evento->fechaFin->format('YmdHis O'));
            $programme->addAttribute('channel', 'Guiadeportiva.tv');

            if ($evento->evento != "Sin Evento" && $evento->evento != "Evento Inicial" && $evento->evento != "Evento Final") {
                if (!preg_match('/^\d{2}:\d{2}/', $evento->evento)) {
                    $horaInicio = $evento->fechaInicio->format("H:i");
                    $title = str_replace(["{separador fusion1}", "{separador eventosfusionados}"], ["", " | "], $evento->evento);
                    $title = $horaInicio . '. ' . $title . " en " .  $evento->canal;
                } else {
                    $title = str_replace(["{separador fusion1}", "{separador eventosfusionados}"], ["", " | "], $evento->evento);
                }
            } else {
                $title = $titulo;
            }

            $programme->addChild('title', $title);
            // Seleccionar la función de descripción adecuada según la fecha del evento
            $descripcionFuncion = null;
            $hoy = new DateTime();
            $manana = new DateTime('tomorrow');
            $pasado = new DateTime('+2 day');
            $tresdias = new DateTime('+3 day');    
            $fechaEvento = $evento->fechaInicio->modify('+0 day');
            

            if ($fechaEvento->format("Ymd") == $hoy->format("Ymd")) {
                $descripcion = $descripcion1;
            } elseif ($fechaEvento->format("Ymd") == $manana->format("Ymd")) {
                $descripcion = $descripcion2;
            } elseif ($fechaEvento->format("Ymd") == $pasado->format("Ymd")) {
                $descripcion = $descripcion3;
            } else {
                $descripcion = $descripcion4;
            }
        
            $programme->addChild('desc', $descripcion);

        }
    
        return $xml;
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

