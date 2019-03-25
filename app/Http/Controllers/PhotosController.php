<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Image;
use Exception;
use App\Photos;
use Illuminate\Support\Facades\Auth;

class PhotosController extends BaseController
{
    public function create(Request $request) {
        $data = $request->all();

        $validate = Validator::make($data, [
            'title' => 'required',
            'image' => 'required|image|'
        ]);

        if($validate->fails()){
            return $this->sendError($validate->errors());
        }

        //gets the file 
        $image = $request->file('image');
        //gives a random name to it
        $imgName = time() . '.' . $image->getClientOriginalExtension();
        //gets the path to save it
        if(env('APP_ENV') == 'local') {
            $path = public_path('images');
        } else {
            $path = app('APP_URL') . '/images';
        }
        $img = Image::make($image);
        //treats the image and save it on the right path
        $img->encode('jpg', 75)->resize(500, 500)->save($path . '/' . $imgName);

        $data['path'] = $path . '/' . $imgName;
       
        $data['user_id'] = Auth::id();
        
        try {
            $photo = Photos::create($data);
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

    public function readAll() {
        $photos = Photos::all();

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
}
