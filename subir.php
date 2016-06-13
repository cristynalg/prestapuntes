<?php 
    include("cabecera.php");
    //Me aseguro de que el usuario este logueado para poder acceder a esta parte de la web.
    if (!isset($_SESSION['idUsuario'])) {
        header("Location: index.php");
    }
   

    //Creamos diversas variables para mostrar información en el HTML.
    $titulo ="";
    $descripcion ="";
    $idAsignatura ="";
    $archivoAdjunto ="";
    $tituloError ="";
    $claseTituloError ="";
    $descripcionError ="";
    $claseDescripcionError ="";
    $cicloError ="";
    $claseCicloError ="";
    $asignaturaError ="";
    $claseAsignaturaError ="";
    $subirAdjuntoError ="";
    $claseSubirAdjuntoError ="";
    $errorEnCampos = false;
    $errorArchivo = false;
    $mostrarModal = false;


    //Si el formulario de cambio de datos ha sido enviado mediante POST, validamos campos del formulario.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //Si "titulo" no es vacio y cumple con las directrices devolvemos $errorEnCampos = true y en otro caso false.
        //El resto de IFs cumplen una función similar a la del titulo, validar los campos antes de subir el archivo o ingresar los datos en base de datos.
        echo $_POST["titulo"];
        if (empty($_POST["titulo"])) {
            $tituloError = "El título es requerido";
            $claseTituloError ="has-error";
            $errorEnCampos = true;
        } else {
            $titulo = strtolower($_POST["titulo"]);
            $titulo = ucfirst($titulo);
            if ((strlen($titulo) < 3) or (strlen($titulo) > 40)) {
                $tituloError = "Error. El título escrito debe tener de 3 a 40 caracteres";
                $claseTituloError ="has-error";
                $errorEnCampos = true;
            }  
        }
        if (empty($_POST["descripcion"])) {
            $descripcionError = "La descripción es requerida";
            $claseDescripcionError ="has-error";
            $errorEnCampos = true;
        } else {
            $descripcion = strtolower($_POST["descripcion"]);
            $descripcion = ucfirst($descripcion);
            if ((strlen($descripcion) < 5) or (strlen($descripcion) > 180)) {
                $descripcionError = "Error. La descripción escrita debe tener de 5 a 180 caracteres";
                $claseDescripcionError ="has-error";
                $errorEnCampos = true;
            }  
        }
        if (empty($_POST["ciclo"])) {
            $cicloError ="Error. No puedes dejar vacío el ciclo";
            $claseCicloError ="has-error";
            $errorEnCampos = true;    
        }
        if (empty($_POST["asignatura"])) {
            $asignaturaError ="Error. No puedes dejar vacía la asignatura";
            $claseAsignaturaError ="has-error";
            $errorEnCampos = true;
        } else {
            $idAsignatura = $_POST["asignatura"];
        }
        //Guardamos una cadena que contiene una ruta a un directorio. Esta función (basename) devolverá el último componente de nombre. 
        $ruta_fichero = basename($_FILES["archivoAdjunto"]["name"]);
        //Devuelve información acerca de la ruta de un fichero, en nuestro caso, su extensión.
        $tipoArchivo = pathinfo($ruta_fichero, PATHINFO_EXTENSION);
        
        //Comprobamos que se ha introducido un archivo
        if (empty($_FILES["archivoAdjunto"]["name"])) {
             $subirAdjuntoError ="Error. Tienes que seleccionar un archivo para subir";
             $errorEnCampos = true;
             $errorArchivo = true;
        }
        // Comprobamos si el archivo es mayor a 7MB (en Bytes). Se ha modificado el fichero php.ini (upload_max_filesize y post_max_size) para admitir ficheros de hasta un determinado tamaño máximo.
        if (!$errorArchivo && $_FILES["archivoAdjunto"]["size"] > 7340032) {
            $subirAdjuntoError ="Error. El archivo que quieres subir tiene más 7 MB";
            $claseSubirAdjuntoError ="has-error";
            $errorEnCampos = true;
            $errorArchivo = true;
        }
        //Comprobamos el formato de los archivos que se desean subir al servidor.
        if (!$errorArchivo && $tipoArchivo != "txt" && $tipoArchivo != "rtf" && $tipoArchivo != "pdf"
            && $tipoArchivo != "odt" && $tipoArchivo != "ods" && $tipoArchivo != "odp"
            && $tipoArchivo != "odb" && $tipoArchivo != "odg" && $tipoArchivo != "doc"
            && $tipoArchivo != "docx" && $tipoArchivo != "dot" && $tipoArchivo != "xls"
            && $tipoArchivo != "xlsx" && $tipoArchivo != "ppt" && $tipoArchivo != "pptx"
            && $tipoArchivo != "pps" && $tipoArchivo != "ppsx" && $tipoArchivo != "xls"
            && $tipoArchivo != "docx" && $tipoArchivo != "dot" && $tipoArchivo != "xls") {
            $subirAdjuntoError ="Error. Solo se permiten ficheros de tipo microsoft office word, excel o power point, libre u open office, o pdf";
            $claseSubirAdjuntoError ="has-error";
            $errorEnCampos = true;
            $errorArchivo = true;
        }
        
        //Si no ha habido errores de validacion en los campos del formulario, insertamos los datos en la base de datos y cargamos en el servidor FTP el documento. En el caso de que haya algun error, la transaccion no se completará y se desharán los cambios propuestos.
        if (!$errorEnCampos) {
            //En el momento que no hay errores de validacion de campos, podemos conectarnos al servidor FTP y al de BDD.
            require_once "conexionftp.php";
            require_once "conexionbdd.php";

            //El archivo en el servidor FTP tendrá un nombre que será fruto de la fecha de subida del mismo.
            $documento_ftp = date("Ymd_His") . "." . $tipoArchivo;
            //El archivo de manera temporal, tendrá el nombre que el propio usuario le haya puesto en "titulo".
            $documento_temp = $_FILES["archivoAdjunto"]["tmp_name"];

            //Generamos la consulta de inserción en base de datos.
            $insert = "INSERT INTO documento (IDUsuario, IDAsignatura, Titulo, Descripcion, NombreDocFTP) values (?, ?, ?, ?, ?)";
            //Preparamos consulta de insercion sql y la ejecutamos.
            $statement = mysqli_prepare($conexion, $insert);
            mysqli_stmt_bind_param($statement,'iisss', $idUsuario, $idAsignatura, $titulo, $descripcion, $documento_ftp);
            mysqli_stmt_execute($statement);
            mysqli_stmt_close($statement);
            
            //FTP: Cargar un archivo.
            //Comprobamos si el directorio del usuario existe, si no existe lo creamos, y despues insertamos el archivo dentro del mismo.
            if (!is_dir("ftp://".$ftp_user_name.":".$ftp_user_pass."@".$ftp_server."/".$_SESSION['idUsuario'])) {
                ftp_mkdir($conn_id, $_SESSION['idUsuario']);
            }
            ftp_chdir($conn_id, $_SESSION['idUsuario']);
            if (ftp_put($conn_id, $documento_ftp, $documento_temp, FTP_BINARY)) {
                //Si la subida del archivo al servidor FTP ha ido bien, mostramos una moda con un mensaje afirmativo.
                $mostrarModal = true;
            } else {
                //Si hay error en el FTP, mostramos un mensaje de error, y por defecto no se guarda nada.
                $subirAdjuntoError ="Lo siento, ha habido algún error interno en el servidor FTP y el archivo no se ha subido. Inténtelo de nuevo";
                $claseSubirAdjuntoError ="has-error";
            }
            ftp_close($conn_id);
        }
    }  

    if ($mostrarModal) {  //Si $mostrarModal es true, mostramos una modal con un mensaje afirmativo   ?>
        <script>
            $(document).ready(function() {
                $('#modalSubidaOK').modal('show');
            });
        </script>
    <?php }
?>

<script>
    //Creamos un array de asignaturas que contendran a su vez un array de objetos JSON, los cuales identifican a cada asignatura de cada ciclo concreto.
    asignaturasSMR = [  {"idAsignatura": 1, "nombre": "Montaje y mantenimiento de equipos"},
                        {"idAsignatura": 2, "nombre": "Sistemas operativos monopuesto"},
                        {"idAsignatura": 3, "nombre": "Aplicaciones ofimáticas"},
                        {"idAsignatura": 4, "nombre": "Redes locales"},
                        {"idAsignatura": 5, "nombre": "Sistemas operativos en red"},
                        {"idAsignatura": 6, "nombre": "Servicios en red"},
                        {"idAsignatura": 7, "nombre": "Seguridad informática"},
                        {"idAsignatura": 8, "nombre": "Aplicaciones web"}                        
                    ];

    asignaturasASIR = [  {"idAsignatura": 9, "nombre": "Fundamentos de hardware"},
                        {"idAsignatura": 10, "nombre": "Gestión de bases de datos"},
                        {"idAsignatura": 11, "nombre": "Implantación de sistemas operativos"},
                        {"idAsignatura": 12, "nombre": "Lenguajes de marcas y sistemas de gestión de información"},
                        {"idAsignatura": 13, "nombre": "Planificación y administración de redes"},
                        {"idAsignatura": 14, "nombre": "Administración de sistemas gestores de bases de datos"},
                        {"idAsignatura": 15, "nombre": "Administración de sistemas operativos"},
                        {"idAsignatura": 16, "nombre": "Implantación de aplicaciones web"},
                        {"idAsignatura": 17, "nombre": "Seguridad y alta disponibilidad"},
                        {"idAsignatura": 18, "nombre": "Servicios de red e internet"}                        
                    ];

    asignaturasDAW = [  {"idAsignatura": 19, "nombre": "Sistemas informáticos"},
                        {"idAsignatura": 20, "nombre": "Bases de datos"},
                        {"idAsignatura": 21, "nombre": "Programación"},
                        {"idAsignatura": 22, "nombre": "Lenguajes de marcas y sistemas de gestión de información"},
                        {"idAsignatura": 23, "nombre": "Entornos de desarrollo"},
                        {"idAsignatura": 24, "nombre": "Desarrollo web en entorno cliente"},
                        {"idAsignatura": 25, "nombre": "Desarrollo web en entorno servidor"},
                        {"idAsignatura": 26, "nombre": "Despliegue de aplicaciones web"},
                        {"idAsignatura": 27, "nombre": "Diseño de interfaces web"},
                        
                    ];   

    asignaturasDAM = [  {"idAsignatura": 28, "nombre": "Sistemas informáticos"},
                        {"idAsignatura": 29, "nombre": "Bases de datos"},
                        {"idAsignatura": 30, "nombre": "Programación"},
                        {"idAsignatura": 31, "nombre": "Lenguajes de marcas y sistemas de gestión de información"},
                        {"idAsignatura": 32, "nombre": "Entornos de desarrollo"},
                        {"idAsignatura": 33, "nombre": "Acceso a datos"},
                        {"idAsignatura": 34, "nombre": "Desarrollo de interfaces"},
                        {"idAsignatura": 35, "nombre": "Programación multimedia y dispositivos móviles"},
                        {"idAsignatura": 36, "nombre": "Programación de servicios y procesos"},
                        {"idAsignatura": 37, "nombre": "Sistemas de gestión empresarial"}                        
                    ];

    asignaturasOtros = [ {"idAsignatura": 38, "nombre": "Otras"} ];

    //Cuando el select sea desplegado, cambiado, se cargaran en listaAsignaturas el array que corresponde a las asignaturas segun el ciclo (a través de su ID) 
    $(document).ready(function() {
        $('#ciclo').change(function(e) {
            var idCiclo = $('#ciclo').val();
            var listaAsignaturas;
            if (idCiclo == 1) {
                listaAsignaturas = asignaturasSMR;
            } else if (idCiclo == 2) {
                listaAsignaturas = asignaturasASIR;
            } else if (idCiclo == 3) {
                listaAsignaturas = asignaturasDAW;
            } else if (idCiclo == 4) {
                listaAsignaturas = asignaturasDAM;
            } else if (idCiclo == 5) {
                listaAsignaturas = asignaturasOtros;
            } else {
                listaAsignaturas = [];
            }

            //Limpiamos select de asignaturas
            $('#asignatura').find('option').remove().end()
                .append("<option value='' selected='true'>Seleccione...</option>");

            $.each(listaAsignaturas, function( index, asignatura ) {
                //El parámetro asignatura referencia a cada objeto Asignatura de listaAsignaturas.
                $('<option>').val(asignatura.idAsignatura).text(asignatura.nombre).appendTo('#asignatura');
            });

        });
    });
</script>

    <div id="cuerpo" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    	<div>
            <h2 class="text-primary titulosPaginas"><strong>SUBIR ARCHIVO</strong></h2>
        </div>
        <div id="subirArchivos"class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <!--FORMULARIO DE SUBIDA DE ARCHIVOS POR PARTE DEL USUARIO LOGUEADO-->
            <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" name="subirArchivo" id="subirArchivo">
                <div class="form-group">
                    <label for="titulo" class="control-label estilosubirArchivo <?= $claseTituloError ?>">Título:
                        <input type="text" class="form-control" name ="titulo" id="titulo" value="<?= $titulo ?>" minlength="3" maxlength="40" size="50">
                        <label class="error"><?= $tituloError ?></label>
                    </label>
                </div>
                <div class="form-group">
                   <label for="descripcion" class="control-label estilosubirArchivo <?= $claseDescripcionError ?>">Descripción:
                       <textarea rows="4" cols="48" class="form-control textarea" name="descripcion" id="descripcion" minlength="5" maxlength="180" size="150"><?= $descripcion ?></textarea>
                       <label class="error"><?= $descripcionError ?></label>
                   </label>
                </div>
                <div class="form-group">
                    <label for="ciclo" class="control-label estilosubirArchivo <?= $claseCicloError ?>">Ciclo:
                        <select class="form-control select" name="ciclo" id="ciclo">
                        	<option value="" selected="true">Seleccione...</option>
        					<option value="1">SMR</option>
        					<option value="2">ASIR</option>
        					<option value="3">DAW</option>
        					<option value="4">DAM</option>
        					<option value="5">OTROS</option>
        				</select>
                        <label class="error"><?= $cicloError ?></label>
                   </label>
                   <label for="asignatura" class="control-label estilosubirArchivo <?= $claseAsignaturaError ?>">Asignatura:
                        <select class="form-control select" name="asignatura" id="asignatura">
                            <option value="" selected="true">Seleccione...</option>
                        </select>
                        <label class="error"><?= $asignaturaError ?></label>
                   </label>
                </div>
                <div class="form-group">
                    <label for="subirArchivoAdjunto" class="control-label estilosubirArchivo <?= $claseSubirAdjuntoError ?>">Adjuntar un archivo:
                        <input type="file" id="archivoAdjunto" name="archivoAdjunto">
                        <p class="bloqueAyuda">Examine en su equipo para localizar el archivo que quiere subir a Prestapuntes</p>
                        <label class="error"><?= $subirAdjuntoError ?></label>
                    </label>
                </div>
                <div class="form group">
                    <input type="submit" class="btn btn-primary" name="enviarSubirArchivo" id="enviarSubirArchivo" value="Enviar datos">
                </div>
            </form>
        </div>
        <!--MODAL DE SUBIDA DE ARCHIVO OK-->
                <div class="modal fade" id="modalSubidaOK" tabindex="-1" role="dialog" aria-labelledby="modalSubidaOK">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                          <h4 class="modal-title" id="modalSubidaOK">Subida de archivo al servidor</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-success" role="alert">La subida de su archivo se ha realizado exitosamente. Gracias por su solidaridad, ¡sigue compartiendo tus apuntes con esta comunidad!</div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal" id="cerrarSubidaOK">Cerrar</button>
                        </div>
                      </div>
                    </div>
                </div>
    </div>
<?php include("pie.php"); ?>