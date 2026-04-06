<?php

require_once __DIR__ . '/../classes/Webhook.php';

function handleEstimationSubmission(array $leadData): void
{
    // ... logique métier d'enregistrement de l'estimation
    Webhook::newEstimation($leadData);
}
