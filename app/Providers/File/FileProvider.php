<?php

namespace App\Providers\File;

use Illuminate\Support\ServiceProvider;
use File;
use App\Http\Controllers\ResponseController as Res;

class FileProvider extends ServiceProvider
{
    // accepted img
    public static function imgAccepted()
    {
        return explode(',', config('constants.image_file_type_accept'));
    }
    // Saving Image gy base64 as a String ,path,userid
    public static function saveImgFromBase64(string $image, string $path = 'general/', array $sized = [800, 600])
    {
        if (count($sized) < 1) Res::badRequest($sized);
        try {
            $dir = public_path('images/' . $path);
            if ($image) {
                // check directory is exist or not if not then create new one
                if (!File::isDirectory($dir)) {
                    mkdir($dir, 0666, true);
                }
                $name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
                $namecheck = explode('.', $name);
                $filetype = $namecheck[1];
                // file type is not accepted
                $savePath = $dir . '/' . $name;
                if (!in_array($filetype, HelperController::imgAccepted())) return Res::badRequest($filetype);
                $isSave = \Image::make($image)->resize($sized[0], $sized[1])->save($savePath);
                $locatedPath = $path . $name;
                return Res::success($locatedPath);
            }
            return Res::error();
        } catch (Exception $ex) {
            return Res::error($ex->getMessage());
        }
    }

    public static function saveImgFormFile($file, string $path = 'general/', array $sized = [800, 600])
    {
        try {
            $dir = public_path('images/' . $path);
            $filetype = $file->getClientOriginalExtension(); // getting image extension
            $filename = time() . '.' . $filetype;

            // check directory is exist or not if not then create new one
            if (!File::isDirectory($dir)) {
                mkdir($dir, 0666, true);
            }
            // file type is not accepted
            $savePath = $dir . '/' . $filename;
            if (!in_array(strtolower($filetype), FileProvider::imgAccepted())) return Res::badRequest([], "Can not read .$filetype file");
            \Image::make($file->path())->resize($sized[0], $sized[1])->save($savePath);
            $locatedPath = $path . $filename;
            return Res::success($locatedPath);
        } catch (Exception $ex) {
            return Res::error($ex->getMessage());
        }
    }
}
