<?php
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'aks':
        include 'index_aks.php';
        break;
    case 'app':
        include 'index_app.php';
        break;
    default:
        echo "<p style='color:red;'>Welcome to the homepage!</p>";
        break;
}
?>
