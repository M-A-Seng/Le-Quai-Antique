<?php

namespace App\Core;

/**
 * Logger Génère des logs dans logs/app.log par défaut ; Si le dossier/fichier n'existe pas -> sera automatiquement crée.
 */
class Logger
{
    private string $file;
    /**
     * __construct
     *
     * @param  string $fileName
     * @return void
     */
    public function __construct(string $fileName = 'app.log')
    {
        $logDir = DIR_ROOT . '/logs';
        // Permet de prendre en charge aussi bien un nom de fichier seul qu'un chemin avec des /
        if (str_contains($fileName, '/')) {
            $this->file = $fileName;
            $logDir = dirname($fileName);
        } else {
            $logDir = DIR_ROOT . '/logs';
            $this->file = $logDir . '/' . $fileName;
        }
        // Si le dossier n'existe pas il est crée avec les permissions larges (0777)
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }

    public function info(string $message): void
    {
        $this->write('INFO', $message);
    }

    public function warning(string $message): void
    {
        $this->write('WARNING', $message);
    }

    public function error(string $message): void
    {
        $this->write('ERROR', $message);
    }

    public function dbError(string $message): void
    {
        $this->write('DATABASE ERROR', $message);
    }

    private function write(string $level, string $message): void
    {
        // Formatte la ligne avec [date], [niveau], [message]
        $line = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), $level, $message);
        // écrit le log dans le fichier sans écraser les précédents
        file_put_contents($this->file, $line, FILE_APPEND);
    }
}