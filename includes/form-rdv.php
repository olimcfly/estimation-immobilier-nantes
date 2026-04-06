<?php

require_once __DIR__ . '/../classes/Webhook.php';

function handleRdvSubmission(array $leadData, array $rdvData): void
{
    // ... logique métier d'enregistrement du RDV
    Webhook::newRdv($leadData, $rdvData);
}
