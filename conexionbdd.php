<?php
	//Datos para la conexión a la base de datos de MySQL.
	define('DB_SERVER','bdd.prestapuntes.com');
	define('DB_NAME','prestapuntes');
	define('DB_USER','cristina');
	define('DB_PASS','apuntes');

	$conexion = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	mysqli_set_charset($conexion, "utf8");
	
	if (!$conexion) {
		trigger_error('mysqli Connection failed! ' . htmlspecialchars(mysqli_connect_error()), E_USER_ERROR);
	}
?>