<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Image;
use Exception;
use App\Photos;

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

        $image = $request->file('image');
        $imgName = time() . '.' . $image->getClientOriginalExtension();

        $path = public_path('images');
        $img = Image::make($image);
        $img->encode('jpg', 75)->resize(500, 500)->save($path . '/' . $imgName);

        $data['path'] = $path . '\\' . $imgName;
       
        //TODO get auth user and its id
        $data['user_id'] = 7;

        // return $data;
        try {
            $photo = Photos::create($data);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $this->sendResponse($photo,'Foto registrada com sucesso!');


    }
    public function read($id) {

    }
    public function readAll() {

    }
    public function update(Request $request, $id) {

    }
    public function delete($id) {

    }
}
