<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

use Hash;
use Auth;
use App\Models\User;
use App\Models\UserAccessToken;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Responses\ResponsesController;


class LoginController extends Controller
{
    use Notifiable;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['do_login', 'do_register']]);
    }

        /**
     * Realiza el registro de un nuevo usuario.
     *
     * @OA\Post(
     *     path="/auth/do_register",
     *     summary="Registro de usuario",
     *     tags={"Authentication"},
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
     *         description="Usuario registrado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario registrado correctamente"),
     *             @OA\Property(property="user", type="object", example={"id": 1, "name": "Nombre del Usuario", "email": "usuario@example.com", "created_at": "2023-02-23T00:09:16.000000Z", "updated_at": "2023-02-23T00:09:16.000000Z"})
     *         )
     *     ),
     *     @OA\Response(response="422", description="Datos de registro inválidos"),
     * )
     */
    public function do_register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        return response()->json(['message' => 'Usuario registrado correctamente', 'user' => $user], 200);
    }

    /**
     * Realiza la autenticación del usuario.
     *
     * @OA\Post(
     *     path="/auth/do_login",
     *     summary="Iniciar sesión",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="string", example="usuario@example.com"),
     *             @OA\Property(property="pwd", type="string", example="contraseña123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="result", type="string", example="success"),
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(property="token", type="string", example="jwt_token_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Credenciales inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object", example={"result": "error", "error_id": 422, "error_msg": "Algunos de los datos introducidos no son correctos"})
     *         )
     *     )
     * )
     */
    public function do_login(Request $request)
    {
        $user = User::where('email', $request->user)->first();
        if (!$user || !Hash::check($request->input('pwd'), $user->password)) {
            return response()->json([
                'data' => [
                    'result' => 'error',
                    'error_id' => 422,
                    'error_msg' => 'Algunos de los datos introducidos no son correctos',
                ],
            ], 422);
        }
    
        try {
            // Verificar si el usuario ya tiene un token asignado
            $existingToken = UserAccessToken::where('user_id', $user->id)
                ->where('ip_address', $request->ip())
                ->where('user_agent', $request->header('User-Agent'))
                ->first();
    
            // Si el token existe y aún no ha expirado, devolverlo
            if ($existingToken && !$this->isTokenExpired($existingToken->access_token)) {
                return response()->json([
                    'data' => [
                        'result' => 'success',
                        'status' => 'ok',
                        'token' => $existingToken->access_token,
                    ],
                ]);
            }
    
            // Generar un nuevo token
            $token = JWTAuth::claims(['email' => $user->email, 'name' => $user->name, 'id' => $user->id, ''])
                ->attempt(['email' => $request->user, 'password' => $request->input('pwd')]);
    
            if (!$token) {
                return response()->json([
                    'data' => [
                        'result' => 'error',
                        'error_id' => 422,
                        'error_msg' => 'Algunos de los datos introducidos no son correctos',
                    ],
                ], 422);
            }
    
            // Actualizar o crear la instancia de UserAccessToken
            if ($existingToken) {
                $existingToken->update(['access_token' => $token]);
            } else {
                UserAccessToken::create([
                    'user_id' => $user->id,
                    'access_token' => $token,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'device_type' => $this->getDeviceType(),
                ]);
            }
    
            // Devuelve el token al cliente
            return response()->json([
                'data' => [
                    'result' => 'success',
                    'status' => 'ok',
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            info('Error al crear el token: ' . $e->getMessage());
    
            // Devolver un error en caso de excepción
            return response()->json([
                'data' => [
                    'result' => 'error',
                    'error_id' => 500,
                    'error_msg' => 'Error al crear el token: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    private function generateToken($user, $request)
    {
        $existingToken = UserAccessToken::where('user_id', $user->id)
            ->where('ip_address', $request->ip())
            ->where('user_agent', $request->header('User-Agent'))
            ->first();

        if ($existingToken && !$this->isTokenExpired($existingToken->access_token)) {
            return $existingToken->access_token;
        }

        $token = JWTAuth::claims(['email' => $user->email, 'name' => $user->name])
            ->attempt(['email' => $request->user, 'password' => $request->input('pwd')]);

        if (!$token) {
            return response()->json([
                'data' => [
                    'result' => 'error',
                    'error_id' => 422,
                    'error_msg' => 'Algunos de los datos introducidos no son correctos',
                ],
            ], 422);
        }

        if ($existingToken) {
            $existingToken->update(['access_token' => $token]);
        } else {
            UserAccessToken::create([
                'user_id' => $user->id,
                'access_token' => $token,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'device_type' => $this->getDeviceType(),
            ]);
        }

        return $token;
    }

    private function isTokenExpired($token)
    {
        try {
            $decoded = JWTAuth::setToken($token)->getPayload();
            $exp = $decoded['exp'];
            return now()->timestamp >= $exp;
        } catch (\Exception $e) {
            return true;
        }
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function getDeviceType()
    {   
        $userAgent = request()->header('User-Agent');

        if (stripos($userAgent, 'android') !== false) {
            return 'mobile';
        } elseif (stripos($userAgent, 'iphone') !== false) {
            return 'mobile';
        } elseif (stripos($userAgent, 'ipad') !== false) {
            return 'tablet';
        } elseif (stripos($userAgent, 'windows phone') !== false) {
            return 'mobile';
        } elseif (stripos($userAgent, 'macintosh') !== false && stripos($userAgent, 'ipad') === false) {
            return 'desktop';
        } elseif (stripos($userAgent, 'windows') !== false) {
            return 'desktop';
        } elseif (stripos($userAgent, 'linux') !== false) {
            return 'desktop';
        } else {
            return 'unknown';
        }
    }
 
    public function logout(Request $request)
    {
        $accessToken = $request->bearerToken();
        $tokenData = UserAccessToken::where('access_token', $accessToken)->first();
        if (!$tokenData) {
            return ResponsesController::error([], 'Unauthorized', 401);
        }
        
        $user = auth()->user();
        $token = JWTAuth::getToken();
        $accessToken = $user->accessTokens->where('access_token', $token)->first();

        if ($accessToken) {
            $accessToken->delete();
        }
        JWTAuth::invalidate($token);

        Auth::logout();
        return response()->json(['message' => 'Sesion Cerrada - Correctamente', 'status' => 'OK', 'code' => 200]);
    }


    
}