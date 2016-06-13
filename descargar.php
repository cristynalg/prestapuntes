<?php 
	session_start();
	//Me aseguro de que el usuario este logueado para poder acceder a esta parte de la web.
    if (!isset($_SESSION['idUsuario'])) {
        header("Location: index.php");
    }
	require_once "conexionbdd.php";
    require_once "conexionftp.php";
    //Si la llamada a esta pagina se ha hecho con el metodo GET, recogemos en una variable el idDocumento para despues realizar la consulta a la BDD sobre ese documento en cuestion y descargarlo del FTP.
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if(isset($_GET['idDocumento'])) {
            $idDocumento = $_GET['idDocumento'];

            $consulta = "SELECT Titulo, IDUsuario, NombreDocFTP FROM documento WHERE IDDocumento = ?";
            //Preparamos consulta sql.
            $statement = mysqli_prepare($conexion, $consulta);
            //Agregamos variables a la consulta sql, pasados como parametros.
            mysqli_stmt_bind_param($statement,'i', $idDocumento);
            //Ejecutamos la sentencia.
            mysqli_stmt_execute($statement);
            //Vinculamos variables a la sentencia preparada para el almacenamiento de resultados.
            mysqli_stmt_bind_result($statement, $titulo, $idUsuario, $nombreDocFTP);
            //Obtenemos los resultados de la sentencia preparada recorriendo la lista de resultados posibles.
            while (mysqli_stmt_fetch($statement)) {
				//Devuelve información acerca de la ruta de un fichero, en nuestro caso, su extensión.
				$extension = pathinfo($nombreDocFTP, PATHINFO_EXTENSION);
				//La ruta del archivo local (será reemplazado si el archivo ya existe).
		        $local_file = $titulo . '.' . $extension;
		        //La ruta del archivo remoto.
				$server_file = $nombreDocFTP;
				//Nos situamos en el directorio.
				ftp_chdir($conn_id, $idUsuario);
				//Intentamos descargar $server_file y guardarlo en $local_file.
				ftp_get($conn_id, $local_file, $server_file, FTP_BINARY);
				//Cerramos la conexión FTP.
				ftp_close($conn_id);
				//Cabeceras HTTP que definen el formato del documento. Fuerzan la descarga y el entendimiento con el navegador del cliente.
				header('Content-Type: application/force-download');
			    header('Content-Disposition: attachment; filename="'.$local_file.'"');
			    header('Content-Transfer-Encoding: binary');
			    header('Content-Length: '.filesize($local_file));
			    ob_end_clean();
		        flush();
				readfile($local_file);
				unlink($local_file);
		    }
		}
    }
?>