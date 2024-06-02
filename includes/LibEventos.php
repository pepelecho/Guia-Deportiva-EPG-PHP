<?php

class evento {
    public $evento;
    public $liga;
    public $deporte;
    public $canal;
    public $fechaInicio;
    public $fechaFin;

    public function __construct($evento, $liga, $deporte, $canal, $fechaini, $fechafin){
        $this->evento = $evento;
        $this->liga = $liga;
        $this->deporte = $deporte;
        $this->canal = $canal;
        $this->fechaInicio = $fechaini;
        $this->fechaFin = $fechafin;
        return $this;
    }
}

class libEventos {
    public $datos = [];

    public function __construct(){
        $this->obtenerEventosMovistar();
    }

    private function procesarResultado($resultado) {
        // Patrón para buscar la primera ocurrencia de ":"
        $primerPatron = '/\{nombre\}(.*?):(.*?){\/nombre}/';
        
        // Patrón para buscar la segunda ocurrencia de ":"
        $segundoPatron = '/\{nombre\}(.*?):(.*?):(.*?){\/nombre}/';
        
        // Comprobamos si hay más de una ocurrencia de ":"
        if (preg_match($segundoPatron, $resultado, $matches)) {
            $liga = '{liga}' . $matches[1] . ':' . $matches[2] . '{/liga}';
            $evento = '{evento}' . $matches[3] . '{/evento}';
        } elseif (preg_match($primerPatron, $resultado, $matches)) {
            $liga = '{liga}' . $matches[1] . '{/liga}';
            $evento = '{evento}' . $matches[2] . '{/evento}';
        } else {
            // Si no hay ":" en absoluto
            $liga = '{liga}Sin determinar{/liga}';
            $evento = '{evento}' . $resultado . '{/evento}';
        }
        
        // En el último caso, si hay un nombre dentro de un evento, lo eliminamos
        if (strpos($evento, '{nombre}') !== false) {
            $evento = str_replace(array('{nombre}', '{/nombre}'), '', $evento);
        }
        
        return $liga . $evento;
    }

    private function recogerdato($dato){
    }

    private function limpiahtml($codigo) {
        $buscar = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
        $reemplazar = array('>', '<', '\\1');
        $codigo = preg_replace($buscar, $reemplazar, $codigo);
        $codigo = str_replace("> <", "><", $codigo);
        return $codigo;
    }

    private function LimpiarFormato($texto) {
        // Comprobar si el primer caracter es un espacio
        if (substr($texto, 0, 1) === ' ') {
            // Eliminar el primer caracter (el espacio)
            $texto = substr($texto, 1);
        }
        
        // Comprobar si el último caracter es un espacio
        if (substr($texto, -1) === ' ') {
            // Eliminar el último caracter (el espacio)
            $texto = substr($texto, 0, -1);
        }
    
        // Comprobar si el último caracter es un punto
        if (substr($texto, -1) === '.') {
            // Eliminar el último caracter (el punto)
             $texto = substr($texto, 0, -1);
        }
        
        // Devolver el texto modificado
        return $texto;
    }

    private function obtenerNumeroMes($nombreMes) {
        $meses = [
            'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
            'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
            'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12
        ];
    
        return $meses[strtolower($nombreMes)];
    }
    
    private function obtenerFechaHora($fechaHoraString) {
        // Establecer el locale en español
        setlocale(LC_TIME, 'es_ES.UTF-8');

        // Remover el 'h' al final (si está presente)
        $fechaHoraString = rtrim($fechaHoraString, 'h');

        // Obtener el día de la semana, día del mes, mes y año
        $datosFecha = sscanf($fechaHoraString, "%[^ ] %d de %[^ ] de %d %d:%d");

        // Verificar si se obtuvieron todos los datos
        if (count($datosFecha) !== 6) {
            return "Formato de fecha y hora incorrecto.";
        }

        // Crear el objeto DateTime
        $fechaHora = sprintf('%d-%02d-%02d %02d:%02d:00', $datosFecha[3], $this->obtenerNumeroMes($datosFecha[2]), $datosFecha[1], $datosFecha[4], $datosFecha[5]);
        return DateTime::createFromFormat('Y-m-d H:i:s', $fechaHora);
    }

    private function obtenerEventosMovistar(){
        $url = file_get_contents('https://www.movistarplus.es/deportesendirectobar');  // Obtener el contenido de la URL y eliminar etiquetas script
        $text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $url);
        $text = str_replace('<div class="brick', '**ESTO ES UN SEPARADOR**<div class="', $text);  // Marcar el inicio de los divs donde se encuentran los horarios
        $text = str_replace('Retransmisiones deportivas en directo', '', $text);
        $text = str_replace('Sujetas a posibles cambios de hora y de canal.', '', $text);
        $text = $this->limpiahtml($text);  // Limpiar saltos en blanco y tabuladores
        $text = str_replace('<span class="box-medium">', '{deporte}', $text);// Reemplazar etiquetas span y enlaces
        $text = str_replace('</span><ul></ul></li><li>', '</ul></li><li>', $text);
        $text = str_replace('</span><ul><li>', '{/deporte}', $text);
        $text = str_replace('</span></li></ul>', '{/hora}{salto}', $text);
        $text = str_replace('<a href="', '{sep332}{nombre}<a href="', $text); 
        $text = str_replace('</a><ul><li class="time-bar">', '{/nombre}{canal}', $text);
        $text = str_replace(') <span>', '){/canal}{hora}', $text);
        $text = preg_replace('/{hora}[0-9.a-z]{2}.[0-9.a-z]{2}h<\/span><\/li><li class="time-bar">/', 'o en ', $text);
        $text = preg_replace('/\(Dial [0-9]*\)/', '', $text);
        $text = str_replace(' {/canal}o en', ',', $text);
        $text = str_replace(' {/canal}', '{/canal}', $text);
        $text = preg_replace('/(\d{2})\.(\d{2})h/', '$1:$2h {/hora}{sep432}', $text);
        $text = strip_tags($text);  // Eliminar todas las etiquetas HTML restantes
        $pieces = explode("**ESTO ES UN SEPARADOR**", $text); // Separar cada entrada
        
       
        foreach ($pieces as $key => $line) {  // Iterar sobre cada línea y aplicar la función procesarResultado
            $pieces[$key] = preg_replace_callback('/\{nombre\}(.*?)\{\/nombre\}/', function($matches) {
                return $this->procesarResultado($matches[0]);
            }, $line);

            $pieces[$key] = preg_replace('/\s+\{evento\}/', '{evento}', $pieces[$key]); // Eliminar espacios delante del nombre en los eventos
        }
        $textonuevo = "{fecha}" . implode('{/hora}{salto}{salto}{salto}{fecha}', array_map('trim', $pieces)) . "{/hora}{salto}{salto}{salto}";
        $textonuevo = str_replace('{salto}', '<br/>', $textonuevo);

        // Expresión regular para encontrar la fecha dentro de los distintivos
        $patron = "/(\{fecha\})(.*?)(\{deporte\})/";

        // Reemplazar el texto antes de {deporte} manteniendo la fecha original
        $textonuevo = preg_replace($patron, "$1$2" . "{/fecha}" . "$3", $textonuevo);
        $lineas = explode("<br/>", $textonuevo);
        $fecha_actual = '';
        $deporte_actual = '';
        $liga_actual = '';

        foreach ($lineas as $linea) {
            if (preg_match('/{fecha}(.+?){\/fecha}/', $linea, $matches_fecha)) {
                $fecha_actual = $matches_fecha[1];
            }

            if (preg_match('/{deporte}(.+?){\/deporte}/', $linea, $matches_deporte)) {
                $deportenormal = $matches_deporte[1];
                // Buscar etiqueta deportes
                if (preg_match('/{deporte}(.+?)(?:{deporte}(.+?))?{\/deporte}/', $linea, $deporteraro)) {
                    $deporte_actual = isset($deporteraro[2]) ? $deporteraro[2] : $deportenormal;
                } else {
                    $deporte_actual = $deportenormal;
                }
                
            }
            
            if (preg_match('/{sep332}(.+?){sep432}/', $linea, $matches_evento)) {
                $matches_hora = "";
                $matches_liga = "";
                $matches_evento = "";
                $matches_canal = "";
            
                if (preg_match('/{hora}(.+?){\/hora}/', $linea, $matches_hora) &&
                    preg_match('/{evento}(.+?){\/evento}/', $linea, $matches_evento) &&
                    preg_match('/{liga}(.+?){\/liga}/', $linea, $matches_liga) &&
                    preg_match('/{canal}(.+?){\/canal}/', $linea, $matches_canal)) {
            
                    // Limpiar formato de los resultados
                    $evento_limpio = $this->LimpiarFormato($matches_evento[1]);
                    $liga_limpia = $this->LimpiarFormato($matches_liga[1]);
                    $canal_limpio = $this->LimpiarFormato($matches_canal[1]);
                    
                    if (strpos($canal_limpio, 'LALIGA TV BAR') === false && strpos($canal_limpio, 'Vamos BAR2') === false) {
                            $canal_limpio = preg_replace('/\b BAR\b/', '', $canal_limpio);
                        }
            
                    $fechaInicio = $this->obtenerFechaHora($fecha_actual . " " . $matches_hora[1]);
                    $fechaFin = $this->obtenerFechaHora($fecha_actual . " " . $matches_hora[1]);
                    //Añadir 2 horas y media de intervalo
                    $fechaFin->add(new DateInterval('PT2H30M'));
            
                    $this->datos[] = new evento($evento_limpio, $liga_limpia, $deporte_actual, $canal_limpio, $fechaInicio, $fechaFin);
                }
            }
        }
    }
}

?>
