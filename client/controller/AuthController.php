<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\InvalidArgumentException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginWithOauth2()
    {
        session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => config('services.oauth2_server.client_id'),
            'redirect_uri' => 'http://client.test/oauth/callback',
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        return redirect('http://auth-server.test/oauth/authorize?' . $query);
    }

    public function oauthCallback(Request $request)
    {
        $state = session()->pull('state');

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class,
            'Invalid state value.'
        );

        $response = Http::asForm()->post('http://auth-server.test/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.oauth2_server.client_id'),
            'client_secret' => config('services.oauth2_server.client_secret'),
            'redirect_uri' => 'http://client.test/oauth/callback',
            'code' => $request->code,
        ]);

        $tokenData = $response->json();
        $user = $this->getUser($response->json());

      //  session()->put('user', $user);
       // session()->put('access_token', $tokenData['access_token']);
        //session()->put('refresh_token', $tokenData['refresh_token']);

        Auth::loginUsingId($user['id']);
        dd($user->json(), $response->json());
        //return redirect('http://client.test/dashboard');
    }
    private function getUser($json_response)
    {
        // Verifica si la clave 'access_token' existe en el array
        if (array_key_exists('access_token', $json_response)) {
            // Obtiene el valor de 'access_token' del array
            $token = $json_response['access_token'];

            // Hace la solicitud HTTP con el token de acceso
            return Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer $token",
            ])->get('http://auth-server.test/api/user');
        } else {
            // Si 'access_token' no está presente en la respuesta, devuelve un error o realiza alguna otra acción apropiada
            // Por ejemplo:
            throw new \RuntimeException('El token de acceso no está presente en la respuesta del servidor.');
        }
    }
}
