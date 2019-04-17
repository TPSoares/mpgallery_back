<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Validator;
use App\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    public function signup(Request $request) {

        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'name' => 'required',
            'gender' => 'string',
        ]);
        
        if($validate->fails()){
            return $this->sendError($validate->errors());
        }

        $request['password'] = bcrypt($request['password']);
        
        try {
            $user = User::create($request->all());
            $user['token'] = $user->createToken('signup')->accessToken;
        } catch (Exception $e) {
            // return $e->get();
            return $this->sendError("Erro ao cadastrar", 500);
        }

        return $this->sendResponse($user, 'Usuário cadastrado!');

    }

    public function signin(Request $request) {
        $credentials = $request->only('email','password');


        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if($validate->fails()){
            return $this->sendError($validate->errors());
        }

        if(Auth::attempt($credentials)) { 
            $user = $request->user();
            $user['token'] = $user->createToken('signin')->accessToken;
            
        } else {
            return $this->sendError('Credenciais inválidas!', 401);    
        }
        return $this->sendResponse($user, 'Usuário logado!');

    }
}
