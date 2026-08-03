<?php
namespace App\Utils;

class UploadHandler
{
    protected string $targetDir;
    protected int $maxSize;
    protected array $allowedMime;
    protected array $allowedExt;

    public function __construct(string $targetDir = 'uploads', int $maxSize = 5_242_880, array $allowedMime = [], array $allowedExt = [])
    {
        $this->targetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
        $this->maxSize = $maxSize; // default 5MB
        $this->allowedMime = $allowedMime;
        $this->allowedExt = $allowedExt;
        if (!is_dir($this->targetDir)) {
            if (!mkdir($this->targetDir, 0755, true) && !is_dir($this->targetDir)) {
                throw new \RuntimeException('Impossible de créer le dossier de destination des uploads.');
            }
        }
    }

    public function handle(string $fieldName): array
    {
        if (empty($_FILES[$fieldName])) {
            return ['success' => false, 'error' => 'no_file_uploaded'];
        }

        $file = $_FILES[$fieldName];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'upload_error', 'code' => $file['error']];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'invalid_upload'];
        }

        if ($file['size'] > $this->maxSize) {
            return ['success' => false, 'error' => 'file_too_large'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!empty($this->allowedMime) && !in_array($mime, $this->allowedMime, true)) {
            return ['success' => false, 'error' => 'invalid_mime', 'mime' => $mime];
        }
        if (!empty($this->allowedExt) && !in_array($ext, $this->allowedExt, true)) {
            return ['success' => false, 'error' => 'invalid_extension', 'ext' => $ext];
        }

        // Generate safe filename
        $basename = bin2hex(random_bytes(8));
        $safeName = $basename . ($ext ? '.' . $ext : '');
        $dest = $this->targetDir . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['success' => false, 'error' => 'move_failed'];
        }

        return [
            'success' => true,
            'path' => $this->buildPublicFacingPath($safeName),
            'mime' => $mime,
            'name' => $safeName
        ];
    }

    protected function buildPublicFacingPath(string $fileName): string
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', basename($this->targetDir) . DIRECTORY_SEPARATOR . $fileName);
    }
}
