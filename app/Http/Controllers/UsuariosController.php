<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UsuariosController extends Controller
{
    public function __construct()
    {
        // El middleware se encarga de la autenticación
        // Pero el rol de administrador lo verificamos aquí o en las rutas
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()->rol !== 'Administrador') {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para acceder a esta zona.');
        }

        $usuarios = Usuario::orderBy('id', 'desc')->get();

        return view('usuarios.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->rol !== 'Administrador') {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para acceder a esta zona.');
        }

        return view('usuarios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->rol !== 'Administrador') {
            return abort(403);
        }

        $request->validate([
            'nombre' => 'required|string|max:100',
            'usuario' => 'required|string|unique:usuarios,usuario|max:50|alpha_dash',
            'password' => [
                'required', 'string', 'min:8', 'max:72',
                'regex:/[A-Za-z]/',
                'regex:/[0-9]/',
                'regex:/[\!\@\#\$\%\^\&\*\(\)\-\_\=\+\[\]\{\}\|\;\:\'\"\,\.\<\>\?\/\\\~\`]/',
            ],
            'rol' => ['required', Rule::in(['Administrador', 'Almacenero', 'Supervisor'])],
        ], [
            'password.regex' => 'La contraseña debe incluir letras, números y al menos un carácter especial (!@#$...).',
            'usuario.unique' => 'El nombre de usuario ya está en uso.',
            'usuario.alpha_dash' => 'El usuario solo puede contener letras, números, guiones y guiones bajos.',
        ]);

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'usuario' => $request->usuario,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'creado_en' => now(),
        ]);

        Log::create([
            'usuario_id' => Auth::id(),
            'accion' => 'CREAR',
            'tabla' => 'usuarios',
            'item_id' => $usuario->id,
            'descripcion' => "Usuario registrado: {$usuario->usuario} ({$usuario->rol})",
            'fecha' => now()
        ]);

        return redirect()->route('usuarios.index')->with('creado', true);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (Auth::user()->rol !== 'Administrador') {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para acceder a esta zona.');
        }

        $usuario = Usuario::findOrFail($id);

        return view('usuarios.edit', compact('usuario'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->rol !== 'Administrador') {
            return abort(403);
        }

        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'usuario' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('usuarios', 'usuario')->ignore($id)],
            'password' => [
                'nullable', 'string', 'min:8', 'max:72',
                'regex:/[A-Za-z]/',
                'regex:/[0-9]/',
                'regex:/[\!\@\#\$\%\^\&\*\(\)\-\_\=\+\[\]\{\}\|\;\:\'\"\,\.\<\>\?\/\\\~\`]/',
            ],
            'rol' => ['required', Rule::in(['Administrador', 'Almacenero', 'Supervisor'])],
        ], [
            'password.regex' => 'La nueva contraseña debe incluir letras, números y al menos un carácter especial.',
            'usuario.unique' => 'El nombre de usuario ya está en uso por otro usuario.',
            'usuario.alpha_dash' => 'El usuario solo puede contener letras, números, guiones y guiones bajos.',
        ]);

        // Evitar que el Admin se quite el rol a sí mismo
        if ($usuario->id === Auth::id() && $request->rol !== 'Administrador') {
            return back()->with('error', 'No puedes cambiar tu propio rol de Administrador.');
        }

        // Evitar cambiar el rol de otro administrador (degradarlo)
        if ($usuario->id !== Auth::id() && $usuario->rol === 'Administrador') {
            return back()->with('error', 'No tienes permiso para editar o degradar a otro Administrador.');
        }

        $data = [
            'nombre' => $request->nombre,
            'usuario' => $request->usuario,
            'rol' => $request->rol,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        Log::create([
            'usuario_id' => Auth::id(),
            'accion' => 'EDITAR',
            'tabla' => 'usuarios',
            'item_id' => $usuario->id,
            'descripcion' => "Usuario actualizado: {$usuario->usuario}",
            'fecha' => now()
        ]);

        return redirect()->route('usuarios.index')->with('editado', true);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Solo administradores pueden eliminar usuarios
        if (Auth::user()->rol !== 'Administrador') {
            return abort(403);
        }

        $usuario = Usuario::findOrFail($id);

        // No puede eliminarse a sí mismo
        if ($usuario->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta mientras tienes la sesión activa.');
        }

        // Guardar datos antes de eliminar para el log
        $usuarioEliminadoNombre  = $usuario->nombre;
        $usuarioEliminadoUsuario = $usuario->usuario;
        $usuarioEliminadoRol     = $usuario->rol;

        $usuario->delete();

        // Registrar en el log: quién eliminó y a quién
        $ejecutorNombre  = Auth::user()->nombre;
        $ejecutorUsuario = Auth::user()->usuario;
        $ejecutorRol     = Auth::user()->rol;

        Log::create([
            'usuario_id' => Auth::id(),
            'accion'     => 'ELIMINAR',
            'tabla'      => 'usuarios',
            'item_id'    => $id,
            'descripcion' => "{$ejecutorRol} '{$ejecutorUsuario}' ({$ejecutorNombre}) eliminó al usuario '{$usuarioEliminadoUsuario}' ({$usuarioEliminadoNombre}) con rol {$usuarioEliminadoRol}.",
            'fecha'      => now()
        ]);

        return redirect()->route('usuarios.index')->with('eliminado', true);
    }
}
