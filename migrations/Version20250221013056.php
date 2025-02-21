<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221013056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE annonce (id INT AUTO_INCREMENT NOT NULL, image_a VARCHAR(255) NOT NULL, titre_a VARCHAR(255) NOT NULL, description_a LONGTEXT NOT NULL, date_a DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, icon VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE courses (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_published TINYINT(1) DEFAULT 1 NOT NULL, progress_points_required INT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A9A55A4C12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, id_annonce INT NOT NULL, titre_e VARCHAR(255) NOT NULL, description_e LONGTEXT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, image_e VARCHAR(255) DEFAULT NULL, INDEX IDX_B26681E28C83A95 (id_annonce), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, level_id INT DEFAULT NULL, file_name VARCHAR(255) NOT NULL, is_viewed TINYINT(1) NOT NULL, INDEX IDX_8C9F36105FB14BA7 (level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE forum (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, totalposts INT DEFAULT NULL, is_public TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, views INT NOT NULL, topics VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, id_group INT NOT NULL, description_group LONGTEXT DEFAULT NULL, nom_createur VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, date_creation_group DATE NOT NULL, image VARCHAR(255) DEFAULT NULL, date_meet DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_9AEACC13591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offre (id_offre INT AUTO_INCREMENT NOT NULL, nom_offre VARCHAR(255) NOT NULL, image_offre VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, date_debut DATETIME NOT NULL, date_fin DATE DEFAULT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id_offre)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement (id_paiement INT AUTO_INCREMENT NOT NULL, id_offre INT NOT NULL, id_user INT NOT NULL, your_email VARCHAR(255) NOT NULL, card_num VARCHAR(255) NOT NULL, date_expiration DATETIME NOT NULL, cvv INT NOT NULL, INDEX IDX_B1DC7A1E4103C75F (id_offre), PRIMARY KEY(id_paiement)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, forum_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, likes INT NOT NULL, photo VARCHAR(255) DEFAULT NULL, INDEX IDX_5A8A6C8D29CCBAD0 (forum_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description_project LONGTEXT DEFAULT NULL, date_creation_project DATETIME NOT NULL, difficulte VARCHAR(255) NOT NULL, date_limite DATETIME DEFAULT NULL, fichier_pdf VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, bio VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, gender VARCHAR(255) DEFAULT NULL, date_creation DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_verified TINYINT(1) DEFAULT NULL, password_reset_token VARCHAR(255) DEFAULT NULL, password_reset_requested_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', diplome VARCHAR(255) DEFAULT NULL, speciality VARCHAR(255) DEFAULT NULL, age INT DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, verification_token VARCHAR(255) DEFAULT NULL, pfp VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE courses ADD CONSTRAINT FK_A9A55A4C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E28C83A95 FOREIGN KEY (id_annonce) REFERENCES annonce (id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36105FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT FK_9AEACC13591CC992 FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E4103C75F FOREIGN KEY (id_offre) REFERENCES offre (id_offre) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE courses DROP FOREIGN KEY FK_A9A55A4C12469DE2');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E28C83A95');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36105FB14BA7');
        $this->addSql('ALTER TABLE level DROP FOREIGN KEY FK_9AEACC13591CC992');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E4103C75F');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D29CCBAD0');
        $this->addSql('DROP TABLE annonce');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE courses');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE forum');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE offre');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
