<?php 

namespace Tests\Traits;

trait TestStorages
{
    protected function deleteAllFiles()
    {
        $folders = \Storage::directories();
        foreach ($folders as $folder) {
            $files = \Storage::files($folder);
            \Storage::delete($files);
            \Storage::deleteDirectory($folder);
        }
    }
}