<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadServiceTest extends TestCase
{
 private FileUploadService $fileUploadService;

 protected function setUp(): void
 {
  parent::setUp();
  $this->fileUploadService = new FileUploadService();

  // テスト用のストレージディスクを使用
  Storage::fake('public');
 }

 /**
  * @test
  * isValidFile() 事前条件検証: UploadedFileオブジェクトが渡される
  * 事後条件検証: bool型を返す
  */
 public function isValidFile_returns_true_for_valid_file()
 {
  // 有効なJPEGファイルを作成（画像処理なしのファイル）
  $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg'); // 1MB

  $result = $this->fileUploadService->isValidFile($file);

  // 事後条件: bool型のtrueが返される
  $this->assertIsBool($result);
  $this->assertTrue($result);
 }

 /**
  * @test
  * isValidFile() 事前条件検証: 無効なファイル（サイズ制限超過）
  * 事後条件検証: falseを返す
  */
 public function isValidFile_returns_false_for_oversized_file()
 {
  // 10MBを超えるファイルを作成
  $file = UploadedFile::fake()->create('large.jpg', 11 * 1024, 'image/jpeg'); // 11MB

  $result = $this->fileUploadService->isValidFile($file);

  // 事後条件: falseが返される
  $this->assertIsBool($result);
  $this->assertFalse($result);
 }

 /**
  * @test
  * isValidFile() 許可されていないMIMEタイプでfalseを返すこと
  */
 public function isValidFile_returns_false_for_disallowed_mime_type()
 {
  // テキストファイルを作成（許可されていない）
  $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

  $result = $this->fileUploadService->isValidFile($file);

  // 事後条件: falseが返される
  $this->assertFalse($result);
 }

 /**
  * @test
  * upload() 事前条件検証: 有効なUploadedFileが渡される
  * 事後条件検証: ファイルパスの文字列を返す
  */
 public function upload_uploads_valid_file_and_returns_path()
 {
  $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

  $result = $this->fileUploadService->upload($file);

  // 事後条件: 文字列のファイルパスが返される
  $this->assertIsString($result);
  $this->assertStringStartsWith('storage/', $result);
  $this->assertStringContainsString('test.jpg', $result);
 }

 /**
  * @test
  * upload() 事前条件違反: 無効なファイルの場合
  * 事後条件検証: 例外が投げられる
  */
 public function upload_throws_exception_for_invalid_file()
 {
  // 無効なファイル（サイズ制限超過）
  $file = UploadedFile::fake()->create('large.jpg', 11 * 1024, 'image/jpeg'); // 11MB

  // 事前条件違反により例外が投げられることを期待
  $this->expectException(\Exception::class);
  $this->expectExceptionMessage('無効なファイルです。');

  $this->fileUploadService->upload($file);
 }

 /**
  * @test
  * 不変条件検証: バリデーションメソッドは副作用を持たない
  */
 public function isValidFile_has_no_side_effects()
 {
  $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

  // 複数回呼び出して同じ結果が返ることを確認
  $result1 = $this->fileUploadService->isValidFile($file);
  $result2 = $this->fileUploadService->isValidFile($file);
  $result3 = $this->fileUploadService->isValidFile($file);

  // 不変条件: 複数回呼び出しても同じ結果
  $this->assertEquals($result1, $result2);
  $this->assertEquals($result2, $result3);
  $this->assertTrue($result1);
 }
}
