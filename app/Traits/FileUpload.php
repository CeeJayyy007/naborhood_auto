<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Validator;
use File;

trait FileUpload
{
    /**
    * telling the class to inherit ApiResponse trait
    */
    use ApiResponse;    

    /**
     * upload new file
     * @param  \Illuminate\Http\Request  $request
     * @return App\Trait
     */
    public function newImageUpload(Request $request, $user)
    {           
        // get filename of file to be uploaded
        $file_name_with_extension = $user->avatar;
        
        // // get file path in public folder
        $file_path = public_path('images/'.$file_name_with_extension);

        // check if file exists in public folder and delete
        if(File::exists($file_path)){
            File::delete($file_path);
            // create new uploaded image name and save image into public_path
            $newImageName = time().'_'.$request->image->getClientOriginalName();
            $request->image->move(public_path('images'), $newImageName);
        }else{
            // create new uploaded image name and save image into public_path
            $newImageName = time().'_'.$request->image->getClientOriginalName();
            $request->image->move(public_path('images'), $newImageName);            
        }
        return $newImageName;
    }

    
    /**
     * delete uploaded immage
     * @param  \Illuminate\Http\Request  $request
     */
    public function deleteUploadedImage($user)
    {
        // get filename of file to be deleted
        $file_name_with_extension = $user->avatar;
        
        // // get file path in public folder
        $file_path = public_path('images/'.$file_name_with_extension);

        // delete locally
        // check if file exists in public folder, then delete
        if(File::exists($file_path)){
            File::delete($file_path);
            $user['avatar'] = null;
            $user->save();
            $message = "Avatar removed successfully!";
        }else{
            // if file does not exist, display message
            $message = "Image does not exist or has been deleted!";
        }
        return $message;
    }
}