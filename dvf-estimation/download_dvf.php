<?php

declare(strict_types=1);

require_once __DIR__ . '/config_dvf.php';

/**
 * Télécharge un fichier distant vers un chemin local.
 *
 * @throws RuntimeException
 */
function downloadFile(string $url, string $destination): void
{
    $fp = fopen($destination, 'wb');
    if ($fp === false) {
        throw new RuntimeException(sprintf('Impossible d\'ouvrir le fichier de destination: %s', $destination));
    }

    $ch = curl_init($url);
    if ($ch === false) {
        fclose($fp);
        throw new RuntimeException('Impossible d\'initialiser cURL.');
    }

    curl_setopt_array($ch, [
        CURLOPT_FILE => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 180,
        CURLOPT_CONNECTTIMEOUT => 20,
        CURLOPT_FAILONERROR => true,
        CURLOPT_USERAGENT => 'DVFEstimator/1.0',
    ]);

    $ok = curl_exec($ch);
    $error = curl_error($ch);
    $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    curl_close($ch);
    fclose($fp);

    if ($ok === false || $statusCode >= 400) {
        @unlink($destination);
        throw new RuntimeException(sprintf('Échec du téléchargement (%d): %s', $statusCode, $error ?: 'Erreur inconnue'));
    }
}

/**
 * Extrait une archive ZIP vers un fichier TXT.
 *
 * @throws RuntimeException
 */
function extractZipToTxt(string $zipPath, string $targetTxtPath): void
{
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        throw new RuntimeException(sprintf('Impossible d\'ouvrir l\'archive ZIP: %s', $zipPath));
    }

    $txtIndex = null;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = (string) $zip->getNameIndex($i);
        if (str_ends_with(strtolower($name), '.txt')) {
            $txtIndex = $i;
            break;
        }
    }

    if ($txtIndex === null) {
        $zip->close();
        throw new RuntimeException('Aucun fichier TXT trouvé dans l\'archive ZIP.');
    }

    $stream = $zip->getStream($zip->getNameIndex($txtIndex));
    if ($stream === false) {
        $zip->close();
        throw new RuntimeException('Impossible de lire le flux TXT de l\'archive ZIP.');
    }

    $out = fopen($targetTxtPath, 'wb');
    if ($out === false) {
        fclose($stream);
        $zip->close();
        throw new RuntimeException(sprintf('Impossible d\'écrire le fichier TXT: %s', $targetTxtPath));
    }

    stream_copy_to_stream($stream, $out);
    fclose($stream);
    fclose($out);
    $zip->close();
}

/**
 * Extrait un fichier GZ vers un TXT.
 *
 * @throws RuntimeException
 */
function extractGzToTxt(string $gzPath, string $targetTxtPath): void
{
    $in = gzopen($gzPath, 'rb');
    if ($in === false) {
        throw new RuntimeException(sprintf('Impossible d\'ouvrir l\'archive GZ: %s', $gzPath));
    }

    $out = fopen($targetTxtPath, 'wb');
    if ($out === false) {
        gzclose($in);
        throw new RuntimeException(sprintf('Impossible d\'écrire le fichier TXT: %s', $targetTxtPath));
    }

    while (!gzeof($in)) {
        $chunk = gzread($in, 8192);
        if ($chunk === false) {
            gzclose($in);
            fclose($out);
            throw new RuntimeException('Erreur de lecture de l\'archive GZ.');
        }
        fwrite($out, $chunk);
    }

    gzclose($in);
    fclose($out);
}

/**
 * Télécharge et extrait la source DVF.
 *
 * @return array{source_url:string,archive_path:string,txt_path:string}
 */
function syncDvfSource(?string $sourceUrl = null): array
{
    ensureDvfDataDirectory();

    $sourceUrl = trim((string) ($sourceUrl ?? getDvfSourceUrl()));
    if ($sourceUrl === '') {
        throw new RuntimeException('URL DVF vide.');
    }

    downloadFile($sourceUrl, DVF_ARCHIVE_PATH);

    $lowerUrl = strtolower($sourceUrl);
    if (str_ends_with($lowerUrl, '.zip')) {
        extractZipToTxt(DVF_ARCHIVE_PATH, DVF_TXT_PATH);
    } elseif (str_ends_with($lowerUrl, '.gz') || str_ends_with(strtolower(DVF_ARCHIVE_PATH), '.gz')) {
        extractGzToTxt(DVF_ARCHIVE_PATH, DVF_TXT_PATH);
    } else {
        throw new RuntimeException('Format d\'archive non supporté (attendu: .zip ou .gz).');
    }

    return [
        'source_url' => $sourceUrl,
        'archive_path' => DVF_ARCHIVE_PATH,
        'txt_path' => DVF_TXT_PATH,
    ];
}

if (PHP_SAPI === 'cli' && realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    try {
        $url = $argv[1] ?? null;
        $result = syncDvfSource($url);

        echo 'Téléchargement DVF depuis ' . $result['source_url'] . PHP_EOL;
        echo 'Archive: ' . $result['archive_path'] . PHP_EOL;
        echo 'Fichier extrait : ' . $result['txt_path'] . PHP_EOL;
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, 'Erreur DVF: ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}
