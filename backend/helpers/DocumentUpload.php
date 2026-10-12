<?php

final class DocumentUpload
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];

    /**
     * @return array{path: string, mime: string}
     */
    public static function store(array $file, string $directory): array
    {
        if (!isset($file['error'], $file['tmp_name'], $file['size'])
            || !is_int($file['error'])
            || !is_string($file['tmp_name'])
            || !is_int($file['size'])) {
            throw new InvalidArgumentException('Selecciona una imagen o archivo PDF válido.');
        }

        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            throw new InvalidArgumentException('El documento no puede superar 10 MB.');
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('No se recibió el archivo. Vuelve a seleccionarlo.');
        }

        if ($file['size'] <= 0 || $file['size'] > 10 * 1024 * 1024) {
            throw new InvalidArgumentException('El documento no puede superar 10 MB.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new RuntimeException('No se pudo validar el tipo del documento.');
        }
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!is_string($mime) || !isset(self::MIME_EXTENSIONS[$mime])) {
            throw new InvalidArgumentException('El documento debe ser JPG, PNG, WEBP o PDF.');
        }

        if (!preg_match('~^uploads/profiles/[a-z-]+/$~', $directory)) {
            throw new InvalidArgumentException('Directorio de documentos inválido.');
        }

        $root = dirname(__DIR__, 2);
        $absoluteDirectory = $root . '/' . $directory;
        if (!is_dir($absoluteDirectory) && !mkdir($absoluteDirectory, 0755, true) && !is_dir($absoluteDirectory)) {
            throw new RuntimeException('No se pudo preparar el directorio de documentos.');
        }

        $filename = 'document_' . bin2hex(random_bytes(16)) . '.' . self::MIME_EXTENSIONS[$mime];
        $absolutePath = $absoluteDirectory . $filename;
        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            throw new RuntimeException('No se pudo guardar el documento.');
        }

        return ['path' => $directory . $filename, 'mime' => $mime];
    }

    public static function deleteManaged(string $relativePath): bool
    {
        $root = dirname(__DIR__, 2);
        $allowedDirectories = [
            $root . '/uploads/profiles/documents',
            $root . '/uploads/profiles/change-requests',
        ];
        $absolutePath = $root . '/' . ltrim($relativePath, '/');
        $resolvedPath = realpath($absolutePath);

        if ($resolvedPath === false) {
            return true;
        }

        foreach ($allowedDirectories as $directory) {
            $resolvedDirectory = realpath($directory);
            if ($resolvedDirectory !== false
                && str_starts_with($resolvedPath, $resolvedDirectory . DIRECTORY_SEPARATOR)
                && is_file($resolvedPath)) {
                return unlink($resolvedPath);
            }
        }

        throw new InvalidArgumentException('La ruta del documento no pertenece a un directorio administrado.');
    }
}
