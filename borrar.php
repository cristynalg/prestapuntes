<?php 
	session_start();
    //Me aseguro de que el usuario este logueado para poder acceder a esta parte de la web.
    if (!isset($_SESSION['idUsuario'])) {
        header("Location: index.php");
    }

    $esBorradoFTP = false;
    $esBorrado = false;

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if(isset($_GET['idDocumento'])) {
            $idDocumento = $_GET['idDocumento'];
            $idUsuario = $_SESSION['idUsuario'];
            //Nos conectamos a la BDD.
	        require_once "conexionbdd.php";
	        
	        $consulta = "SELECT NombreDocFTP FROM documento WHERE IDDocumento = ? and IDUsuario = ?";
	        //Preparamos consulta sql.
	        $statement = mysqli_prepare($conexion, $consulta);
	        //Agregamos variables a la consulta sql, pasados como parametros.
	        mysqli_stmt_bind_param($statement,'ii', $idDocumento, $idUsuario);
	        //Ejecutamos la sentencia.
	        mysqli_stmt_execute($statement);
	        //Vinculamos variables a la sentencia preparada para el almacenamiento de resultados.
	        mysqli_stmt_bind_result($statement, $nombreDocFTP);
	        //Obtenemos los resultados de la sentencia preparada recorriendo la lista de resultados posibles.
            while (mysqli_stmt_fetch($statement)) {
		        //Nos conectamos al FTP.
		        require_once "conexionftp.php";
		        //Nos situamos en el directorio del usuario logueado.
				ftp_chdir($conn_id, $idUsuario);
				//Intentamos borrar $nombreDocFTP en la ruta remota (servidor FTP).
				ftp_delete($conn_id, $nombreDocFTP);
				//Cerramos la conexión FTP.
				ftp_close($conn_id);
				//En este instante, el archivo ha sido borrado del servidor FTP.
				$esBorradoFTP = true;
			}

			mysqli_stmt_close($statement);

			//Si hemos borrado el archivo del servidor FTP, procedemos a borrar el dato de la BDD.
			if ($esBorradoFTP) {
				$borrado = "DELETE FROM documento WHERE IDDocumento = ? and IDUsuario = ?";
				//Preparamos consulta sql.
		        $sentencia = mysqli_prepare($conexion, $borrado) or die(mysqli_error($conexion));
		        mysqli_stmt_bind_param($sentencia,'ii', $idDocumento, $idUsuario);
		        if ( !mysqli_execute($sentencia) ) {
				  die( 'stmt error: '.mysqli_stmt_error($sentencia) );
				}
		        mysqli_stmt_execute($sentencia);
		        mysqli_stmt_close($sentencia);
		        //En este instante ya se ha borrado el dato del archivo de la BDD.
		        $esBorrado = true;
			}
		}
	}

	//Si todo ha salido bien, mostramos de nuevo la lista de archivos subidos del usuario de manera actualizada.
	if ($esBorrado) {
		header("Location: mostrarsubidos.php?esBorrado=".$esBorrado);
	}
?>
