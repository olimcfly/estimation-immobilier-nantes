SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ================================================
-- TABLE PRINCIPALE : ESTIMATIONS / LEADS
-- ================================================
CREATE TABLE IF NOT EXISTS estimations (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Identification
    reference VARCHAR(20) UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Type d'estimation
    type_estimation ENUM('simple','detaillee') DEFAULT 'simple',

    -- Contact
    prenom VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),

    -- Localisation
    adresse TEXT,
    complement_adresse VARCHAR(255),
    code_postal VARCHAR(10),
    ville VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    distance_centre DECIMAL(6, 2),
    dans_zone TINYINT(1) DEFAULT 1,

    -- Bien immobilier - Base
    type_bien ENUM('appartement','maison','terrain','commerce','immeuble') NOT NULL,
    surface DECIMAL(10, 2) NOT NULL,
    nb_pieces INT,
    nb_chambres INT,

    -- Bien immobilier - Détails
    etat_bien ENUM('neuf','renove','bon','travaux','refaire'),
    annee_construction INT,
    etage INT,
    nb_etages_immeuble INT,
    ascenseur TINYINT(1) DEFAULT 0,

    -- Équipements
    terrasse TINYINT(1) DEFAULT 0,
    surface_terrasse DECIMAL(8, 2),
    balcon TINYINT(1) DEFAULT 0,
    jardin TINYINT(1) DEFAULT 0,
    surface_jardin DECIMAL(10, 2),
    parking TINYINT(1) DEFAULT 0,
    nb_parkings INT DEFAULT 0,
    garage TINYINT(1) DEFAULT 0,
    cave TINYINT(1) DEFAULT 0,
    piscine TINYINT(1) DEFAULT 0,

    -- Caractéristiques
    exposition ENUM('nord','sud','est','ouest','nord-est','nord-ouest','sud-est','sud-ouest'),
    vue ENUM('standard','degagee','mer','montagne','parc','monument'),
    luminosite ENUM('sombre','normal','lumineux','tres_lumineux'),
    calme ENUM('bruyant','normal','calme','tres_calme'),
    dpe CHAR(1),
    ges CHAR(1),

    -- Résultats estimation
    prix_estime DECIMAL(12, 2),
    prix_bas DECIMAL(12, 2),
    prix_haut DECIMAL(12, 2),
    prix_m2 DECIMAL(10, 2),
    prix_m2_reference DECIMAL(10, 2),
    methode_calcul VARCHAR(50),
    coefficients_appliques JSON,

    -- CRM / Pipeline
    lead_statut ENUM('nouveau','contacte','qualifie','estimation_rdv','mandat','perdu') DEFAULT 'nouveau',
    lead_score INT DEFAULT 0,
    lead_temperature ENUM('froid','tiede','chaud','brulant') DEFAULT 'tiede',
    agent_assigne INT,
    notes TEXT,

    -- RDV
    rdv_pris TINYINT(1) DEFAULT 0,
    rdv_date DATETIME,
    rdv_type ENUM('domicile','agence','visio'),
    rdv_notes TEXT,

    -- Tracking
    source VARCHAR(50) DEFAULT 'site',
    utm_source VARCHAR(100),
    utm_medium VARCHAR(100),
    utm_campaign VARCHAR(100),
    utm_term VARCHAR(100),
    utm_content VARCHAR(100),
    ip_address VARCHAR(45),
    user_agent TEXT,
    referer TEXT,

    -- Relances email
    email_resultat_envoye TINYINT(1) DEFAULT 0,
    email_relance_j3 TINYINT(1) DEFAULT 0,
    email_relance_j7 TINYINT(1) DEFAULT 0,
    email_relance_j14 TINYINT(1) DEFAULT 0,
    unsubscribed TINYINT(1) DEFAULT 0,

    -- RGPD
    rgpd_consent TINYINT(1) DEFAULT 0,
    rgpd_consent_date DATETIME,
    rgpd_ip VARCHAR(45),

    -- Index
    INDEX idx_email (email),
    INDEX idx_ville (ville),
    INDEX idx_created (created_at),
    INDEX idx_statut (lead_statut),
    INDEX idx_score (lead_score),
    INDEX idx_temperature (lead_temperature),
    INDEX idx_type_bien (type_bien),
    INDEX idx_rdv_pris (rdv_pris),
    INDEX idx_agent (agent_assigne),
    INDEX idx_reference (reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE : UTILISATEURS ADMIN
-- ================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','agent') DEFAULT 'agent',
    avatar_url VARCHAR(500),
    telephone VARCHAR(20),
    actif TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE : ADMIN AUTH (EMAIL + CODE)
-- ================================================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prenom VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    code_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    used_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_created (admin_id, created_at),
    INDEX idx_expires (expires_at),
    CONSTRAINT fk_admin_codes_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE : PRIX AU M² PAR VILLE
-- ================================================
CREATE TABLE IF NOT EXISTS villes_prix (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville VARCHAR(100) NOT NULL,
    code_postal VARCHAR(10),
    departement VARCHAR(5),
    region VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    population INT,
    distance_centre DECIMAL(6, 2) DEFAULT 0,

    -- Prix au m²
    prix_m2_appartement DECIMAL(10, 2),
    prix_m2_maison DECIMAL(10, 2),
    prix_m2_terrain DECIMAL(10, 2),

    -- Tendances
    tendance_annuelle DECIMAL(5, 2) DEFAULT 0,
    tendance_trimestrielle DECIMAL(5, 2) DEFAULT 0,

    -- Stats
    nb_transactions INT DEFAULT 0,
    prix_m2_min DECIMAL(10, 2),
    prix_m2_max DECIMAL(10, 2),

    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_ville (ville),
    INDEX idx_cp (code_postal),
    INDEX idx_distance (distance_centre),
    INDEX idx_coords (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE : PARAMÈTRES
-- ================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key),
    INDEX idx_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE : NOTES / ACTIVITÉS SUR LES LEADS
-- ================================================
CREATE TABLE IF NOT EXISTS lead_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estimation_id INT NOT NULL,
    user_id INT,
    type ENUM('note','appel','email','rdv','statut','score','systeme') DEFAULT 'note',
    contenu TEXT,
    metadata JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_estimation (estimation_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (estimation_id) REFERENCES estimations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE : SÉCURITÉ - RATE LIMITING
-- ================================================
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_action (ip, action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE : SÉCURITÉ - TENTATIVES DE LOGIN
-- ================================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    email VARCHAR(255),
    success TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip (ip),
    INDEX idx_email (email),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE : LOGS EMAILS
-- ================================================
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(500),
    template VARCHAR(100),
    status ENUM('sent','failed','bounced') DEFAULT 'sent',
    error_message TEXT,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipient (recipient),
    INDEX idx_template (template),
    INDEX idx_status (status),
    INDEX idx_sent (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE : LOGS WEBHOOKS
-- ================================================
CREATE TABLE IF NOT EXISTS webhook_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event VARCHAR(100) NOT NULL,
    url VARCHAR(500),
    payload TEXT,
    http_code INT,
    response TEXT,
    status ENUM('success','failed') DEFAULT 'failed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event (event),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE : SESSIONS ADMIN (optionnel, pour remember me)
-- ================================================
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip VARCHAR(45),
    user_agent TEXT,
    last_activity DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Tables complémentaires utilisées par le module admin avancé
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ads_checklist_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    step_key VARCHAR(100) NOT NULL,
    completed TINYINT(1) DEFAULT 0,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_admin_step (admin_id, step_key),
    INDEX idx_admin_id (admin_id),
    CONSTRAINT fk_ads_checklist_admin FOREIGN KEY (admin_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS google_ads_drafts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_type VARCHAR(30) NOT NULL,
    titres JSON NOT NULL,
    descriptions JSON NOT NULL,
    final_url VARCHAR(255) NOT NULL,
    path1 VARCHAR(15) DEFAULT '',
    path2 VARCHAR(15) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
