<?php

include 'functions.php';

echo "<br>";

$redirect = "";

if (login($_POST["inputUsername"], $_POST["inputPassword"])) {
    $redirect = "<meta http-equiv='refresh' content='0;url=index.php'>";
} else {
    echo "Invalid credentials";
}
?>

<html>
    <head>
        <title>Jontys Locksmiths</title>
        <link rel="stylesheet" type="text/css" href="styles.css">
        <?php echo $redirect; ?>
    </head>
    <body>
        <main>
            <span>Username <?php echo $_POST["inputUsername"]; ?></span><br />
            <span>Password <?php echo $_POST["inputPassword"]; ?></span>
    </body>
</html>