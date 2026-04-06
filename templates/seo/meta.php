<?php
declare(strict_types=1);

/** @var array<string, mixed> $pageData */
$pageData = $pageData ?? [];
?>
<title><?= htmlspecialchars((string) ($pageData['title'] ?? ''), ENT_QUOTES); ?></title>
<meta name="description" content="<?= htmlspecialchars((string) ($pageData['meta_description'] ?? ''), ENT_QUOTES); ?>">
<link rel="canonical" href="<?= htmlspecialchars((string) ($pageData['canonical'] ?? ''), ENT_QUOTES); ?>">
<?php if (!empty($pageData['keywords']) && is_array($pageData['keywords'])): ?>
<meta name="keywords" content="<?= htmlspecialchars(implode(', ', $pageData['keywords']), ENT_QUOTES); ?>">
<?php endif; ?>
