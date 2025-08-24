<?php

namespace App\Application\Services;

use App\Application\Contracts\FileUploadServiceInterface;
use Illuminate\Http\UploadedFile;

class FileUploadService implements FileUploadServiceInterface
{
    /**
     * ファイルをアップロードして保存先のパスを返す
     *
     * @param UploadedFile $file
     * @return string
     * @throws \Exception
     */
    public function upload(UploadedFile $file): string
    {
        if (!$this->isValidFile($file)) {
            throw new \Exception('無効なファイルです。');
        }

        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('', $fileName, 'public');
        
        return 'storage/' . $fileName;
    }

    /**
     * ファイルが有効かどうかを検証
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function isValidFile(UploadedFile $file): bool
    {
        // ファイルが正常にアップロードされているかチェック
        if (!$file->isValid()) {
            return false;
        }

        // ファイルサイズチェック（10MB以下）
        if ($file->getSize() > 10 * 1024 * 1024) {
            return false;
        }

        // 許可されたMIMEタイプのチェック
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return false;
        }

        return true;
    }
}
