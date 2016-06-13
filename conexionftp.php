<?php
	//Establecer la conexión FTP básica
	$ftp_server = "ftp.prestapuntes.com";
	$conn_id = ftp_connect($ftp_server);
	//Iniciar sesión con nombre de usuario y contraseña
	$ftp_user_name = "webmasterftp";
	$ftp_user_pass = "apuntes";
	$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
	//Comprobar la conexion
	if ((!$conn_id) || (!$login_result)) {
	    die("¡La conexión FTP ha fallado! Puede ser que el servidor FTP esté caído o haya algún error al intentar subir un archivo. Por favor intente de nuevo más tarde.");
	}
?>