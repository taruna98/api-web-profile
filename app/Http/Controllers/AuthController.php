<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

use function PHPUnit\Framework\returnSelf;

class AuthController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    // public function login(Request $request)
    // {
    //     $this->validate($request, [
    //         'eml'   => 'required|email',
    //         'pas'   => 'required|min:6',
    //         'sig'   => 'required'
    //     ]);

    //     $email      = $request->input('eml');
    //     $password   = $request->input('pas');
    //     $signature  = $request->input('sig');

    //     $user = User::where('eml', $email)->first();
    //     if (!$user) {
    //         return response()->json(['msg' => 'login failed'], 401);
    //     }

    //     if ($password !== $user->pas) {
    //         return response()->json(['msg' => 'login failed'], 401);
    //     }

    //     if ($user->sig !== $signature) {
    //         return response()->json(['msg' => 'login failed'], 403);
    //     }

    //     // $generateSignature = md5(substr($email, 0, 8) . $password);
    //     // $user->update([
    //     //     'sig' => $generateSignature
    //     // ]);

    //     return response($user, 200);
    // }

    public function login(Request $request)
    {
        $this->validate($request, [
            'eml'   => 'required|email',
            'pas'   => 'required|min:6'
        ]);

        $email      = $request->input('eml');
        $password   = $request->input('pas');

        $user = User::where('eml', $email)->first();

        // return Hash::make($password);

        if (Hash::check($password, $user->pas)) {
            $apiToken = bin2hex(random_bytes(20));

            $user->update([
                'tok' => $apiToken
            ]);

            return response()->json([
                'suc'   => true,
                'msg'   => 'login success',
                'dat'   => [
                    'usr' => $user,
                    'tok' => $apiToken
                ]
            ], 201);
        } else {
            return response()->json([
                'suc'   => false,
                'msg'   => 'login failed',
                'dat'   => ''
            ], 406);
        }
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'eml'   => 'required|email',
            'pas'   => 'required|min:6',
        ]);

        $email      = $request->input('eml');
        $password   = $request->input('pas');

        $user = User::where('eml', $email)->first();
        if ($user) {
            return response()->json(['msg' => 'register failed'], 401);
        }

        $generateSignature = md5(substr($email, 0, 8) . $password);

        $insert_user = User::insert([
            'eml'   => $email,
            'pas'   => $password,
            'sta'   => 1,
            'sig'   => $generateSignature
        ]);

        if (!$insert_user) {
            return response()->json(['msg' => 'register failed'], 401);
        }

        $dataload = [
            'eml'   => $email,
            'pas'   => $password,
            'sta'   => 1,
            'sig'   => $generateSignature
        ];

        return response($dataload, 200);
    }

    public function change_password(Request $request)
    {
        $this->validate($request, [
            'eml'   => 'required|email',
            'opa'   => 'required|min:6',
            'npa'   => 'required',
            'sig'   => 'required'
        ]);

        $email          = $request->input('eml');
        $old_password   = $request->input('opa');
        $new_password   = $request->input('npa');
        $signature      = $request->input('sig');

        $user = User::where('eml', $email)->first();
        if (!$user) {
            return response()->json(['msg' => 'change password failed'], 401);
        }

        if ($old_password !== $user->pas) {
            return response()->json(['msg' => 'change password failed'], 401);
        }

        if ($user->sig !== $signature) {
            return response()->json(['msg' => 'change password failed'], 403);
        }

        $generateSignature = md5(substr($email, 0, 8) . $new_password);
        $user->update([
            'pas' => $new_password,
            'sig' => $generateSignature
        ]);

        return response($user, 200);
    }
}
