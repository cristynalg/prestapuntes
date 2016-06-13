<?php 
    include("cabecera.php");
    //Me aseguro de que el usuario este logueado para poder acceder a esta parte de la web.
    if (!isset($_SESSION['idUsuario'])) {
        header("Location: index.php");
    }
    require_once "conexionbdd.php";

    $consulta = "SELECT doc.IDDocumento, doc.IDUsuario, doc.Titulo, doc.Descripcion, asi.Nombre, ci.Abreviatura FROM documento doc, asignatura asi, ciclo ci WHERE doc.IDAsignatura = asi.IDAsignatura and asi.IDCiclo = ci.IDCiclo and doc.IDUsuario like ?";
    //Preparamos consulta sql.
    $statement = mysqli_prepare($conexion, $consulta);
    //Agregamos variables a la consulta sql, pasados como parametros.
    mysqli_stmt_bind_param($statement,'i', $idUsuario);
    //Ejecutamos la sentencia.
    mysqli_stmt_execute($statement);
    //Vinculamos variables a la sentencia preparada para el almacenamiento de resultados.
    mysqli_stmt_bind_result($statement, $idDocumento, $idUsuario, $titulo, $descripcion, $nombreAsig, $abreviaturaCiclo);

    $mostrarModalOK = false;
    $mostrarModalErr = false;
    $hayResultados = false;

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
     	if(isset($_GET['esBorrado'])) {
     		$esBorrado = $_GET['esBorrado'];
     		if ($esBorrado == true) {
     			$mostrarModalOK = true;
	     	} else {
	     		$mostrarModalErr = true;
	     	}
	    } 	
     }

     if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if(isset($_GET['esModificado'])) {
            $esModificado = $_GET['esModificado'];
            if ($esModificado == true) {
                $mostrarModalOK = true;
            } else {
                $mostrarModalErr = true;
            }
        }   
     }
?>

<div id="cuerpo" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 titulosPaginas">
            <h2 class="text-primary"><strong>ARCHIVOS SUBIDOS</strong></h2>
        </div>
        <div id="mostrarArchivos"class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="apartadoInfo">
                <h5 class="text-info">En este apartado podrás visualizar la lista de archivos subidos por tí, modificar y borrar dichos archivos</h5>
            </div>
    <?php
        //Obtemos los resultados de la sentencia preparada en las variables vinculadas. Si la ejecucion del programa entra por el while, es que hay resultados.
        while (mysqli_stmt_fetch($statement)) {
            $hayResultados = true;
    ?>
        <div class="media">
        	<div class="media-body">
                <h4 class="media-heading">
                	<a href="modificar.php?idDocumento=<?=$idDocumento?>" class="btn btn-primary btn-xs" role="button" aria-label="Left Align"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>
                    <a href="borrar.php?idDocumento=<?=$idDocumento?>" class="btn btn-primary btn-xs" role="button" aria-label="Left Align"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>
                    <?= $titulo ?>
                    <span class="label label-info tamanoAbreviatura"><?= $abreviaturaCiclo ?></span> 
                    <small><?= $nombreAsig ?></small>
                </h4>
        	    <?= $descripcion ?> 
        	</div>
    	</div>
    <?php
        }
        //Cerramos la sentencia.
        mysqli_stmt_close($statement);

        if (isset($_GET['esBorrado'])) { ?> <!-- Si se ha borrado, mostramos modal-->
        <script>
            $(document).ready(function() {
                $('#modalBorrado').modal('show');
            });
        </script>
        <?php }

        if (isset($_GET['esModificado'])) { ?> <!-- Si se ha modificado, mostramos modal-->
        <script>
            $(document).ready(function() {
                $('#modalModificado').modal('show');
            });
        </script>

        <?php }

        if (!$hayResultados) { //Si no hay resultados generados en el bucle while mostramos mensaje  ?>
            <h4>No hay archivos subidos</h4>
        <?php }

    ?>

    <div class="modal fade" id="modalBorrado" tabindex="-1" role="dialog" aria-labelledby="modalBorrado">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
            <!--BORRADO DE ARCHIVO OK-->
            <?php if ($mostrarModalOK) { ?>
                <h4 class="modal-title">Archivo borrado del servidor</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-success" role="alert">El borrado de su archivo en el servidor se ha realizado exitosamente</div>
            </div>
            <?php } ?>
            <!--BORRADO DE ARCHIVO ERROR-->
            <?php if ($mostrarModalErr) { ?>
              <h4 class="modal-title">Error borrar archivo</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">El borrado de su archivo en el servidor no se ha podido completar. Por favor intente de nuevo la operación de borrado del archivo deseado</div>
            </div>
            <?php } ?>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal" id="cerrarBorrado">Cerrar</button>
            </div>
          </div>
        </div>
    </div>


    <div class="modal fade" id="modalModificado" tabindex="-1" role="dialog" aria-labelledby="modalModificado">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
            <!--MODIFICACIÓN OK-->
            <?php if ($mostrarModalOK) { ?>
                <h4 class="modal-title">Modificación datos del archivo</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-success" role="alert">Formulario de modificación completado satisfactoriamente. Su archivo tiene un nuevo título y descripción</div>
            </div>
            <?php } ?>
            <!--MODIFICACIÓN ERROR-->
            <?php if ($mostrarModalErr) { ?>
              <h4 class="modal-title">Error modificación del archivo</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">La modificación del archivo no ha podido completarse. Por favor inténtelo de nuevo más tarde</div>
            </div>
            <?php } ?>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal" id="cerrarModificado">Cerrar</button>
            </div>
          </div>
        </div>
    </div>

</div>
<?php include("pie.php"); ?>