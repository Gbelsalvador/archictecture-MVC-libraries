<?php
namespace App\cache;

class FileCache implements CacheInterface {
    private string $cacheDir;
    
    public function __construct(string $cacheDir) {
        $this->cacheDir = rtrim($cacheDir, '/\\') . DIRECTORY_SEPARATOR;

        if(!is_dir($this->cacheDir)){
            mkdir($this->cacheDir, 0755, true);
        }
    }

    private function getFilePath(string $key): string {
        return $this->cacheDir . sha1($key) .'.cache';
    }

    public function get(string $key, mixed $default = null): mixed {
        $file = $this->getFilePath($key);
        if(!file_exists($file)){
            return $default;
        }

        $content = file_get_contents($file);
        $data = unserialize($content);

        if(time() > $data['expires_at']) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool {
        $file = $this->getFilePath($key);

        $data = [
            'expires_at' => time() + $ttl,
            'value'=> $value
        ];
        # ecriture atomique avec verouillage de fichier (LOCK_EX)
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }

    public function delete(string $key): bool {
        $file = $this->getFilePath($key);
        if(!file_exists($file)){
            return unlink($file);
        }

        return true;
    }

    public function clear(): bool{
        $file = glob($this->cacheDir . '*.cache');
        foreach($file as $file){
            if(is_file($file)){
                unlink($file);
            }
        }
        return true;
    }

    public function remember(string $key, int $ttl , callable $callback): mixed {

    $value = $this->get($key);

    if($value !== null){
        return $value;
    }
    $computedValue = $callback();
    $this->set($key, $computedValue, $ttl);

    return $computedValue;
    }
}