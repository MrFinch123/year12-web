<!DOCTYPE html>
<html>
<body>
 
<?php
echo "My first PHP script!";
$db = new SQLite3('db.db');

$results = $db->query('SELECT * FROM hello');
while ($row = $results->fetchArray()) {
    echo "<br />";
    echo "<span style=\"color:blue;\">" . $row['name'] . " costs: £" . $row['item_id'] ." </span>";
}

?>

</body>
</html>