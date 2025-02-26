<html>
<body>

Username <?php echo $_POST["inputUserame"]; ?><br>
Password <?php echo $_POST["inputPassword"]; ?>

<?php

include 'functions.php';

echo "<br>";

if (login($_POST["inputUsername"], $_POST["inputPassword"])) {
    echo "Logged in";
} else {
    echo "Invalid credentials";
}
?>

</body>
</html>