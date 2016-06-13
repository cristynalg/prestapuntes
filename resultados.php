<?php 
    include("cabecera.php");
    require_once "conexionbdd.php";
    require_once "conexionftp.php";

    $hayResultados = false;

    //La consulta se realiza mediante el campo de buscar de la web.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if(isset($_POST['campoBuscar'])) {
            $buscar = $_POST['campoBuscar'];
            $consulta = "SELECT doc.IDDocumento, doc.Titulo, doc.Descripcion, asi.Nombre, ci.Abreviatura FROM documento doc, asignatura asi, ciclo ci WHERE doc.IDAsignatura = asi.IDAsignatura and asi.IDCiclo = ci.IDCiclo and (doc.Titulo LIKE CONCAT('%', ?, '%') or doc.Descripcion LIKE CONCAT('%', ?, '%'))";
            //Preparamos consulta sql.
            $statement = mysqli_prepare($conexion, $consulta);
            //Agregamos variables a la consulta sql, pasados como parametros.
            mysqli_stmt_bind_param($statement,'ss', $buscar, $buscar);
            //Ejecutamos la sentencia.
            mysqli_stmt_execute($statement);
            //Vinculamos variables a la sentencia preparada para el almacenamiento de resultados.
            mysqli_stmt_bind_result($statement, $idDocumento, $titulo, $descripcion, $nombreAsig, $abreviaturaCiclo);
        }
    }
    //La consulta se realiza directamente desde el menu de ciclos de la web.
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if(isset($_GET['idAsignatura'])) {
            $idAsignatura = $_GET['idAsignatura'];
            $consultaFiltrada = "SELECT doc.IDDocumento, doc.Titulo, doc.Descripcion, asi.Nombre, ci.Abreviatura FROM documento doc, asignatura asi, ciclo ci WHERE doc.IDAsignatura = ? and doc.IDAsignatura = asi.IDAsignatura and asi.IDCiclo = ci.IDCiclo";
            //Preparamos consulta sql.
            $statement = mysqli_prepare($conexion, $consultaFiltrada);
            //Agregamos variables a la consulta sql, pasados como parametros.
            mysqli_stmt_bind_param($statement,'s', $idAsignatura);
            //Ejecutamos la sentencia.
            mysqli_stmt_execute($statement);
            //Vinculamos variables a la sentencia preparada para el almacenamiento de resultados.
            mysqli_stmt_bind_result($statement, $idDocumento, $titulo, $descripcion, $nombreAsig, $abreviaturaCiclo);
        } else {
            header("Location: index.php");
        }
    }
?>

<div id="cuerpo" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 titulosPaginas">
            <h2 class="text-primary"><strong>RESULTADOS</strong></h2>
        </div>
        <div id="resultados" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="apartadoInfo">
                <h5 class="text-info">Debes estar registrado para poder descargar documentos</h5>
            </div>
    <?php
        //Obtemos los resultados de la sentencia preparada en las variables vinculadas. Si la ejecucion del programa entra por el while, es que hay resultados.
        while (mysqli_stmt_fetch($statement)) {
            $hayResultados = true;
    ?>
        <div class="media">
        	<div class="media-body">
                <h4 class="media-heading">
                    <?php
                        //Si el usuario está logueado en la web, podrá ver el icono de descargar de los archivos, si no, solo verá titulos y descripciones.
                        if(isset($_SESSION['idUsuario'])) { ?>
                            <a href="descargar.php?idDocumento=<?=$idDocumento?>" class="btn btn-primary btn-xs" target="_blank" role="button" aria-label="Left Align">
                                <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
                            </a>
                    <?php } ?>
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

        if (!$hayResultados) { //Si no hay resultados generados en el bucle while mostramos mensaje  ?>
            <h4>No se han encontrado resultados</h4>
        <?php }
    ?>
</div>
<?php include("pie.php"); ?>