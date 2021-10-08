<?php

namespace App\Controllers\Auth;

use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\Session;

class LoginController extends Controller
{
    public function index()
    {
        return $this->view('auth/login', 'Oturum Aç');
    }

    public function login(Request $request)
    {
        $email = $request->getBody()["email"];
        $password = $request->getBody()["password"];

        $user = User::where('email', $email)[0];

        if (validate_input() === true) {
            if (empty($user)) {
                Session::add('error', ["Bu e‑posta adresi ile bağlantılı bir hesap bulunamadı."]);
                redirect('/login');
            }

            if (!password_verify($password, $user['password'])){
                Session::add('error', ["Parola yanlış."]);
                redirect('/login');
            }
        }

        Session::add('user', $user['user_id']);
        redirect('/');
    }

    public function logout() {
        Session::close();
        redirect('/');
        exit();
    }
}