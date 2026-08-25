<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Enforce append-only audit_logs at the database layer.
     *
     * The AuditLog model already rejects update/delete via model events, but a
     * DB superuser (or a compromised connection) could still mutate rows with
     * raw SQL. These BEFORE triggers make the table immutable for every
     * non-privileged connection, providing defense-in-depth for the financial
     * audit trail required by the platform.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS rihal_audit_logs_no_update');
        DB::statement('DROP TRIGGER IF EXISTS rihal_audit_logs_no_delete');

        DB::statement(<<<'SQL'
            CREATE TRIGGER rihal_audit_logs_no_update
            BEFORE UPDATE ON audit_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'audit_logs is append-only and cannot be updated';
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER rihal_audit_logs_no_delete
            BEFORE DELETE ON audit_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'audit_logs is append-only and cannot be deleted';
            END
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS rihal_audit_logs_no_update');
        DB::statement('DROP TRIGGER IF EXISTS rihal_audit_logs_no_delete');
    }
};
