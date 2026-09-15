<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            if (!Schema::hasColumn('incidencias', 'tipo_falta')) {
                $table->string('tipo_falta', 100)->nullable()->after('reparado');
            }
            if (!Schema::hasColumn('incidencias', 'gravedad')) {
                $table->string('gravedad', 50)->nullable()->after('tipo_falta');
            }
            if (!Schema::hasColumn('incidencias', 'accion_correctiva')) {
                $table->string('accion_correctiva', 255)->nullable()->after('gravedad');
            }
            if (!Schema::hasColumn('incidencias', 'evidencia')) {
                $table->string('evidencia', 255)->nullable()->after('accion_correctiva');
            }
            if (!Schema::hasColumn('incidencias', 'estado_disciplinario')) {
                $table->string('estado_disciplinario', 50)->default('Activo')->after('evidencia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $cols = ['tipo_falta', 'gravedad', 'accion_correctiva', 'evidencia', 'estado_disciplinario'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('incidencias', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
