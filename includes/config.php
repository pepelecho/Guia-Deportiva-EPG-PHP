<?php


// 1. Añada el título que se mostrará en el EPG
$titulo = "Próximos eventos deportivos. 
Mantenga 'OK' para ver la guía.";



//2. DETERMINE EL ORDEN DE LOS DEPORTES QUE SE MOSTRARÁN PRIMERO EN LA DESCRIPCIÓN (GUÍA DEPORTIVA). TAMBIÉN DARÁ PRIORIDAD A LOS EVENTOS QUE SE VERÁN EN EL NUEVO CANAL QUE CREE (PODRÁ VER LOS EVENTOS EN EMISIÓN DESDE EL NUEVO CANAL CREADO SI AÑADE UN CANAL QUE CONTENGA LA DIRECCIÓN SUSERVIDOR/SIMPLEDOM/)

$determinardeportes = [
    "Fútbol español",
    "Fútbol internacional",
    "Tenis",
];



//3. ESTABLEZCA SUS EVENTOS FAVORITOS (SE AÑADIRÁN LOS EVENTOS DESTACADOS AL TIMELINE DE SU EPG SI UTILIZA /EPG.PHP COMO EPG. TAMBIÉN SE LE DARÁ PRIORIDAD A ESTOS EVENTOS PARA QUE LOS PUEDA VER DESDE EL NUEVO CANAL QUE CREE)
//(Compruebe https://www.movistarplus.es/deportesendirectobar para ver los nombres establecidos)

//Añada cuantos favoritos quiera

$config = [
    'favoritos' => [
        //Favorito 1
        'fav1' => function($evento) {
            //Fútbol, Equipos españoles (Puede añadir más equipos o cambiarlos)
            return (
               //Deportes: Fútbol español, Fútbol internacional
                (strcasecmp($evento->deporte, "Fútbol español") == 0 || strcasecmp($evento->deporte, "Fútbol internacional") == 0) &&
                //Ligas: Laliga o La Liga, Supercopa, Copa del Rey, Champions, UEFA.
                (stripos($evento->liga, "LaLiga") !== false || stripos($evento->liga, "La Liga") !== false || stripos($evento->liga, "Supercopa") !== false || stripos($evento->liga, "Copa del Rey") !== false || stripos($evento->liga, "Champions") !== false || stripos($evento->liga, "UEFA") !== false) &&
                //Equipos: Barcelona, Real Madrid, Atlético de Madrid, Betis
                (stripos($evento->evento, "Barcelona") !== false || stripos($evento->evento, "Real Madrid" ) !== false || stripos($evento->evento, "Atlético de Madrid" ) !== false || stripos($evento->evento, "At. Madrid" ) !== false  || stripos($evento->evento, "Betis" ) !== false)
            );
        },

        //Favorito 2
        'fav2' => function($evento) {
            //Tenis
            return (
                //Deporte: Tenis
                strcasecmp($evento->deporte, "Tenis") == 0 &&
                //Jugadores: Alcaraz, Nadal, Djockovic, Sinner...
                (stripos($evento->evento, "Alcaraz") !== false || stripos($evento->evento, "Nadal") !== false || stripos($evento->evento, "Djokovic") !== false  || stripos($evento->evento, "Sinner") !== false  || stripos($evento->evento, "Zverev") !== false || stripos($evento->evento, "Medvedev") !== false || stripos($evento->evento, "Ruud") !== false  || stripos($evento->evento, "Tsitsipas") !== false  || stripos($evento->evento, "Rublev") !== false || stripos($evento->evento, "De Miñaur") !== false  || stripos($evento->evento, "De Minaur") !== false)
            );
        },

        //Favorito 3
        'fav3' => function($evento) {
            //Fútbol, Equipos internacionales (Francia)
            return (
                //Deportes: Fútbol internacional
                strcasecmp($evento->deporte, "Fútbol internacional") == 0 &&
                //Ligas: Ligue 1, Champions, UEFA
                (stripos($evento->liga, "Ligue 1") !== false || stripos($evento->liga, "Champions") !== false || stripos($evento->liga, "UEFA") !== false) &&
                //Equipos: Olympique de Marsella, PSG
                (stripos($evento->evento, "Olympique de Marsella") !== false || stripos($evento->evento, "PSG") !== false)
            );
        },

        //Favorito 4
        'fav4' => function($evento) {
            //Fútbol, Equipos internacionales (UK)
            return (
                //Deportes: Fútbol internacional
                strcasecmp($evento->deporte, "Fútbol internacional") == 0 &&
                //Ligas: Premier League, Champions, UEFA
                (stripos($evento->liga, "Premier League") !== false || stripos($evento->liga, "Champions") !== false || stripos($evento->liga, "UEFA") !== false) &&
                //Equipos: Chelsea, Manchester (City o United)
                (stripos($evento->evento, "Chelsea") !== false || stripos($evento->evento, "Manchester") !== false)
            );
        },

        // Favorito 5
        'fav5' => function($evento) {
            // Fútbol, Selección española Absoluta Masculina
            return (
                // Deportes: Fútbol internacional
                strcasecmp($evento->deporte, "Fútbol internacional") == 0 &&
                // Ligas: excluyendo sub- y Femeninas
                stripos($evento->liga, "sub-") === false && stripos($evento->liga, "(F)") === false &&
                // Equipos: España, Selección española
                (stripos($evento->evento, "España") !== false || stripos($evento->evento, "Selección española") !== false)
            );
        },

        // Favorito 6
        'fav6' => function($evento) {
            // Fútbol, Mundial y Eurocopa Absoluta Masculina
            return (
                // Deportes: Fútbol internacional
                strcasecmp($evento->deporte, "Fútbol internacional") == 0 &&
                // Ligas: Mundial, Eurocopa
                (stripos($evento->liga, "Mundial") !== false || stripos($evento->liga, "Euro") !== false) &&
                // Ligas: excluyendo sub- y Femeninas
                stripos($evento->liga, "sub-") === false && stripos($evento->liga, "(F)") === false &&
                // Equipos: Brasil, Argentina, Inglaterra, Francia
                (stripos($evento->evento, "Brasil") !== false || stripos($evento->evento, "Argentina") !== false || stripos($evento->evento, "Inglaterra") !== false || stripos($evento->evento, "Francia") !== false || stripos($evento->evento, "Alemania") !== false)
            );
        },



        /*

        // Puedes añadir más filtros aquí según tus necesidades, si desea utilizarlos añádalos más arriba de / * y cambie el número después de "fav" al correspondiente
        //Aquí tienes algunos ejemplos:


        //Favorito 7 Fórmula 1
        'fav7' => function($evento) {
            //Fórmula 1
            return (
                //Deportes: Motor
                strcasecmp($evento->deporte, "Motor" == 0) &&
                //Competición: Mundial de Fórmula 1 
                stripos($evento->liga, "Mundial de Fórmula 1") !== false
            );
        },
        

         //Favorito 8 MotoGP
         'fav8' => function($evento) {
            //MotoGP
            return (
                //Deportes: Motociclismo
                strcasecmp($evento->deporte, "Motociclismo" == 0) &&
                //Competición: Mundial de MotoGP
                stripos($evento->liga, "Mundial de MotoGP") !== false
            );
        },


        // Favorito 9 Baloncesto Nacional
        'fav9' => function($evento) {
            return (
                // Deportes: Baloncesto
                strcasecmp($evento->deporte, "Baloncesto") == 0 &&
                // Ligas: Liga, Copa del Rey, Supercopa, UEFA
                (stripos($evento->liga, "Liga") !== false || stripos($evento->liga, "Copa del Rey") !== false || stripos($evento->liga, "Supercopa") !== false || stripos($evento->liga, "Euroliga") !== false) &&
                // Equipos: Barcelona, Real Madrid
                (stripos($evento->evento, "Barcelona") !== false || stripos($evento->evento, "Real Madrid") !== false)
                );
            },


        // Favorito 10 Baloncesto NBA
        'fav10' => function($evento) {
            return (
                // Deportes: Baloncesto
                strcasecmp($evento->deporte, "Baloncesto") == 0 &&
                // Liga: NBA (y todos los torneos que incluyan esa palabra)
                 stripos($evento->liga, "NBA") !== false &&
                // Equipos 
                (stripos($evento->evento, "Dallas Mavericks") !== false || stripos($evento->evento, "Los Angeles Lakers") !== false)
                );
            },



        //Favorito 11 Fútbol Femenino
        'fav11' => function($evento) {
            //Fútbol femenino, Equipos españoles (Puede añadir más equipos o cambiarlos)
            return (
                //Deportes: Fútbol español, Fútbol internacional
                (strcasecmp($evento->deporte, "Fútbol español") == 0 || strcasecmp($evento->deporte, "Fútbol internacional") == 0) &&
                //Ligas: Laliga o La Liga, Supercopa, Copa del Rey, Champions, UEFA.
                (stripos($evento->liga, "Liga F") !== false || stripos($evento->liga, "Supercopa Femenina") !== false || stripos($evento->liga, "Supercopa F") !== false  || stripos($evento->liga, "Copa de la Reina") !== false || stripos($evento->liga, "Liga de Campeones Femenina") !== false) &&
                //Equipos: Barcelona o Real Madrid
                (stripos($evento->evento, "Barcelona") !== false || stripos($evento->evento, "Real Madrid") !== false)
            );
        },



        //Favorito 12 Golf
        'fav12' => function($evento) {
            //Golf
            return (
                //Deportes:Golf
                strcasecmp($evento->deporte, "Golf") == 0  &&
                //Ligas: Masters, Open, PGA
                (stripos($evento->liga, "Masters") !== false || stripos($evento->liga, "Open") !== false || stripos($evento->liga, "PGA") !== false)
                );
            },

     */

    ],
];



//4. DATOS DE SU PROVEEDOR IPTV
$enlacealternativo = "http://SU_SERVIDOR/resources/vid.mp4"; //Enlace del vídeo cuando no hay eventos (su_url/resources/vid.mp4)
$url = "http://ELDOMINIODESUPROOVEDORIPTV:PUERTO"; //Dominio de su proovedor IPTV (incluya http://). Añada puerto si es necesario.
$username = "USUARIODESUPROOVEDORIPTV"; //Usuario de su proveedor IPTV (Si trabaja con listas m3u, la primera carpeta después del dominio)
$password = "CONTRASEÑADESUPROVEEDORIPTV"; //Contraseña de su proveedor IPTV (Si trabaja con listas m3u, la segunda carpeta después del dominio)
$formato = ""; //Añada .m3u8 o .ts si lo necesita o déjelo vacio si no.
$estructura = $url . "/" . $username . "/" . $password; //Modífiquelo si lo necesita



//5. URL DE LOS CANALES.
$listacanales = array(
    "LA 1" => $estructura . "/"."1".$formato, //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "LA 2" => $estructura . "/"."2".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "Antena 3" => $estructura . "/"."3".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "Cuatro" => $estructura . "/"."4".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "Telecinco" => $estructura . "/"."5".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "LaSexta" => $estructura . "/"."6".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "Movistar Plus+" => $estructura . "/"."7".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Vamos" => $estructura . "/"."8".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Ellas V" => $estructura . "/"."83".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "LALIGA TV BAR" => $estructura . "/"."132".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "LALIGA TV BAR 3" => $estructura . "/"."132".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "DAZN 1" => $estructura . "/"."205".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "DAZN 2" => $estructura . "/"."128".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "DAZN 3" => $estructura . "/"."129".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "DAZN 4" => $estructura . "/"."130".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "DAZN LaLiga 1" => $estructura . "/"."134".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "DAZN LaLiga 2" => $estructura . "/"."136".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "DAZN F1" => $estructura . "/"."126".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ LALIGA TV" => $estructura . "/"."138".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ LALIGA TV 2" => $estructura . "/"."89".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ LALIGA TV 3" => $estructura . "/"."219".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ LALIGA TV 4" => $estructura . "/"."144".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ LALIGA TV 5" => $estructura . "/"."146".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ LALIGA TV 6" => $estructura . "/"."185".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ LALIGA TV 7" => $estructura . "/"."187".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Copa del Rey" => $estructura . "/"."90".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones" => $estructura . "/"."159".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 2" => $estructura . "/"."161".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 3" => $estructura . "/"."163".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 4" => $estructura . "/"."165".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 5" => $estructura . "/"."167".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 6" => $estructura . "/"."169".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 7" => $estructura . "/"."171".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 8" => $estructura . "/"."173".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 9" => $estructura . "/"."175".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 10" => $estructura . "/"."177".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 11" => $estructura . "/"."179".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 12" => $estructura . "/"."181".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Liga de Campeones 13" => $estructura . "/"."183".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Deportes" => $estructura . "/"."329".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Deportes 2" => $estructura . "/"."189".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Deportes 3" => $estructura . "/"."97".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Deportes 4" => $estructura . "/"."111".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Deportes 5" => $estructura . "/"."112".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Deportes 6" => $estructura . "/"."113".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Deportes 7" => $estructura . "/"."114".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Golf" => $estructura . "/"."123".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "M+ Golf 2" => $estructura . "/"."3372".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "Eurosport 4K" => $estructura . "/"."120".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "Eurosport 1" => $estructura . "/"."120".$formato,  //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "Eurosport 2" => $estructura . "/"."121".$formato, //Los números indican el ID del canal, cambielo al que esté en su lista .m3u
    "Teledeporte" => $estructura . "/"."119".$formato
);


 
   
    
