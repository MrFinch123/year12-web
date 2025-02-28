<?php

function userExists(string $name)
/**
     * Checks if a user exists in the database.
     *
     * @param string $name The username to check for existence.
     * @return bool Returns true if the user exists, false otherwise.
     */
{
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    $statement = $db->prepare('SELECT username from  "tbl_user" WHERE username = :name;');
    $statement->bindValue(':name', $name);
    $result = $statement->execute();

    while ($row = $result->fetchArray()) {
        return true;
    }
    return false;
}

function checkCredentials(string $name, string $pass)
/**
 * Checks if the provided credentials are valid.
 *
 * This function connects to an SQLite3 database and verifies if the given
 * username and password match an entry in the "tbl_user" table.
 *
 * @param string $name The username to check.
 * @param string $pass The password to check.
 * @return bool Returns true if the credentials are valid, false otherwise.
 */
{
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

function registerUser(string $name, string $pass): bool
/**
 * Registers a new user in the database.
 *
 * @param string $name The username of the new user.
 * @param string $pass The password of the new user.
 * @return bool Returns true on successful registration, false if the user already exists.
 */
{
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    if (userExists($name)) {
        return false; // User already exists
    }

    $db->exec('BEGIN;');
    
    $statement = $db->prepare('INSERT INTO "tbl_user" (username, password) VALUES (:name, :pass);');
    $statement->bindValue(':name', $name);
    $statement->bindValue(':pass', $pass);
    $statement->execute();
    
    $db->exec('COMMIT;');

    return true;
}

function getCookie(): string
/**
 * Retrieves the value of the "session" cookie if it exists.
 *
 * @return string The value of the "session" cookie, or an empty string if the cookie is not set.
 */
{
    if (isset($_COOKIE["session"])) {
        return $_COOKIE["session"];
    }
    return "";
}

function clearAuthCookie(): void
/**
 * Clears the "session" cookie.
 */
{
    setcookie("session", "", time() - 3600, "/");
}

function setAuthCookie(string $cookie, DateTime $expiry): void
/**
 * Sets the "session" cookie to the provided value.
 *
 * @param string $cookie The value to set the "session" cookie to.
 */
{
    setcookie("session", $cookie, $expiry->getTimestamp(), "/");
}

/**
 * Checks if the provided cookie exists in the database and returns the associated user ID.
 *
 * @param string $cookie The authentication cookie to check.
 * @return integer The user ID if the cookie is found, or -1 if the cookie is not found.
 */
function checkCookie(string $cookie): int
{
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    $statement = $db->prepare('SELECT * from  "tbl_auth" WHERE auth_cookie = :cookie;');
    $statement->bindValue(':cookie', $cookie);
    $result = $statement->execute();

    while ($row = $result->fetchArray()) {

        $expiry = new DateTime($row["expiry"]);
        $now = new DateTime();
        if ($now < $expiry) {
            // echo "Session valid";
            return $row["user_ID"];
        } else {
            // echo "Session expired";
            $db->close();
            deleteCurrentDBSession($row["user_ID"]);
            clearAuthCookie();
        }
    }
    return -1;

}

/**
 * Retrieves user information from the database based on the user ID.
 *
 * @param int $user_Id The ID of the user to retrieve.
 * @return array The user information as an associative array.
 * @throws Exception If there is an issue with the database connection or query execution.
 */
function userFromID(int $user_Id): array
{
    if ($user_Id == -1) {
        return ["username" => ""];
    }
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    $statement = $db->prepare('SELECT * from  "tbl_user" WHERE user_ID = :user_Id;');
    $statement->bindValue(':user_Id', $user_Id);
    $result = $statement->execute();

    while ($row = $result->fetchArray()) {
        return $row;
    }
    return "";
}

/**
 * Retrieves the session authentication cookie from the database for a given user ID.
 *
 * @param int $user_Id The ID of the user whose session authentication cookie is to be retrieved.
 * @return string The authentication cookie associated with the user ID, or an empty string if not found.
 */
function getSessionFromDBByUserID(int $user_Id): string
{
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    $statement = $db->prepare('SELECT * from  "tbl_auth" WHERE user_ID = :user_Id;');
    $statement->bindValue(':user_Id', $user_Id);
    $result = $statement->execute();

    while ($row = $result->fetchArray()) {
        return $row["auth_cookie"];
    }
    return "";
}
/**
 * Deletes the current database session for a given user.
 *
 * This function connects to the SQLite database and deletes the session
 * information for the specified user ID from the "tbl_auth" table.
 *
 * @param int $user_Id The ID of the user whose session is to be deleted.
 *
 * @return bool Returns true if the session was successfully deleted, false otherwise.
 */
function deleteCurrentDBSession($user_Id): bool
{
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    echo "Session deleted";
    $db->exec('BEGIN;');

    $result = $db->query('DELETE FROM "tbl_auth" WHERE user_ID = ' . $user_Id . ';');

    if ($result) {
        $db->exec('COMMIT;');
        return true;
    } else {
        $db->exec('ROLLBACK;');
        return false;
    }
}

/**
 * Creates a new session for the given user ID.
 *
 * This function generates a new session for the specified user by creating a new
 * authentication cookie and storing it in the database. If a session already exists
 * for the user, it will be deleted before creating a new one.
 *
 * @param int $user_Id The ID of the user for whom the session is being created.
 * @return string The generated authentication cookie.
 *
 * @throws Exception If there is an error generating the random bytes for the cookie.
 */
function newSession(int $user_Id): string
{
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    if (getSessionFromDBByUserID($user_Id) != "") {
        deleteCurrentDBSession($user_Id);
    }

    $cookie = bin2hex(random_bytes(32));
    $expiry = new DateTime();
    $expiry->modify('+1 week');

    $db->exec('BEGIN;');

    $db->query('INSERT INTO "tbl_auth" (user_ID, auth_cookie, expiry) VALUES (' . $user_Id . ', "' . $cookie . '", "' . $expiry->format("Y-m-d H:i:s") .'");');

    $db->exec('COMMIT;');

    setAuthCookie($cookie, $expiry);

    return $cookie;
}

/**
 * Uses the session cookie from the browser to retrieve the user array. If it is invalid or expired, returns an empty array.
 */
function getSessionFromBrowser() {};

/**
 * Logs in a user by verifying their credentials and creating a session.
 *
 * @param string $name The username of the user attempting to log in.
 * @param string $pass The password of the user attempting to log in.
 * @return bool Returns true if login is successful, false otherwise.
 */
function login(string $name,string $pass): bool
{
    $db = new SQLite3('../db.db', SQLITE3_OPEN_READWRITE);

    if (!checkCredentials($name, $pass)) {
        print("Invalid credentials for " . $name);
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
        return true;
    }
}
