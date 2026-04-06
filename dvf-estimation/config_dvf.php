<?php

declare(strict_types=1);

/**
 * Configuration globale du pipeline DVF.
 */
const DVF_SOURCE_URL = 'https://files.data.gouv.fr/geo-dvf/latest/csv/valeursfoncieres-latest.txt.gz';
const DVF_DATA_DIR = __DIR__ . '/data';
const DVF_ARCHIVE_PATH = DVF_DATA_DIR . '/valeursfoncieres-latest.txt.gz';
const DVF_TXT_PATH = DVF_DATA_DIR . '/valeursfoncieres-latest.txt';
const DVF_CSV_PATH = DVF_DATA_DIR . '/valeursfoncieres-latest.csv';
const DVF_JSON_PATH = DVF_DATA_DIR . '/valeursfoncieres-latest.json';
const DVF_CACHE_PATH = DVF_DATA_DIR . '/estimator.cache.php';

/**
 * Période d'analyse par défaut (en mois).
 */
const DVF_DEFAULT_MONTHS = 12;

/**
 * Mappage des types de local DVF vers des libellés applicatifs.
 */
const DVF_TYPE_LOCAL_MAP = [
    '1' => 'maison',
    '2' => 'appartement',
];

/**
 * Définition des champs d'un enregistrement TXT à largeur fixe.
 * Indexation 0-based.
 */
const DVF_FIXED_WIDTH_FIELDS = [
    'id_mutation' => [0, 12],
    'date_mutation' => [12, 8],
    'nature_mutation' => [22, 40],
    'valeur_fonciere' => [62, 14],
    'code_postal' => [121, 5],
    'commune' => [126, 40],
    'type_local' => [168, 2],
    'surface_reelle' => [170, 9],
    'nombre_pieces' => [179, 2],
];

/**
 * Crée le dossier de données DVF si absent.
 *
 * @throws RuntimeException
 */
function ensureDvfDataDirectory(): void
{
    if (is_dir(DVF_DATA_DIR)) {
        return;
    }

    if (!mkdir(DVF_DATA_DIR, 0775, true) && !is_dir(DVF_DATA_DIR)) {
        throw new RuntimeException(sprintf('Impossible de créer le dossier DVF: %s', DVF_DATA_DIR));
    }
}

/**
 * Retourne l'URL source DVF active.
 * Permet une surcharge via variable d'environnement.
 */
function getDvfSourceUrl(): string
{
    $envUrl = getenv('DVF_SOURCE_URL');
    if (is_string($envUrl) && $envUrl !== '') {
        return $envUrl;
    }

    return DVF_SOURCE_URL;
}
