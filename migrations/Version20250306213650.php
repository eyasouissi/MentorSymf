<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306213650 extends AbstractMigration
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
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, user_id INT NOT NULL, content VARCHAR(2000) NOT NULL, created_at DATETIME NOT NULL, photo VARCHAR(255) DEFAULT NULL, INDEX IDX_9474526C4B89032C (post_id), INDEX IDX_9474526CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE courses (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_published TINYINT(1) DEFAULT 1 NOT NULL, progress_points_required INT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_premium TINYINT(1) DEFAULT 0 NOT NULL, tutor_name VARCHAR(255) DEFAULT NULL, INDEX IDX_A9A55A4C12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, id_annonce INT NOT NULL, titre_e VARCHAR(255) NOT NULL, description_e LONGTEXT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, image_e VARCHAR(255) DEFAULT NULL, INDEX IDX_B26681E28C83A95 (id_annonce), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, level_id INT DEFAULT NULL, file_name VARCHAR(255) NOT NULL, is_viewed TINYINT(1) NOT NULL, INDEX IDX_8C9F36105FB14BA7 (level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE forum (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, totalposts INT DEFAULT NULL, is_public TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, views INT NOT NULL, topics VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groupstudent (id INT AUTO_INCREMENT NOT NULL, nbr_members INT NOT NULL, description_group LONGTEXT DEFAULT NULL, nom_group VARCHAR(255) NOT NULL, date_creation_group DATE DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, date_meet DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_student_members (group_student_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1605FADE1C592EA8 (group_student_id), INDEX IDX_1605FADEA76ED395 (user_id), PRIMARY KEY(group_student_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, previous_level_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_complete TINYINT(1) NOT NULL, INDEX IDX_9AEACC13591CC992 (course_id), INDEX IDX_9AEACC13405A53A3 (previous_level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notif (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, triggered_by_id INT NOT NULL, post_id INT NOT NULL, type VARCHAR(20) NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C0730D6BA76ED395 (user_id), INDEX IDX_C0730D6B63C5923F (triggered_by_id), INDEX IDX_C0730D6B4B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offre (id_offre INT AUTO_INCREMENT NOT NULL, nom_offre VARCHAR(255) NOT NULL, image_offre VARCHAR(255) DEFAULT NULL, prix DOUBLE PRECISION NOT NULL, date_debut DATETIME NOT NULL, date_fin DATE DEFAULT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id_offre)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement (id_paiement INT AUTO_INCREMENT NOT NULL, id_offre INT NOT NULL, id_user INT NOT NULL, your_email VARCHAR(255) NOT NULL, card_num VARCHAR(255) NOT NULL, date_expiration DATETIME NOT NULL, cvv INT NOT NULL, INDEX IDX_B1DC7A1E4103C75F (id_offre), PRIMARY KEY(id_paiement)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, forum_id INT DEFAULT NULL, user_id INT NOT NULL, content VARCHAR(2000) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, likes INT NOT NULL, photos JSON DEFAULT NULL, gif_url VARCHAR(255) DEFAULT NULL, INDEX IDX_5A8A6C8D29CCBAD0 (forum_id), INDEX IDX_5A8A6C8DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_likes (post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_DED1C2924B89032C (post_id), INDEX IDX_DED1C292A76ED395 (user_id), PRIMARY KEY(post_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prohibited_word (id INT AUTO_INCREMENT NOT NULL, word VARCHAR(255) NOT NULL, category VARCHAR(50) NOT NULL, severity SMALLINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_E91FB342C3F17511 (word), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description_project LONGTEXT DEFAULT NULL, fichier_pdf VARCHAR(255) DEFAULT NULL, date_creation_project DATETIME NOT NULL, difficulte VARCHAR(255) NOT NULL, date_limite DATETIME DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rating (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, user_id INT NOT NULL, rating INT NOT NULL, INDEX IDX_D8892622591CC992 (course_id), INDEX IDX_D8892622A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reply (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, comment_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_FDA8C6E0A76ED395 (user_id), INDEX IDX_FDA8C6E0F8697D13 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, bio VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, gender VARCHAR(255) DEFAULT NULL, date_creation DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_verified TINYINT(1) DEFAULT NULL, password_reset_token VARCHAR(255) DEFAULT NULL, password_reset_requested_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', diplome VARCHAR(255) DEFAULT NULL, speciality VARCHAR(255) DEFAULT NULL, age INT DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, verification_token VARCHAR(255) DEFAULT NULL, pfp VARCHAR(255) DEFAULT NULL, bg VARCHAR(255) DEFAULT NULL, oauth_id VARCHAR(255) DEFAULT NULL, oauth_type VARCHAR(255) DEFAULT NULL, is_restricted TINYINT(1) NOT NULL, google_id VARCHAR(255) DEFAULT NULL, karma_points INT NOT NULL, locale VARCHAR(5) DEFAULT \'en\' NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_completed_levels (user_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_5C1B3D2AA76ED395 (user_id), INDEX IDX_5C1B3D2A5FB14BA7 (level_id), PRIMARY KEY(user_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE courses ADD CONSTRAINT FK_A9A55A4C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E28C83A95 FOREIGN KEY (id_annonce) REFERENCES annonce (id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36105FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE group_student_members ADD CONSTRAINT FK_1605FADE1C592EA8 FOREIGN KEY (group_student_id) REFERENCES groupstudent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_student_members ADD CONSTRAINT FK_1605FADEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT FK_9AEACC13591CC992 FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT FK_9AEACC13405A53A3 FOREIGN KEY (previous_level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE notif ADD CONSTRAINT FK_C0730D6BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notif ADD CONSTRAINT FK_C0730D6B63C5923F FOREIGN KEY (triggered_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notif ADD CONSTRAINT FK_C0730D6B4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E4103C75F FOREIGN KEY (id_offre) REFERENCES offre (id_offre) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_DED1C2924B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_DED1C292A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D8892622591CC992 FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D8892622A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E0F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE user_completed_levels ADD CONSTRAINT FK_5C1B3D2AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_completed_levels ADD CONSTRAINT FK_5C1B3D2A5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE courses DROP FOREIGN KEY FK_A9A55A4C12469DE2');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E28C83A95');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36105FB14BA7');
        $this->addSql('ALTER TABLE group_student_members DROP FOREIGN KEY FK_1605FADE1C592EA8');
        $this->addSql('ALTER TABLE group_student_members DROP FOREIGN KEY FK_1605FADEA76ED395');
        $this->addSql('ALTER TABLE level DROP FOREIGN KEY FK_9AEACC13591CC992');
        $this->addSql('ALTER TABLE level DROP FOREIGN KEY FK_9AEACC13405A53A3');
        $this->addSql('ALTER TABLE notif DROP FOREIGN KEY FK_C0730D6BA76ED395');
        $this->addSql('ALTER TABLE notif DROP FOREIGN KEY FK_C0730D6B63C5923F');
        $this->addSql('ALTER TABLE notif DROP FOREIGN KEY FK_C0730D6B4B89032C');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E4103C75F');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D29CCBAD0');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY FK_DED1C2924B89032C');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY FK_DED1C292A76ED395');
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D8892622591CC992');
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D8892622A76ED395');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E0A76ED395');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E0F8697D13');
        $this->addSql('ALTER TABLE user_completed_levels DROP FOREIGN KEY FK_5C1B3D2AA76ED395');
        $this->addSql('ALTER TABLE user_completed_levels DROP FOREIGN KEY FK_5C1B3D2A5FB14BA7');
        $this->addSql('DROP TABLE annonce');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE courses');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE forum');
        $this->addSql('DROP TABLE groupstudent');
        $this->addSql('DROP TABLE group_student_members');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE notif');
        $this->addSql('DROP TABLE offre');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_likes');
        $this->addSql('DROP TABLE prohibited_word');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE rating');
        $this->addSql('DROP TABLE reply');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_completed_levels');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
