<html>
<body>

Username <?php echo $_POST["myTexty"]; ?><br>
Password <?php echo $_POST["myTexty1"]; ?>

<?php


$db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

function userExists($name) {
    global $db;
    
    $statement = $db->prepare('SELECT * from  "tbl_user" WHERE username = :name;');
    $statement->bindValue(':name', $name);
    $result = $statement->execute();
    
    while ($row = $result->fetchArray()) {
        return true;
    }
    return false;
}

function checkUser($name, $pass) {
    global $db;

    if (!userExists($name)) {
        return false;
    }
    
    $statement = $db->prepare('SELECT * from  "tbl_user" WHERE username = :name AND password = :pass;');
    $statement->bindValue(':name', $name);
    $statement->bindValue(':pass', $pass);
    $result = $statement->execute();
    
    while ($row = $result->fetchArray()) {
        return true;
    }
    return false;
}

function registerUser($name, $pass) {
    global $db;

    $db->exec('BEGIN;');
    
    $db->query('INSERT INTO "tbl_user" (username, password) VALUES ("' . $name . '", "' . $pass . '");');

    $db->exec('COMMIT;');

    return true;
}

echo "<br>";
echo registerUser($_POST["myTexty"], $_POST["myTexty1"]);

?>

</body>
</html>