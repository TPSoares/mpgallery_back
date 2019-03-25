<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Likes;
use App\Photos;
use Exception;
use Illuminate\Support\Facades\Auth;

class LikesController extends BaseController
{
    public function create($id) {

        $photo = Photos::find($id);

        if(!$photo) {
            return $this->sendError('Foto não encontrada', 404);
        }

        $data['user_id'] = Auth::id();
        $data['photo_id'] = $photo['id'];

        $like = Likes::where([
            ['user_id', Auth::id()],
            ['photo_id', $photo['id']]
        ])->get();

        if($like->count() > 0) {
            return;
        }

        try {
            $createdLike = Likes::create($data);
        } catch (Exception $e) {
            return $this->sendError('Erro ao criar like');
        }

        return $this->sendResponse($createdLike, 'Like criado!');
    }

    public function read($id) {
        $photo = Photos::find($id);

        if(!$photo) {
            return $this->sendError('Foto não encontrada', 404);
        }

        try {
            $likes = Likes::where('photo_id', $photo['id'])->get();
        } catch (Exception $e) {
            return $this->sendError('Erro ao retornar likes');
        }

        return $this->sendResponse($likes->count(), '');
    }

    public function delete($id) {
        $photo = Photos::find($id);

        if(!$photo) {
            return $this->sendError('Foto não encontrada', 404);
        }

        $like = Likes::where([
            ['user_id', Auth::id()],
            ['photo_id', $photo['id']]
        ])->get();

        if($like->count() === 0) {
            return;
        }

        try {
            $like->each->delete();
        
        } catch (Exception $e) {
            return $this->sendError('Erro ao retornar likes');
            // return $e->getMessage();
        }

        return $this->sendResponse($like, 'Like deletado');
    }

}
