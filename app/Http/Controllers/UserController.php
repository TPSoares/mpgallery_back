<?php

namespace App\Http\Controllers;
use App\Photos;
use App\User;
use Validator;
use Image;
use Exception;
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

    public function getUser($user_id) {
        $user = User::find($user_id);

        if(!$user) {
            return $this->sendError('Usuário não encontrado!', 404);
        }

        return $this->sendResponse($user, '');

    }

    public function setProfilePicture(Request $request) {
        $data = $request->all();
        
        $validate = Validator::make($data, [
            'image.*' => 'required|image|'
        ]);

        if($validate->fails()){
            return $this->sendError($validate->errors());
        }

        $user = User::find(Auth::id());
        $path = $this->imageStorage($request['image']);

        $user['profile_picture'] = $path;

        try {   
            $user->save();
            return $this->sendResponse($user, 'Foto salva!');
        } catch (Exception $e) {
            return $this->sendError('Erro ao atualizar foto!');
        }
    }

    private function imageStorage($image) {

        //gives a random name to it
        // $imgName = time() . '.' . $image->getClientOriginalExtension();
        $imgName = time() . '.' . 'jpg'; //temporary solution
        $localStoragePath = public_path('images');

        $img = Image::make($image);
        //treats the image and save it on the right path
        $img->encode('jpg', 75)->resize(500, 500)->save($localStoragePath . '/' . $imgName);

        //gets the path to save it
        if(env('APP_ENV') == 'local') {
            $path = public_path('images');
        } else {
            //path to url
            $path = env('APP_URL') . '/images';
            //AWS S3 save
            Storage::disk('s3')->put($imgName, $img);

            return Storage::disk('s3')->url($imgName);

        }

        return $path . '/' . $imgName;
    }

}
