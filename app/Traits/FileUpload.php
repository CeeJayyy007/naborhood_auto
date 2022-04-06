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
    public function newImageUpload(Request $request)
    {
 
            $validator = Validator::make($request->all(),[
               "image" => "required|file|mimes:jpeg,png,jpg,gif,svg|max:5048",
               "user_id" => "required|integer"
            ]);

            if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
            }
            
            $newImageName = time().'_'.$request->image->getClientOriginalName();
            $test = $request->image->move(public_path('images'), $newImageName);
            
            // dd($test);

            return $newImageName;
    }

    
    /**
     * delete uploaded immage
     * @param  \Illuminate\Http\Request  $request
     */
    public function deleteUploadedImage($user)
    {

        // get filename of file to be deleted
            $file_name_with_extension = $user->user_avatar;
            
            // // get file path in public folder
            $file_path = public_path('images/'.$file_name_with_extension);

            // delete locally
            // check if file exists in public folder, then delete
            if(File::exists($file_path)){
                File::delete($file_path);
                $user['user_avatar'] = null;
                $user->save();
                $message = "User avatar removed successfully!";
            }else{
                // if file does not exist, display message
                $message = "Image does not exist or has been deleted!";
            }
        return $message;
    }
}