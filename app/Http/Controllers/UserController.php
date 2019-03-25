<?php

namespace App\Http\Controllers;
use App\Photos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    
    public function getUserPhotos() {
        $photos = Photos::where('user_id', Auth::id())->get();

        if(!$photos) {
            return $this->sendError('Fotos não encontrada!', 404);
        }

        return $this->sendResponse($photos, '');
    }
}
