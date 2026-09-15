<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna creado_en a la tabla herramientas.
     * Esta columna es necesaria para ordenar y filtrar por fecha de registro.
     */
    public function up(): void
    {
        Schema::table('herramientas', function (Blueprint $table) {
            if (!Schema::hasColumn('herramientas', 'creado_en')) {
                $table->timestamp('creado_en')->nullable()->after('metadata');
            }
        });

        // Actualizar registros existentes con la fecha actual
        \Illuminate\Support\Facades\DB::table('herramientas')
            ->whereNull('creado_en')
            ->update(['creado_en' => now()]);
    }

    public function down(): void
    {
        Schema::table('herramientas', function (Blueprint $table) {
            if (Schema::hasColumn('herramientas', 'creado_en')) {
                $table->dropColumn('creado_en');
            }
        });
    }
};
