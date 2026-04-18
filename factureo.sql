-- Création de la base
CREATE DATABASE IF NOT EXISTS factureo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE factureo;

-- Table administrateur
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- hashé
    nom VARCHAR(100) NOT NULL
);

-- Table clients
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    adresse VARCHAR(255),
    email VARCHAR(100),
    telephone VARCHAR(20),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table produits/services
CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    tva DECIMAL(5,2) NOT NULL DEFAULT 20.00
);

-- Table devis
CREATE TABLE devis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('brouillon','envoyé','accepté','refusé') DEFAULT 'brouillon',
    total_ht DECIMAL(10,2),
    total_tva DECIMAL(10,2),
    total_ttc DECIMAL(10,2),
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

-- Table factures
CREATE TABLE factures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    devis_id INT,
    client_id INT NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('payée','impayée') DEFAULT 'impayée',
    total_ht DECIMAL(10,2),
    total_tva DECIMAL(10,2),
    total_ttc DECIMAL(10,2),
    pdf_path VARCHAR(255),
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (devis_id) REFERENCES devis(id)
);

-- Table lignes de devis/factures
CREATE TABLE lignes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    facture_id INT,
    devis_id INT,
    produit_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    tva DECIMAL(5,2) NOT NULL,
    remise DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (facture_id) REFERENCES factures(id),
    FOREIGN KEY (devis_id) REFERENCES devis(id),
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);

-- Table paiements
CREATE TABLE paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    facture_id INT NOT NULL,
    date_paiement DATETIME,
    montant DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (facture_id) REFERENCES factures(id)
);

-- Table historique des actions
CREATE TABLE historique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(255) NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    utilisateur VARCHAR(50) NOT NULL
);

-- Ajout d’un admin par défaut (mot de passe à changer après installation) mot de passe : factureo123
INSERT INTO admin (email, password, nom) VALUES (
    'admin@factureo.com',
    '$2y$10$.vWrztPsr6kscZtTSz3GYe/DstiOO2nCmxi/hvgrP3izpPLYhJMSS',
    'Administrateur'
);