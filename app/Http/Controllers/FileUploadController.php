<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;


class FileUploadController extends Controller
{
    
    private $chunkStorageDisk = 'local';
    private $chunkStorageDir = 'chunks';
    private $finalStorageDir = 'uploads';

  
    public function uploadChunk(Request $request)
    {
        
        $request->validate([
            'file' => 'required|file',
            'file_id' => 'required|string',
            'chunk_index' => 'required|integer',
            'total_chunks' => 'required|integer',
            'file_name' => 'required|string',
        ]);

        $file = $request->file('file');
        $fileId = $request->input('file_id');
        $chunkIndex = $request->input('chunk_index');
        
      
        $tempDir = $this->chunkStorageDir . '/' . $fileId;

     
        $path = $file->storeAs($tempDir, $chunkIndex, $this->chunkStorageDisk);

       
        return response()->json([
            'message' => 'Chunk uploaded successfully', 
            'chunk_index' => $chunkIndex
        ]);
    }

  
    public function combineChunks(Request $request)
    {
      
        $request->validate(['file_id' => 'required|string']);

        $fileId = $request->input('file_id');
        $tempDir = $this->chunkStorageDir . '/' . $fileId;
        
      
        $chunks = Storage::disk($this->chunkStorageDisk)->files($tempDir);
        
        if (empty($chunks)) {
            return response()->json(['error' => 'No chunks found for this ID.'], 404);
        }

      
        natsort($chunks);

      
        $finalFileContent = '';
        foreach ($chunks as $chunkPath) {
            $finalFileContent .= Storage::disk($this->chunkStorageDisk)->get($chunkPath);
        }

     
        $finalFileName = time() . '_' . $fileId . '.dat'; 
        $finalPath = $this->finalStorageDir . '/' . $finalFileName;

    
        Storage::disk($this->chunkStorageDisk)->put($finalPath, $finalFileContent);
        
       
        Storage::disk($this->chunkStorageDisk)->deleteDirectory($tempDir);

       
        $fileUrl = Storage::url($finalPath);

        return response()->json([
            'message' => 'File successfully combined',
            'file_id' => $fileId,
            'url' => $fileUrl, 
            'path' => $finalPath
        ]);
    }
}