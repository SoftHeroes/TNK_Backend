<?php
function getScriptAsSqlString($type, $name)
{
    $content = File::get(base_path() . "/sql/" . $type . "/" . $name . ".sql");

    $content = preg_replace('/^.+\n/', '', $content); // remove 1st line from string

    $content = str_replace('DELIMITER ;', '', $content); // remove DELIMITER
    $content = str_replace('DELIMITER;', '', $content); // remove DELIMITER

    $content = str_replace('$$', '', $content); // remove $$ from string

    return $content;
}
