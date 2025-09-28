<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Badge;


class BadgeService
{
    public function getData(){
        $data = Badge::get();
        return $data;
    }
    public function getDataDatatables(){
        $dataUser = Badge::Orderby('id','asc');
        return $dataUser;
    }

    public function createOrUpdateData($request)
    {
        DB::beginTransaction();
        try {
            if($request->input('id') == 0){
                $badge = new Badge();
            }else{
                $badge = Badge::find($request->input('id'));
            }
            $badge->title = $request->title;
            $badge->type = $request->type;
            $badge->total = $request->total;
            $badge->text_congrats_1 = $request->text_congrats_1;
            $badge->text_congrats_2 = $request->text_congrats_2;
            $badge->text_congrats_3 = $request->text_congrats_3;
            $img = $request->filenameimageActive;
            $img_inactive = $request->filenameimageInActive;
            $img_congrats = $request->filenameimageContrats;
            if (isset($img) && $img != '') {
                $png_url = str_replace(' ', '_', $request->title).rand(1,10).'_active.'.'png';
                $image = explode('base64,',$img);
                $image = end($image);
                $image = str_replace(' ', '+', $image);
                $file = "badges/" . $png_url;
                $success = Storage::disk('public')->put($file,base64_decode($image));
                $badge->icon_active = $png_url;
            }
            if (isset($img_inactive) && $img_inactive != '') {
                $png_url = str_replace(' ', '_', $request->title).rand(1,10).'_inactive.'.'png';
                $image = explode('base64,',$img_inactive);
                $image = end($image);
                $image = str_replace(' ', '+', $image);
                $file = "badges/" . $png_url;
                $success = Storage::disk('public')->put($file,base64_decode($image));
                $badge->icon_unactive = $png_url;
            }
            if (isset($img_congrats) && $img_congrats != '') {
                $png_url = str_replace(' ', '_', $request->title).rand(1,10).'_congrats.'.'png';
                $image = explode('base64,',$img_congrats);
                $image = end($image);
                $image = str_replace(' ', '+', $image);
                $file = "badges/" . $png_url;
                $success = Storage::disk('public')->put($file,base64_decode($image));
                $badge->images_congrats = $png_url;
            }

            $badge->save();
            DB::commit();
            $status = true;
            $message = $badge;
        } catch (\Throwable $e) {
            $status = false;
            DB::rollback();
            $message = $e;
            \Log::error($e->getFile() . ": " . $e->getLine() . " | " . $e->getMessage() . " | (" . $e->getCode() . ") " . json_encode($request));
        }
        return array(
            'status' => $status,
            'message' => $message
        );
    }

    public function createData($request){
        DB::beginTransaction();
        try {
            $data['title'] = $request->title;
            $data['type'] = $request->type;
            $data['total'] = $request->total;
            $img = $request->icon_active;
            $img_inactive = $request->icon_inactive;
            if (isset($img) && $img != '') {
                $png_url = str_replace(' ', '_', $request->title).rand(1,10).'_active.'.'png';
                $image = explode('base64,',$img);
                $image = end($image);
                $image = str_replace(' ', '+', $image);
                $file = "badges/" . $png_url;
                $success = Storage::disk('public')->put($file,base64_decode($image));
                $data['icon_active'] = $png_url;
            }
            if (isset($img_inactive) && $img_inactive != '') {
                $png_url = str_replace(' ', '_', $request->title).rand(1,10).'_inactive.'.'png';
                $image = explode('base64,',$img_inactive);
                $image = end($image);
                $image = str_replace(' ', '+', $image);
                $file = "badges/" . $png_url;
                $success = Storage::disk('public')->put($file,base64_decode($image));
                $data['icon_unactive'] = $png_url;
            }
            $user = Badge::create($data);
            DB::commit();
            $status = true;
            $message = $user;
        } catch (\Throwable $e) {
            dd($e);
            $status = false;
            DB::rollback();
            $message = $e;
            \Log::error($e->getFile() . ": " . $e->getLine() . " | " . $e->getMessage() . " | (" . $e->getCode() . ") " . json_encode($request));
        }
        return array(
            'status' => $status,
            'message' => $message
        );
    }

    public function updateData($request,$id){
        DB::beginTransaction();
        try {
            $group = Badge::find($id);
            $group->title = $request->title;
            $group->type = $request->type;
            $group->total = $request->total;
            $img = $request->icon_active;
            $img_inactive = $request->icon_inactive;
            if (isset($img) && $img != '') {
                $png_url = str_replace(' ', '_', $request->title).rand(1,10).'_active.'.'png';
                $image = explode('base64,',$img);
                $image = end($image);
                $image = str_replace(' ', '+', $image);
                $file = "badges/" . $png_url;
                $success = Storage::disk('public')->put($file,base64_decode($image));
                $group->icon_active = $png_url;
            }
            if (isset($img_inactive) && $img_inactive != '') {
                $png_url = str_replace(' ', '_', $request->title).rand(1,10).'_inactive.'.'png';
                $image = explode('base64,',$img_inactive);
                $image = end($image);
                $image = str_replace(' ', '+', $image);
                $file = "badges/" . $png_url;
                $success = Storage::disk('public')->put($file,base64_decode($image));
                $group->icon_unactive = $png_url;
            }
            $group->save();
            DB::commit();
            $status = true;
            $message = $group;
        } catch (\Throwable $e) {
            dd($e);
            $status = false;
            DB::rollback();
            $message = $e;
            \Log::error($e->getFile() . ": " . $e->getLine() . " | " . $e->getMessage() . " | (" . $e->getCode() . ") " . json_encode($request));
        }
        return array(
            'status' => $status,
            'message' => $message
        );
    }

    public function findData($id){
        $data = Badge::find($id);
        return $data;
    }

    public function deleteData($id){
        $data = Badge::find($id)->delete();
        return $data;
    }
}
