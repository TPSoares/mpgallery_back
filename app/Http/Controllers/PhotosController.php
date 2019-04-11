<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Image;
use Exception;
use App\Photos;
use App\User;
use App\Comments;
use App\Likes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PhotosController extends BaseController
{
    public function create(Request $request) {
        $data = $request->all();
        
        $validate = Validator::make($data, [
            'title' => 'required',
            'image.*' => 'required|image|'
        ]);

        if($validate->fails()){
            return $this->sendError($validate->errors());
        }

        $user = User::find(Auth::id());
        
        $data['path'] = $this->imageStorage($request['image']);
        $data['user_id'] = Auth::id();
        // $data['user'] = $user;

        // return $data;
        
        try {
            $photo = Photos::create($data);
            $photo['user'] = $user;
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $this->sendResponse($photo,'Foto registrada com sucesso!');


    }
    public function read($id) {

        $photo = Photos::find($id);

        if(!$photo) {
            return $this->sendError('Foto não encontrada!', 404);
        }

        return $this->sendResponse($photo, '');
    }

    public function readAll($offset) {
        $photos = Photos::offset($offset)->limit(5)->orderBy('created_at', 'desc')->get();

        //find each user of each photo
        // foreach ($photos as $photo) {
        //     $user = User::find($photo['user_id']);
        //     $comments = Comments::where('photo_id', $photo['id'])->offset(0)->limit(3)->orderBy('created_at', 'desc')->get();
        //     foreach($comments as $comment) {
        //         $comment_user = User::find($comment['user_id']);
        //         $comment['user'] = $comment_user;
        //     }
        //     $likes = Likes::where('photo_id', $photo['id'])->get();
        //     $photo['user'] = $user;
        //     $photo['comments'] = $comments;
        //     $photo['likes'] = $likes;
        // }

        if(!$photos) {
            return $this->sendError('Fotos não encontrada!', 404);
        }

        return $this->sendResponse($photos, '');
    }
    public function update(Request $request, $id) {

        $photo = Photos::find($id);
        $data = $request->all();

        if(!$photo) {
            return $this->sendError('Foto não encontrada!', 404);
        }

        $validate = Validator::make($data, [
            'title' => 'required',
        ]);

        if($validate->fails()){
            return $this->sendError($validate->errors());
        }

        //checks if the photo belongs to the currently authenticated user
        if($photo['user_id'] !== Auth::id()) {
            return $this->sendError('Você não tem autorização para alterar esta foto!', 403);
        }

        $photo['title'] = $data['title'];
        $photo['description'] = $data['description'];   

        try {   
            $photo->save();
            return $this->sendResponse($photo, 'Foto atualizada!');
        } catch (Exception $e) {
            return $this->sendError('Erro ao atualizar foto!');
        }

    }
    public function delete($id) {
        $photo = Photos::find($id);

        if(!$photo) {
            return $this->sendError('Foto não encontrada!', 404);
        }

        if($photo['user_id'] !== Auth::id()) {
            return $this->sendError('Você não tem autorização para alterar esta foto!', 403);
        }

        try {   
            $photo->delete();
            return $this->sendResponse($photo, 'Foto deletada!');
        } catch (Exception $e) {
            return $this->sendError('Erro ao deletar foto!');
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
