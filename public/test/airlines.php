<?php
$query = <<<eof
    LOAD DATA INFILE 'airline_list_designator.csv'
     INTO TABLE tableName
     FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
     LINES TERMINATED BY '\n'
    (designator,name)
eof;

$db->query($query);
?>