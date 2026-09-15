<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportesController extends Controller
{
    public function index()
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Supervisor'])) {
            abort(403);
        }
        return view('reportes.index');
    }

    public function usuarios()
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Supervisor'])) {
            abort(403);
        }

        $query = "SELECT t.id, t.dni, t.nombre, t.apellidos, 
                         COUNT(DISTINCT v.id) as total_vales,
                         SUM(vd.cantidad_prestada) as total_herramientas,
                         (SELECT SUM(monto_sancion) FROM incidencias WHERE trabajador_id = t.id) as total_sanciones
                  FROM trabajadores t
                  LEFT JOIN vales v ON t.id = v.trabajador_id
                  LEFT JOIN vale_detalles vd ON v.id = vd.vale_id
                  WHERE t.deleted_at IS NULL
                  GROUP BY t.id, t.dni, t.nombre, t.apellidos
                  ORDER BY total_herramientas DESC";
                  
        $reporte = DB::select($query);

        return view('reportes.usuarios', compact('reporte'));
    }

    public function personal()
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Supervisor'])) {
            abort(403);
        }

        $query = "SELECT u.id, u.nombre, u.usuario, u.rol,
                         (SELECT COUNT(*) FROM vales v WHERE v.usuario_id = u.id) as vales_registrados,
                         (SELECT SUM(vd.cantidad_prestada) FROM vales v JOIN vale_detalles vd ON v.id = vd.vale_id WHERE v.usuario_id = u.id) as herramientas_procesadas
                  FROM usuarios u
                  ORDER BY vales_registrados DESC";
                  
        $reporte = DB::select($query);

        return view('reportes.personal', compact('reporte'));
    }

    public function herramientas()
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Supervisor'])) {
            abort(403);
        }

        $query = "SELECT h.id, h.codigo, h.nombre, h.stock_total, h.stock_disponible,
                         (SELECT SUM(vd.cantidad_prestada) FROM vale_detalles vd WHERE vd.herramienta_id = h.id) as total_prestado,
                         (SELECT COUNT(*) FROM vale_detalles vd WHERE vd.herramienta_id = h.id) as veces_prestada,
                         (SELECT COUNT(*) FROM incidencias i WHERE i.herramienta_id = h.id) as total_incidencias,
                         (SELECT COUNT(*) FROM incidencias i WHERE i.herramienta_id = h.id AND i.tipo = 'Mantenimiento') as mantenimientos_realizados,
                         (SELECT SUM(i.monto_sancion) FROM incidencias i WHERE i.herramienta_id = h.id) as costo_mantenimiento
                  FROM herramientas h
                  WHERE h.deleted_at IS NULL
                  ORDER BY veces_prestada DESC";
                  
        $reporte = DB::select($query);

        return view('reportes.herramientas', compact('reporte'));
    }

    public function temporal(Request $request)
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Supervisor'])) {
            abort(403);
        }

        $inicio = $request->input('inicio', date('Y-m-01'));
        $fin = $request->input('fin', date('Y-m-d'));
        
        if ($request->has('periodo')) {
            switch ($request->input('periodo')) {
                case 'semana':
                    $inicio = date('Y-m-d', strtotime('-7 days'));
                    break;
                case 'mes':
                    $inicio = date('Y-m-01');
                    break;
            }
            $fin = date('Y-m-d');
        }

        if ($request->has('dias')) {
            $dias = (int)$request->input('dias');
            if ($dias > 0) {
                $inicio = date('Y-m-d', strtotime("-{$dias} days"));
                $fin = date('Y-m-d');
            }
        }

        $query = "SELECT v.id, v.codigo_vale, v.fecha_creacion, v.estado, t.nombre as trabajador_nombre, t.apellidos as trabajador_apellidos,
                         SUM(vd.cantidad_prestada) as items
                  FROM vales v
                  JOIN trabajadores t ON v.trabajador_id = t.id
                  JOIN vale_detalles vd ON v.id = vd.vale_id
                  WHERE DATE(v.fecha_creacion) BETWEEN :inicio AND :fin
                  GROUP BY v.id, v.codigo_vale, v.fecha_creacion, v.estado, t.nombre, t.apellidos
                  ORDER BY v.fecha_creacion DESC";
                  
        $reporte = DB::select($query, ['inicio' => $inicio, 'fin' => $fin]);

        return view('reportes.temporal', compact('reporte', 'inicio', 'fin'));
    }

    public function exportBugWord()
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Supervisor'])) {
            abort(403);
        }
        $headers = array(
            "Content-type" => "application/vnd.ms-word",
            "Content-Disposition" => "attachment;Filename=Reporte_Bug_QA.doc"
        );
        return response()->view('reports.bug_report_word')->withHeaders($headers);
    }

    public function exportMatrizWord()
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Supervisor'])) {
            abort(403);
        }
        $headers = array(
            "Content-type" => "application/vnd.ms-word",
            "Content-Disposition" => "attachment;Filename=Matriz_Dispositivos_QA.doc"
        );
        return response()->view('reports.matriz_dispositivos_word')->withHeaders($headers);
    }
}
