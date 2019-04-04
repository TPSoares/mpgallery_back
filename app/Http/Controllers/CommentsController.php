<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Photos;
use App\Comments;
use App\User;
use Exception;
use Validator;
use Illuminate\Support\Facades\Auth;

class CommentsController extends BaseController
{
    public function create(Request $request, $id) {
        $photo = Photos::find($id);
        $comment = $request->only(['comment']);

        if(!$photo) {
            return $this->sendError('Foto não encontrada!', 404);
        }

        $validate = Validator::make($comment, [
            'comment' => 'required'
        ]);

        if($validate->fails()) {
            return $this->sendError($validate->errors());
        }

        $data['user_id'] = Auth::id();
        $data['photo_id'] = $photo['id'];
        $data['comment'] = $request['comment'];

        try {
            $createdComment = Comments::create($data);
        } catch (Exception $e) {
            return $this->sendError('Erro ao criar comentário!');
            // return $e->getMessage();
        }

        return $this->sendResponse($createdComment, 'Comentário criado!');
    }

    public function read($id, $offset, $limit) {
        $photo = Photos::find($id);

        if(!$photo) {
            return $this->sendError('Foto não encontrada!', 404);
        }

        try {
            $comments = Comments::where('photo_id', $photo['id'])
            ->offset($offset)
            ->limit($limit)
            ->orderBy('created_at', 'desc')
            ->get();
            // $comments->withPath('commentpage');
            foreach($comments as $comment) {
                $comment_user = User::find($comment['user_id']);
                $comment['user'] = $comment_user;
            }
        } catch (Exception $e) {
            return $this->sendError('Erro ao retornar comentários!');
            // return $e->getMessage();
        }

        return $this->sendResponse($comments, '');
    }

    public function update(Request $request, $id, $commentId) {
        $photo = Photos::find($id);
        $comment = Comments::find($commentId);
 
        if(!$photo) {
            return $this->sendError('Foto não encontrada!', 404);
        }

        $validate = Validator::make($request->only(['comment']), [
            'comment' => 'required'
        ]);

        if($validate->fails()) {
            return $this->sendError($validate->errors());
        }

        if($comment['user_id'] !== Auth::id()) {
            return $this->sendError('Você não tem autorização para alterar este comentário!', 403);
        }
        $comment['comment'] = $request['comment'];

        try {
            $comment->save();
        } catch (Exception $e) {
            return $this->sendError('Erro ao atualizar comentário!');
        }

        return $this->sendResponse($comment, 'Comentário atualizado');
    }

    public function delete($id, $commentId) {
        $photo = Photos::find($id);
        $comment = Comments::find($commentId);
 
        if(!$photo) {
            return $this->sendError('Foto não encontrada!', 404);
        }

        if($comment['user_id'] !== Auth::id()) {
            return $this->sendError('Você não tem autorização para deletar este comentário!', 403);
        }

        try {
            $comment->delete();
        } catch (Exception $e) {
            return $this->sendError('Erro ao deletar comentário!');
        }

        return $this->sendResponse($comment, 'Comentário deletado');
    }
}
