<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Responses\ResponsesController;
use App\Models\User;

/**
 * @OA\Info(
 *     title="Validacion Valeria Barona",
 *     version="1.0",
 *     description="Creacion de usuarios - Documentación"
 * )
 *
 * @OA\Server(url="http://127.0.0.1:8000")
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/users/list",
     *     summary="Obtiene la lista de usuarios",
     *     tags={"Users"},
     *     @OA\Response(response="200", description="Lista de usuarios recuperada con éxito")
     * )
     */
    public function list()
    {
        $users = User::all();
        return ResponsesController::success($users, 'Usuarios recuperados con éxito');
    }

    /**
     * Almacenar un nuevo usuario
     *
     * @OA\Post(
     *     path="/users/store",
     *     summary="Crea un nuevo usuario",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Nombre del Usuario"),
     *             @OA\Property(property="email", type="string", format="email", example="usuario@example.com"),
     *             @OA\Property(property="password", type="string", example="contraseña123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario creado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="number", example=1),
     *             @OA\Property(property="name", type="string", example="Nombre del Usuario"),
     *             @OA\Property(property="email", type="string", example="usuario@example.com"),
     *             @OA\Property(property="created_at", type="string", example="2023-02-23T00:09:16.000000Z"),
     *             @OA\Property(property="updated_at", type="string", example="2023-02-23T00:09:16.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="UNPROCESSABLE ENTITY",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        return ResponsesController::success($user, 'Usuario creado correctamente');
    }


    /**
     * @OA\Get(
     *     path="/users/show/{id}",
     *     summary="Obtiene la información de un usuario",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Usuario recuperado con éxito"),
     *     @OA\Response(response="404", description="Usuario no encontrado")
     * )
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return ResponsesController::error([], 'Usuario no encontrado', 404);
        }

        return ResponsesController::success($user, 'Usuario recuperado con éxito');
    }

    /**
     * @OA\Put(
     *     path="/users/update/{id}",
     *     summary="Actualiza la información de un usuario",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", maximum=255),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", minimum=8),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Usuario actualizado con éxito"),
     *     @OA\Response(response="404", description="Usuario no encontrado")
     * )
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return ResponsesController::error([], 'Usuario no encontrado', 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
        ]);

        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->filled('password') ? bcrypt($request->input('password')) : $user->password,
        ]);

        return ResponsesController::success($user, 'Usuario actualizado con éxito');
    }

    /**
     * @OA\Delete(
     *     path="/users/destroy/{id}",
     *     summary="Elimina un usuario",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Usuario eliminado correctamente"),
     *     @OA\Response(response="404", description="Usuario no encontrado")
     * )
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return ResponsesController::error([], 'Usuario no encontrado', 404);
        }

        $user->delete();

        return ResponsesController::success([], 'Usuario eliminado correctamente');
    }
}
