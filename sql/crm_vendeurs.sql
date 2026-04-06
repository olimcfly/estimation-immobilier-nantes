CREATE TABLE IF NOT EXISTS crm_vendeurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source ENUM('site_web', 'google_ads', 'facebook', 'bouche_a_oreille') NOT NULL,
    campagne_ads VARCHAR(100) DEFAULT NULL,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) DEFAULT NULL,
    type_bien ENUM('appartement', 'maison', 'loft', 'terrain') NOT NULL,
    ville VARCHAR(50) NOT NULL,
    quartier VARCHAR(50) DEFAULT NULL,
    surface INT DEFAULT NULL,
    nb_pieces INT DEFAULT NULL,
    estimation_min DECIMAL(10, 2) DEFAULT NULL,
    estimation_max DECIMAL(10, 2) DEFAULT NULL,
    date_estimation DATETIME DEFAULT NULL,
    statut ENUM('nouveau', 'contact_etabli', 'visite_planifiee', 'mandat_signe', 'perdu') NOT NULL DEFAULT 'nouveau',
    date_derniere_relance DATETIME DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_crm_vendeurs_email (email),
    INDEX idx_crm_vendeurs_telephone (telephone),
    INDEX idx_crm_vendeurs_statut (statut),
    INDEX idx_crm_vendeurs_localisation (ville, quartier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS crm_interactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendeur_id INT NOT NULL,
    type ENUM('email', 'sms', 'appel', 'visite', 'mandat') NOT NULL,
    contenu TEXT DEFAULT NULL,
    date DATETIME NOT NULL,
    utilisateur_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_crm_interactions_vendeur FOREIGN KEY (vendeur_id)
        REFERENCES crm_vendeurs (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS crm_ads_keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendeur_id INT NOT NULL,
    keyword VARCHAR(255) NOT NULL,
    campagne VARCHAR(100) DEFAULT NULL,
    gclid VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_crm_ads_keywords_campagne (campagne),
    INDEX idx_crm_ads_keywords_keyword (keyword),
    INDEX idx_crm_ads_keywords_gclid (gclid),
    CONSTRAINT fk_crm_ads_keywords_vendeur FOREIGN KEY (vendeur_id)
        REFERENCES crm_vendeurs (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
