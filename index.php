<?php

define("ENDEBUG", TRUE);
require("includes/LibEventos.php");
require ("includes/config.php");
$RepoEventos = new libEventos();

$evFiltrados = FiltroDeEventosParaCanal($RepoEventos->datos, $config['favoritos'], $determinardeportes);

// Función para mostrar los eventos en debug
function debug($datosEventos) {
    foreach ($datosEventos as $evento) {
        echo $evento->deporte . " | " . $evento->liga . " | " . $evento->evento . " | " . $evento->canal . " | " . $evento->fechaInicio->format('d/m/Y H:i') . " | " . $evento->fechaFin->format('d/m/Y H:i') . "<br/>";
    }
}

if (ENDEBUG) {
    echo "<h1>EVENTOS ACTIVOS</h1><br>"; // Modificación: Cambiado el título
}

$eventosActivos = [];
$eventosDestacados = [];
$eventosCategorias = [];
$otrosEventos = [];

if (!empty($evFiltrados)) {
    
    $fecha_actual = new DateTime();
    $evento_activo = null;

    foreach ($evFiltrados as $evento) {
        // Modifica la fecha del evento a quince minutos antes
        $fecha_inicio_modificada = date_sub($evento->fechaInicio, date_interval_create_from_date_string('15 minutes'));
        // Modifica la fecha del evento a quince minutos después
        $fecha_fin_modificada = date_add($evento->fechaFin, date_interval_create_from_date_string('15 minutes'));
        if ($fecha_inicio_modificada <= $fecha_actual && $fecha_actual <= $fecha_fin_modificada) {
            $evento_activo = $evento;
            $eventosActivos[] = $evento_activo; // Agregar evento activo a la lista

            foreach ($listacanales as $nombre_canal => $url_canal) {
                if (strpos($evento->canal, ',') !== false) {
                    $nombres_canal = explode(',', $evento->canal);
                    $nombre_encontrado = false;
                    foreach ($nombres_canal as $nombre) {
                        $nombre = trim($nombre);
                        if (array_key_exists($nombre, $listacanales)) {
                            $nuevo_nombre_canal = $nombre;
                            $nombre_encontrado = true;
                            break;
                        }
                    }
                    if (!$nombre_encontrado) {
                        $nuevo_nombre_canal = trim($nombres_canal[0]);
                    }
                } else {
                    $nuevo_nombre_canal = $evento->canal;
                }

                if ($nombre_canal === $nuevo_nombre_canal) {

                    if (ENDEBUG) {
                        echo  "<b>" . $evento_activo->fechaInicio->format('H:i') . " - " . $evento_activo->fechaFin->format('H:i') . "</b><br/>" . "Deporte: " . $evento_activo->deporte . "<br/>" . "Liga: " . $evento_activo->liga . "<br/>" . "Evento: " . $evento_activo->evento . "<br/> Canal: " . $nuevo_nombre_canal .  "<br/><br/>";
                    } else {
                        // Redirigir a la URL del canal
                        header("Location: $url_canal");
                        exit; // Terminar la ejecución del script después de la redirección
                    }
                }
            }
        }
    }

    // Verificar si no se encontraron eventos activos
    if (empty($eventosActivos)) {
        $eventosActivos = null; // Si no hay eventos activos, asignar null
    }

    if (!ENDEBUG) {
        // Si no se encuentra un evento con canal disponible, redirigir a enlace alternativo
        if (empty($eventosActivos) && empty($eventosDestacados) && empty($eventosCategorias) && empty($otrosEventos)) {
            header("Location: $enlacealternativo");
            exit; // Terminar la ejecución después de la redirección
        } else {
            echo "No hay eventos activos";
        }
    }
}

// Modificado
if (ENDEBUG) {
    echo "<h1>EVENTOS DESTACADOS</h1>"; // Modificación: Agregado título
    $eventosDestacados = array_filter($evFiltrados, function ($evento) use ($config) {
        foreach ($config['favoritos'] as $favorito) {
            if ($favorito($evento)) {
                return true; // Si el evento cumple con algún favorito, se considera destacado
            }
        }
        return false; // Si no coincide con ningún favorito, no es destacado
    });

    debug($eventosDestacados); // Modificación: Filtrar y mostrar solo eventos destacados por canal favorito
    if (empty($eventosDestacados)) {
        $eventosDestacados = null; // Si no hay eventos destacados, asignar null
    }

    if (isset($determinardeportes) && !empty($determinardeportes)) { // Verificar si $determinardeportes está definida y no está vacía
        echo "<h1>EVENTOS CATEGORÍAS</h1>"; // Modificación: Agregado título
        foreach ($determinardeportes as $categoria) { // Iterar sobre las categorías
            echo "<h2>$categoria</h2>"; // Mostrar el nombre de la categoría
            $eventosCategoria = array_filter($evFiltrados, function ($evento) use ($categoria) {
                return $evento->deporte === $categoria; // Filtrar eventos por categoría
            });
            debug($eventosCategoria); // Mostrar eventos de la categoría actual
            if (empty($eventosCategoria)) {
                $eventosCategorias = null; // Si no hay eventos por categoría, asignar null
            }
        }
    } else {
        echo "<h1>EVENTOS CATEGORÍAS</h1>"; // Modificación: Agregado título
        echo "No se han especificado categorías."; // Mensaje de advertencia si $determinardeportes está vacía o no definida
    }

    echo "<h1>OTROS EVENTOS</h1><br>"; // Modificación: Agregado título
    $otrosEventos = array_filter($evFiltrados, function ($evento) use ($config, $determinardeportes) {
        return isset($determinardeportes) && !in_array($evento->deporte, $determinardeportes) && !in_array($evento->canal, $config['favoritos']); // Filtrar y mostrar solo otros eventos que no están en favoritos
    });
    debug($otrosEventos); // Modificación: Filtrar y mostrar solo otros eventos
    if (empty($otrosEventos)) {
        $otrosEventos = null; // Si no hay otros eventos, asignar null
    }
}

if (!ENDEBUG) {
    // Si todas las variables relevantes están vacías, redirigir a enlace alternativo
    if (empty($eventosActivos)) {
        header("Location: $enlacealternativo");
        exit; // Terminar la ejecución después de la redirección
    }
}

function FiltroDeEventosParaCanal($eventos, $filtros, $determinardeportes){
    $EventosDestacados = [];
    $otrosEventosCategorias = [];
    $otrosEventos = [];
    $ultimaHora = (((new DateTime())->modify('+1 day'))->setTime(00, 00, 0));

    // Clasificar eventos según su deporte
    $eventosPorDeporte = [];
    foreach ($determinardeportes as $deporte) {
        $eventosPorDeporte[$deporte] = [];
    }

    // Clasificar eventos en sus respectivas categorías de deportes
    foreach($eventos as $evento){
        if($evento->fechaInicio < $ultimaHora){
            $destacado = false;

            foreach ($filtros as $filtro) {
                // Verificar si el evento cumple con al menos uno de los criterios del filtro actual
                if ($filtro($evento)) {
                    $destacado = true;
                    break;
                }
            }

            if ($destacado) {
                array_push($EventosDestacados, $evento);
            } else if (in_array($evento->deporte, $determinardeportes)) {
                array_push($eventosPorDeporte[$evento->deporte], $evento);
            } else {
                array_push($otrosEventos, $evento);
            }
        }
    }

    // Ordenar los eventos destacados según el orden de los filtros
    // Definimos un orden específico basado en la prioridad de fav1, fav2, fav3, etc.
    usort($EventosDestacados, function($eventoA, $eventoB) use ($filtros) {
        // Determina la prioridad de cada evento basado en el orden de los filtros
        $prioridadA = -1;
        $prioridadB = -1;

        foreach ($filtros as $index => $filtro) {
            if ($filtro($eventoA)) {
                $prioridadA = $index;
            }
            if ($filtro($eventoB)) {
                $prioridadB = $index;
            }
        }

        // Si ambos eventos tienen la misma prioridad, se dejan en el mismo orden
        if ($prioridadA === $prioridadB) {
            return 0;
        }

        // De lo contrario, los ordenamos por prioridad
        return ($prioridadA < $prioridadB) ? -1 : 1;
    });

    // Construir el array de eventos en el orden especificado
    foreach ($determinardeportes as $deporte) {
        if (!empty($eventosPorDeporte[$deporte])) {
            $otrosEventosCategorias = array_merge($otrosEventosCategorias, $eventosPorDeporte[$deporte]);
        }
    }

    // Primero los eventos destacados, luego los de categorías de deporte y finalmente los otros eventos
    return array_merge($EventosDestacados, $otrosEventosCategorias, $otrosEventos);
}

