<?php
	//Destruimos la sesión del usuario activo en sesión.
	session_start();
	session_destroy();
	header("Location: index.php");
?>