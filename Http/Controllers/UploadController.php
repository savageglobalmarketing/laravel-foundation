<?php

namespace SavageGlobalMarketing\Foundation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        dd($request->all());
        $file = Storage::disk(config('foundation.upload_disk'))->put('uploads', $request->file('uploadFileObj'));

        dd($file);
        return response()->json(['path' => $file]);
    }
}
