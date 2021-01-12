<?php

namespace SavageGlobalMarketing\Foundation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $file = Storage::disk(config('foundation.upload_disk'))->put('uploads', $request->file('uploadFileObj'));

        return response()->json(['path' => $file]);
    }
}
