<?php

namespace App\Application\Contracts;

use Illuminate\Http\UploadedFile;

interface FileUploadServiceInterface
{
 /**
  * ファイルをアップロードして保存先のパスを返す
  *
  * @param UploadedFile $file
  * @return string
  * @throws \Exception
  */
 public function upload(UploadedFile $file): string;

 /**
  * ファイルが有効かどうかを検証
  *
  * @param UploadedFile $file
  * @return bool
  */
 public function isValidFile(UploadedFile $file): bool;
}
