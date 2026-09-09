<?php
// Recreate the audit_logs append-only triggers on a given database.
// Usage: php scripts/ensure-audit-triggers.php [db-name]
// Writes SQL to a temp file and pipes it through mysql.exe (reliable for BEGIN..END blocks).

$db = $argv[1] ?? 'rihal_next';
$mysqlBin = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysql.exe';
$host = '127.0.0.1';
$user = 'root';

$sql = <<<SQL
DELIMITER //
DROP TRIGGER IF EXISTS rihal_audit_logs_no_update//
DROP TRIGGER IF EXISTS rihal_audit_logs_no_delete//
CREATE TRIGGER rihal_audit_logs_no_update
BEFORE UPDATE ON audit_logs
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs is append-only and cannot be updated';
END //
CREATE TRIGGER rihal_audit_logs_no_delete
BEFORE DELETE ON audit_logs
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs is append-only and cannot be deleted';
END //
DELIMITER ;
SQL;

$tmp = sys_get_temp_dir() . '\\audit-triggers-' . $db . '.sql';
file_put_contents($tmp, $sql);

function countTriggers(string $mysqlBin, string $host, string $user, string $db): string {
    $cmd = '"' . $mysqlBin . '" -h ' . $host . ' -u ' . $user . ' ' . $db . ' -N -e "SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE EVENT_OBJECT_TABLE=\'audit_logs\' AND TRIGGER_NAME LIKE \'rihal_audit_logs_no%\';"';
    exec($cmd, $out, $code);
    return trim(implode('', $out));
}

// The mysqldump replay can leave duplicate trigger rows; DROP repeatedly to clear them,
// then (re)create exactly two.
for ($i = 0; $i < 6; $i++) {
    $cmd = '"' . $mysqlBin . '" -h ' . $host . ' -u ' . $user . ' ' . $db . ' < "' . $tmp . '"';
    exec($cmd, $output, $code);
    if (countTriggers($mysqlBin, $host, $user, $db) === '2') {
        break;
    }
    // If duplicates remain, drop them explicitly and retry.
    $drop = 'DROP TRIGGER IF EXISTS rihal_audit_logs_no_update; DROP TRIGGER IF EXISTS rihal_audit_logs_no_delete;';
    $dropCmd = '"' . $mysqlBin . '" -h ' . $host . ' -u ' . $user . ' ' . $db . ' -e ' . escapeshellarg($drop);
    exec($dropCmd, $dropOut, $dropCode);
}
@unlink($tmp);

$count = countTriggers($mysqlBin, $host, $user, $db);
echo "audit_logs triggers on $db: $count\n";

if ($count !== '2') {
    fwrite(STDERR, "Expected 2 triggers, found $count\n");
    exit(1);
}
echo "OK\n";
