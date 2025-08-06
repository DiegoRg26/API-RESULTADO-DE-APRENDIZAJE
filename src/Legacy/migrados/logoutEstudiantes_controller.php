<?php
session_start();
session_destroy();
header("Location: https://www.uninunez.edu.co/estudiantes.html");
exit();
?>