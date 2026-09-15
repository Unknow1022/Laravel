<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Usuario;
use App\Services\CortexAutomationService;

class CortexAutomationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test the real optimize_db functionality.
     */
    public function test_optimize_db_executes_successfully()
    {
        $admin = Usuario::factory()->create(['rol' => 'Administrador']);

        $service = new CortexAutomationService();
        $message = $service->executeAuthorizedRepair('optimize_db');

        $this->assertStringContainsString('Optimización de base de datos finalizada', $message);
        $this->assertStringContainsString('Tablas desfragmentadas', $message);
    }

    /**
     * Test the real fix_permissions functionality.
     */
    public function test_fix_permissions_executes_successfully()
    {
        $admin = Usuario::factory()->create(['rol' => 'Administrador']);

        $service = new CortexAutomationService();
        $message = $service->executeAuthorizedRepair('fix_permissions');

        $this->assertStringContainsString('Permisos de sistema restaurados en directorios críticos', $message);
        $this->assertStringContainsString('procesados', $message);
    }

    /**
     * Test execution of repair endpoint by admin.
     */
    public function test_admin_can_trigger_repair_endpoints()
    {
        $admin = Usuario::factory()->create(['rol' => 'Administrador']);

        $response = $this->actingAs($admin)->post('/cortex/repair/optimize_db');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /**
     * Test non-admin cannot trigger repair endpoints.
     */
    public function test_non_admin_cannot_trigger_repair_endpoints()
    {
        $user = Usuario::factory()->create(['rol' => 'Almacenero']);

        $response = $this->actingAs($user)->post('/cortex/repair/optimize_db');

        $response->assertStatus(403);
    }

    /**
     * Test that running scan logs the diagnostic event in db and redirects.
     */
    public function test_run_scan_logs_diagnostic_event_and_redirects()
    {
        $user = Usuario::factory()->create();
        
        $response = $this->actingAs($user)->get('/cortex/scan');
        
        $response->assertRedirect(route('cortex.index'));
        $response->assertSessionHas('scan_completed', true);
        
        // Assert that a log was recorded in cortex_events
        $this->assertDatabaseHas('cortex_events', [
            'type' => 'DIAGNOSTIC',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test that solve_all can be triggered by admin.
     */
    public function test_admin_can_trigger_solve_all()
    {
        $admin = Usuario::factory()->create(['rol' => 'Administrador']);

        $response = $this->actingAs($admin)->post('/cortex/solve-all');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $this->assertTrue(session()->has('cortex_all_solved'));
    }

    /**
     * Test export audit report successfully.
     */
    public function test_admin_can_export_audit_report()
    {
        $admin = Usuario::factory()->create(['rol' => 'Administrador']);

        $response = $this->actingAs($admin)->get('/cortex/export-audit');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test export audit report when system is solved.
     */
    public function test_admin_can_export_audit_report_when_solved()
    {
        $admin = Usuario::factory()->create(['rol' => 'Administrador']);

        $response = $this->actingAs($admin)
            ->withSession(['cortex_all_solved' => true])
            ->get('/cortex/export-audit');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
