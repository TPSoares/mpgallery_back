<?php

namespace App\Http\Controllers;
use App\Photos;
use App\User;
use App\Likes;
use App\Comments;
use Validator;
use Image;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class UserController extends BaseController 
{
    
    public function getUserPhotos() {
        $photos = Photos::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();

        
        if(!$photos) {
            return $this->sendError('Fotos não encontrada!', 404);
        }
        
        foreach($photos as $photo) {
            $likes = Likes::where('photo_id', $photo['id'])->get();
            foreach($likes as $like) {
                $user = User::where('id', $like['user_id'])->get();
                $like['user'] = $user;
            }
            $comments = Comments::where('photo_id', $photo['id'])->get();
            foreach($comments as $comment) {
                $user = User::where('id', $comment['user_id'])->get();
                $comment['user'] = $user;
            }

            $photo['likes'] = $likes;
            $photo['comments'] = $comments;

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

    public function update(Request $request) {
        $user = User::find(Auth::id());
        $data = $request->all();

        if(!$user) {
            return $this->sendError('Usuário não encontrado!', 404);
        }

        $validate = Validator::make($data, [
            'name' => 'required',
            'email' => 'required',
            'gender' => 'required',
            'age' => 'required'
        ]);

        if($validate->fails()){
            return $this->sendError($validate->errors());
        }

        $user['name'] = $data['name'];
        $user['email'] = $data['email'];
        $user['gender'] = $data['gender'];
        $user['age'] = $data['age'];

        try {   
            $user->save();
            return $this->sendResponse($user, 'Usuário atualizado!');
        } catch (Exception $e) {
            return $this->sendError('Erro ao atualizar usuário!');
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
