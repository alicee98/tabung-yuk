<?php
require __DIR__.'/includes/app.php';
go(empty($_SESSION['user']) ? 'login.php' : 'beranda.php');
