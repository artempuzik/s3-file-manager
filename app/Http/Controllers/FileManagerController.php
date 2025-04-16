<?php

namespace App\Http\Controllers;

use App\Services\S3Service;
use Illuminate\Http\Request;

class FileManagerController extends Controller
{
    protected $s3Service;

    public function __construct(S3Service $s3Service)
    {
        $this->s3Service = $s3Service;
    }

    public function index(Request $request)
    {
        $path = $request->get('path', '');
        $contents = $this->s3Service->listFiles($path);

        return view('file-manager.index', [
            'files' => $contents['files'],
            'folders' => $contents['folders'],
            'currentPath' => $path,
        ]);
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'folder_name' => 'required|string',
            'current_path' => 'required|string',
        ]);

        $path = rtrim($request->current_path, '/') . '/' . $request->folder_name;
        $this->s3Service->createFolder($path);

        return redirect()->back()->with('success', 'Folder created successfully');
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'current_path' => 'required|string',
        ]);

        $file = $request->file('file');
        $path = rtrim($request->current_path, '/') . '/' . $file->getClientOriginalName();

        $this->s3Service->uploadFile($file, $path);

        return redirect()->back()->with('success', 'File uploaded successfully');
    }

    public function deleteFile(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $this->s3Service->deleteFile($request->path);

        return redirect()->back()->with('success', 'File deleted successfully');
    }

    public function getFileUrl($path)
    {
        $url = $this->s3Service->getFileUrl($path);
        return response()->json(['url' => $url]);
    }

    public function downloadFile($path)
    {
        $file = $this->s3Service->downloadFile($path);
        
        return response()->stream(
            function () use ($file) {
                echo $file['content'];
            },
            200,
            [
                'Content-Type' => $file['contentType'],
                'Content-Length' => $file['contentLength'],
                'Content-Disposition' => 'attachment; filename="' . $file['filename'] . '"'
            ]
        );
    }
}
