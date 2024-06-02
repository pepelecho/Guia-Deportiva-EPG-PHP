# Guia-Deportiva-EPG-PHP
Esta agenda deportiva permite consultar eventos deportivos emitidos en España, indicando su canal y hora de emisión. Ofrece resultados válidos para EPG, listados XML y un canal deportivo, ideal para listas IPTV. 

## Sobre el proyecto

Los datos se extraen de la guía deportiva de Movistar Plus.

Con este script, podrá:

1. Ver la programación de eventos deportivos hasta 6 días en su reproductor IPTV.
2. Ordenar los eventos según su interés.
3. Destacar eventos en el timeline de su EPG (opcional).
4. Disponer de un canal que redirige a eventos en emisión (requiere suscripción IPTV propia y no se proporciona ningún servicio ni dato sobre ello). Puede priorizar eventos destacados o elegir un simple vídeo.
5. Obtener elementos deportivos en XML para su implementación en aplicaciones web.

Este código es compatible con la mayoría de reproductores IPTV (puede requerir decodificación por hardware) y ha sido probado mayoritariamente en Tivimate.

### Desarrollado en

Compatible con PHP 4 o superior.

<a href="https://www.php.net/" target="_blank"><img alt="File:PHP-logo.svg" src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/27/PHP-logo.svg/711px-PHP-logo.svg.png?20180502235434" decoding="async" width="54" height="30" srcset="https://upload.wikimedia.org/wikipedia/commons/thumb/2/27/PHP-logo.svg/1067px-PHP-logo.svg.png?20180502235434 1.5x, https://upload.wikimedia.org/wikipedia/commons/thumb/2/27/PHP-logo.svg/1422px-PHP-logo.svg.png?20180502235434 2x" data-file-width="54" data-file-height="30"></a><br/>
<a href="https://www.w3.org/XML/" target="_blank"><img alt="File:Xml logo.svg" src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/Xml_logo.svg/241px-Xml_logo.svg.png?20080508104026" decoding="async" width="54" height="14" srcset="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/Xml_logo.svg/362px-Xml_logo.svg.png?20080508104026 1.5x, https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/Xml_logo.svg/482px-Xml_logo.svg.png?20080508104026 2x" data-file-width="54" data-file-height="14"></a>

<!-- GETTING STARTED -->
## Instalación

Iniciar este código es extremadamente sencillo. Solo necesita un servidor web capaz de ejecutar PHP y reproducir vídeos desde el navegador, además de un reproductor IPTV compatible con programación EPG (para más de una fuente si ya posee una para otros canales).

1. Copie y pegue los archivos en la carpeta deseada de su servidor.
2. Cree un canal en su lista IPTV habitual o una nueva lista con un único canal (asegúrese de que el dispositivo pueda acceder al servidor).

### Crear un canal en su lista M3U o Xtream

Puede usar un editor de listas IPTV (como iptveditor.com) o un simple bloc de notas (solo válido para listas M3U). Añada el tvg-ID "Guiadeportiva.tv" y el enlace http://SU_SERVIDOR/SU_CARPETA/. También puede agregar el logo ubicado en la carpeta /resources/ con el nombre "Guía deportiva".

*Nota: Este enlace explota todas las funciones (ver los eventos en vivo desde ese canal). Si desea solo ver un mismo vídeo durante las 24h, use el enlace http://SU_SERVIDOR/SU_CARPETA/simple_index.php (no recomendado).*

Ejemplo de código para editar su lista M3U desde un bloc de notas (guárdelo como .m3u):

```plaintext
#EXTINF:0 tvg-name="Guía deportiva" tvg-ID="Guiadeportiva.tv" tvg-logo="http://SU_SERVIDOR/resources/guia_deportiva.png" group-title="EL GRUPO QUE DESEE DE SU LISTA DE CANALES",Guía deportiva
http://SU_SERVIDOR/SU_CARPETA/
```

Alternativamente, puede añadir una nueva lista local con un único canal si su reproductor IPTV permite varias listas (guárdelo como .m3u):

```plaintext
#EXTM3U
#EXTINF:0 tvg-name="Guía deportiva" tvg-ID="Guiadeportiva.tv" tvg-logo="http://SU_SERVIDOR/resources/guia_deportiva.png" group-title="EL GRUPO QUE DESEE DE SU LISTA DE CANALES",Guía deportiva
http://SU_SERVIDOR/SU_CARPETA/
```

3. Crear el nuevo EPG en su reproductor IPTV

Añada una nueva fuente EPG desde su reproductor IPTV. Elija entre una de estas dos opciones:

1. **Explotar todas las funciones:** Además de ver todos los eventos en la descripción del EPG del canal, podrá destacar eventos por criterios. Estos eventos destacados aparecerán en el timeline para que pueda añadir recordatorios o verlos con más facilidad (Recomendado):
   http://SU_SERVIDOR/SU_CARPETA/epg.php

2. **EPG simple:** Añada este enlace para un EPG simple durante los tres días siguientes (Mayor facilidad a la hora de configurar):
   http://SU_SERVIDOR/SU_CARPETA/simple_epg.php

Se recomienda configuración de actualización de EPG de 2 a 4 horas (salvo algunas excepciones, vea Notas adicionales).

Una vez completados estos pasos, el código debería funcionar por defecto. Configúrelo a sus necesidades en /includes/config.php.

### /includes/config.php

Abra /includes/config.php y configure el código según sus criterios:

1. Añada el título que se mostrará en el timeline mientras no haya eventos, o durante todo el día si ha elegido simple_epg.php:
```php
$titulo = "TEXRO A MOSTRAR EN EL TIMELINE";
```

2. Determine el orden de los deportes que se priorizarán en la descripción EPG. Solo añada los que más le interesen. El nombre de los deportes debe coincidir con exactitud con los de https://www.movistarplus.es/deportesendirectobar. Ejemplo:

```php
$determinardeportes = [
    "Fútbol español",
    "Fútbol internacional",
    "Tenis",
    "Baloncesto"
];
```

3. Eventos destacados. Decida qué eventos son destacados para usted (se les dará máxima prioridad y se mostrarán en el timeline del EPG para que pueda añadir recordatorios y verlos más fácilmente). Agregue tantos favoritos como desee siguiendo este ejemplo. Hay algunos ejemplos adicionales en el código para facilitar su modificación según sus preferencias. Ejemplo:

```php
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
```

4. Datos de su proveedor IPTV  y Enlace al vídeo a mostrar cuando no haya eventos. Los datos de su proveedor IPTV se consultan descargando la lista m3u y abriéndola con el bloc de notas. Busque cualquier canal, debajo, aparecerá un enlace similar a esto: "http://ELDOMINIODESUPROVEEDORIPTV:PUERTO/USUARIODESUPROVEEDORIPTV/CONTRASEÑADESUPROVEEDORIPTV/CANAL.ts". Deberá añadir los datos separados por / a las variables pertinentes. 


```php
$enlacealternativo = "http://SERVIDOR_QUE_EJECUTA_EL_SRCIPT_PHP/resources/vid.mp4"; //Enlace del vídeo cuando no hay eventos
$url = "http://ELDOMINIODESUPROOVEDORIPTV:PUERTO"; //Dominio de su proveedor IPTV
$username = "USUARIODESUPROOVEDORIPTV"; //Usuario de su proveedor IPTV
$password = "CONTRASEÑADESUPROVEEDORIPTV"; //Contraseña de su proveedor IPTV
$formato = ""; //Añada .m3u8 o .ts si lo necesita o déjelo vacío
$estructura = $url . "/" . $username . "/" . $password; //Modificala según necesites
```

Alternativamente, puede añadir directamente toda la dirección a la variable $estructura:

```php
$estructura = "http://ELDOMINIODESUPROVEEDORIPTV:PUERTO/USUARIODESUPROVEEDORIPTV/CONTRASEÑADESUPROVEEDORIPTV/";
```


5. Añade los canales y su identificación correspondiente. Abre tu lista m3u con un bloc de notas como hicimos anteriormente y busca cada nombre de canal. Por ejemplo, supongamos que buscamos "LA 1" y obtuvimos este resultado debajo del nombre:

```
http://ELDOMINIODESUPROOVEDORIPTV:PUERTO/USUARIODESUPROOVEDORIPTV/CONTRASEÑADESUPROVEEDORIPTV/3212
```

Modificaremos el número (ID del canal), por defecto el 1:


```
    "LA 1" => $estructura . "/"."1".$formato, //Los números indican el ID del canal, cambielo al que esté en su lista .m3u

por 

    "LA 1" => $estructura . "/"."3212".$formato, // Los números indican el ID del canal, cámbielo al que esté en su lista .m3u
```

Haz lo mismo por cada canal y añade los que desees.

IMPORTANTE: No modifiques los nombres de los canales, ya que es la forma en que aparecen en la página de Movistar. Modificarlos ocasionaría que no se encuentren coincidencias de esos canales en los scripts PHP.

Después de realizar estos pasos, el código estará listo para funcionar.

### Notas adicionales:

- Los eventos tienen una duración de 2.5 horas en el EPG y 3 horas en el canal (-15 minutos antes y + 15 minutos después).
- El canal prioriza eventos destacados y luego otros eventos, priorizando primero el orden establecido en la variable $determinardeportes.
- Los eventos de madrugada suelen desaparecer de la página fuente a las 00:00 del mismo día. Se recomienda aumentar el tiempo de actualización del EPG a 8 horas o más y añadir recordatorios antes de que se eliminen.

### Agradecimientos:

Gracias por su paciencia y disfruten del código :)

---

[php.net]: https://www.php.net/
[w3.org/XML/]: https://www.w3.org/XML/
