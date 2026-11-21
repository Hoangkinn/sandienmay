<?php

namespace App\Helpers;

use FilesystemIterator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class UploadImageHelper
{
    private const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    /**
     * Lưu ảnh xuống public/{folder}, trả về ['name', 'url', 'reused'].
     * Ảnh đã có trên đĩa (trùng từng byte) được dùng lại thay vì ghi thêm bản mới.
     *
     * @return array{name: string, url: string, reused: bool}
     */
    public static function store(UploadedFile $file, string $folder): array
    {
        $existing = self::findByHash(md5_file($file->getPathname()), $file->getSize());
        if ($existing !== null) {
            return [
                'name' => basename($existing),
                'url' => '/' . $existing,
                'reused' => true,
            ];
        }

        $uploadPath = public_path($folder);
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = self::fileName($file);
        $file->move($uploadPath, $fileName);

        return [
            'name' => $fileName,
            'url' => '/' . trim($folder, '/') . '/' . $fileName,
            'reused' => false,
        ];
    }

    /**
     * Tên file lưu dạng "thời-gian_<slug-khong-dau>.<đuôi gốc>".
     */
    public static function fileName(UploadedFile $file): string
    {
        $slug = Str::slug(self::stripDuplicateSuffix(
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
        ));

        return time() . '_' . ($slug !== '' ? $slug : 'anh') . '.' . $file->guessExtension();
    }

    /**
     * Bỏ hậu tố Windows/Chrome tự thêm khi trùng tên: "ban-hut-nuoc (1) 1.png" -> "ban hut nuoc".
     */
    private static function stripDuplicateSuffix(string $name): string
    {
        $name = preg_replace('/\s*\([\dA-Za-z]\)/u', '', $name);

        // Chỉ cắt số 1-2 chữ số cuối để không phá model name: "bst-170", "kms-1500" giữ nguyên.
        return preg_replace('/(?:[\s_-]+\d{1,2})+$/u', '', $name);
    }

    /**
     * Tìm file ảnh đã có trong public/storage có cùng dung lượng rồi cùng md5.
     *
     * @return string|null Đường dẫn tương đối tính từ public/, ví dụ "storage/photos/39/abc.png"
     */
    private static function findByHash(string $hash, int $size): ?string
    {
        $root = public_path('storage');
        if (!is_dir($root)) {
            return null;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($items as $item) {
            if (!$item->isFile()
                || $item->getSize() !== $size
                || !in_array(strtolower($item->getExtension()), self::IMAGE_EXTENSIONS, true)
            ) {
                continue;
            }

            if (md5_file($item->getPathname()) === $hash) {
                $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($root) + 1));
                return 'storage/' . $relative;
            }
        }

        return null;
    }
}
