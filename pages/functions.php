<?php function userExists($name) {
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);
    
    $statement = $db->prepare('SELECT * from  "tbl_user" WHERE username = :name;');
    $statement->bindValue(':name', $name);
    $result = $statement->execute();
    
    while ($row = $result->fetchArray()) {
        return true;
    }
    return false;
}

function checkCredentials($name, $pass) {
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

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
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    $db->exec('BEGIN;');
    
    $db->query('INSERT INTO "tbl_user" (username, password) VALUES ("' . $name . '", "' . $pass . '");');

    $db->exec('COMMIT;');

    return true;
}

function getCookie() {
    if (isset($_COOKIE["session"])) {
        return $_COOKIE["session"];
    }
    return "";
}


function setAuthCookie($cookie) {
    setcookie("session", $cookie, time() + (86400 * 30), "/");
}

function checkCookie($cookie) {
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    $statement = $db->prepare('SELECT * from  "tbl_auth" WHERE auth_cookie = :cookie;');
    $statement->bindValue(':cookie', $cookie);
    $result = $statement->execute();
    
    while ($row = $result->fetchArray()) {
        return $row["user_ID"];
    }
    return false;
}

function userNameFromID ($user_Id) {
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    $statement = $db->prepare('SELECT * from  "tbl_user" WHERE user_ID = :user_Id;');
    $statement->bindValue(':user_Id', $user_Id);
    $result = $statement->execute();
    
    while ($row = $result->fetchArray()) {
        return $row["username"];
    }
    return "";
}

function getSession($user_Id) {
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    $statement = $db->prepare('SELECT * from  "tbl_auth" WHERE user_ID = :user_Id;');
    $statement->bindValue(':user_Id', $user_Id);
    $result = $statement->execute();
    
    while ($row = $result->fetchArray()) {
        return $row["auth_cookie"];
    }
    return "";
}

function deleteCurrentDBSession ($user_Id) {
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    $db->exec('BEGIN;');
    
    $db->query('DELETE FROM "tbl_auth" WHERE user_ID = ' . $user_Id . ';');

    $db->exec('COMMIT;');
}

function newSession($user_Id) {
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    if (getSession($user_Id) != "") {
        deleteCurrentDBSession($user_Id);
    }

    $cookie = bin2hex(random_bytes(32));
    
    $db->exec('BEGIN;');
    
    $db->query('INSERT INTO "tbl_auth" (user_ID, auth_cookie, expiry) VALUES (' . $user_Id . ', "' . $cookie . '", "2025-02-26 15:44:39");');

    $db->exec('COMMIT;');

    return $cookie;
}

function login($name, $pass) {
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    if (!checkCredentials($name, $pass)) {
        echo "Invalid credentials";
        return false;
    }

    if (checkCredentials($name, $pass)) {
        $user_Id = -1;
        $statement = $db->prepare('SELECT * from  "tbl_user" WHERE username = :name;');
        $statement->bindValue(':name', $name);
        $result = $statement->execute();
        
        while ($row = $result->fetchArray()) {
            $user_Id = $row["user_ID"];
        }
        $createdCookie = newSession($user_Id);
        setAuthCookie($createdCookie);
        return true;
    }
} ?>