<?php
    include("cabecera.php");
    //Me aseguro de que el usuario este logueado para poder acceder a esta parte de la web.
    if (!isset($_SESSION['idUsuario'])) {
        header("Location: index.php");
    }
    
    //Nos conectamos a la BDD.
	require_once "conexionbdd.php";

    //Creamos diversas variables para mostrar información en el HTML.
    $idUsuario = $_SESSION['idUsuario'];
    $titulo ="";
    $descripcion ="";
    $tituloError ="";
    $claseTituloError ="";
    $descripcionError ="";
    $claseDescripcionError ="";
    $errorEnCampos = false;
    $mostrarModal = false;

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if(isset($_GET['idDocumento'])) {
            $idDocumento = $_GET['idDocumento'];
           
	        $consulta = "SELECT Titulo, Descripcion FROM documento WHERE IDDocumento = ? and IDUsuario = ?";
	        //Preparamos consulta sql.
	        $statement = mysqli_prepare($conexion, $consulta);
	        //Agregamos variables a la consulta sql, pasados como parametros.
	        mysqli_stmt_bind_param($statement,'ii', $idDocumento, $idUsuario);
	        //Ejecutamos la sentencia.
	        mysqli_stmt_execute($statement);
	        //Vinculamos variables a la sentencia preparada para el almacenamiento de resultados.
            mysqli_stmt_bind_result($statement, $titulo, $descripcion);
            //Obtenemos el titulo y descripción del documento seleccionado para su modificación a partir del resultado.
	        mysqli_stmt_fetch($statement);
            //Cerramos la sentencia.
        	mysqli_stmt_close($statement);
        }
    }    	
    //Si el formulario de cambio de datos ha sido enviado mediante POST, validamos campos del formulario.
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$idUsuario = $_SESSION['idUsuario'];
	    //Si "titulo" no es vacio y cumple con las directrices devolvemos $errorEnCampos = true y en otro caso false.
	    //El if de descripcion tambien valida el campo antes modificar los datos en base de datos.
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

	    if (!$errorEnCampos) {
            $idDocumento = $_POST['idDocumento'];
	        //En el momento que no hay errores de validacion de campos podremos modificar los datos en la BDD.
	        $update = "UPDATE documento SET Titulo = ?, Descripcion = ? WHERE IDDocumento = ? and IDUsuario = ?";
	        //Preparamos consulta sql.
	        $statement = mysqli_prepare($conexion, $update);	    
	        //Agregamos variables a la consulta sql, pasados como parametros.
	        mysqli_stmt_bind_param($statement,'ssii', $titulo, $descripcion, $idDocumento, $idUsuario); 
	        //Ejecutamos la sentencia.
	        $resultado = mysqli_stmt_execute($statement);
	        $_SESSION['titulo'] = $titulo;
	        $_SESSION['descripcion'] = $descripcion;
	        //Cerramos la sentencia.
	        mysqli_stmt_close($statement);

	        header("Location: mostrarsubidos.php?esModificado=".$resultado);
    	}
	}

?>
    <!--CONTENEDOR DEL CUERPO DE LA PÁGINA-->
    <div id="cuerpo" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 titulosPaginas">
            <h2 class="text-primary"><strong>MODIFICAR ARCHIVO</strong></h2>
        </div>
        <div id="modificacionArchivos"class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        	<div class="apartadoInfo">
                <h5 class="text-info">En este apartado podrás cambiar el título y la descripción de uno de los archivos subidos por tí</h5>
            </div>
            <!--FORMULARIO DE SUBIDA DE ARCHIVOS POR PARTE DEL USUARIO LOGUEADO-->
            <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" name="modificarArchivo" id="modificarArchivo">
                <input type="hidden" name="idDocumento" value="<?= $idDocumento ?>">
                <div class="form-group">
                    <label for="titulo" class="control-label estiloModificarArch <?= $claseTituloError ?>">Título:
                        <input type="text" class="form-control" name ="titulo" id="titulo" value="<?= $titulo ?>" minlength="3" maxlength="40" size="50">
                        <label class="error"><?= $tituloError ?></label>
                    </label>
                </div>
                <div class="form-group">
                   <label for="descripcion" class="control-label estiloModificarArch <?= $claseDescripcionError ?>">Descripción:
                       <textarea rows="4" cols="48" class="form-control textarea" name="descripcion" id="descripcion" minlength="5" maxlength="180" size="150"><?= $descripcion ?></textarea>
                       <label class="error"><?= $descripcionError ?></label>
                   </label>
                </div>
                <div class="form-group">
                	<input type="submit" class="btn btn-primary" name="enviarModifArch" id="enviarModifArch" value="Enviar datos">
                </div>
            </form>
        </div>        
    </div>
<?php include("pie.php"); ?>