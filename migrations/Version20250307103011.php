<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250307103011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, user_id INT NOT NULL, content VARCHAR(2000) NOT NULL, created_at DATETIME NOT NULL, photo VARCHAR(255) DEFAULT NULL, INDEX IDX_9474526C4B89032C (post_id), INDEX IDX_9474526CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notif (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, triggered_by_id INT NOT NULL, post_id INT NOT NULL, type VARCHAR(20) NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C0730D6BA76ED395 (user_id), INDEX IDX_C0730D6B63C5923F (triggered_by_id), INDEX IDX_C0730D6B4B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_likes (post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_DED1C2924B89032C (post_id), INDEX IDX_DED1C292A76ED395 (user_id), PRIMARY KEY(post_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prohibited_word (id INT AUTO_INCREMENT NOT NULL, word VARCHAR(255) NOT NULL, category VARCHAR(50) NOT NULL, severity SMALLINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_E91FB342C3F17511 (word), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rating (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, user_id INT NOT NULL, rating INT NOT NULL, INDEX IDX_D8892622591CC992 (course_id), INDEX IDX_D8892622A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reply (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, comment_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_FDA8C6E0A76ED395 (user_id), INDEX IDX_FDA8C6E0F8697D13 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_completed_levels (user_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_5C1B3D2AA76ED395 (user_id), INDEX IDX_5C1B3D2A5FB14BA7 (level_id), PRIMARY KEY(user_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notif ADD CONSTRAINT FK_C0730D6BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notif ADD CONSTRAINT FK_C0730D6B63C5923F FOREIGN KEY (triggered_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notif ADD CONSTRAINT FK_C0730D6B4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_DED1C2924B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_DED1C292A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D8892622591CC992 FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D8892622A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E0F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE user_completed_levels ADD CONSTRAINT FK_5C1B3D2AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_completed_levels ADD CONSTRAINT FK_5C1B3D2A5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE courses ADD is_premium TINYINT(1) DEFAULT 0 NOT NULL, ADD tutor_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE level ADD previous_level_id INT DEFAULT NULL, ADD is_complete TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT FK_9AEACC13405A53A3 FOREIGN KEY (previous_level_id) REFERENCES level (id)');
        $this->addSql('CREATE INDEX IDX_9AEACC13405A53A3 ON level (previous_level_id)');
        $this->addSql('ALTER TABLE paiement DROP your_email, DROP card_num, DROP cvv, CHANGE date_expiration date_paiement DATETIME NOT NULL');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B1DC7A1E6B3CA4B ON paiement (id_user)');
        $this->addSql('ALTER TABLE post ADD user_id INT NOT NULL, ADD photos JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE content content VARCHAR(2000) NOT NULL, CHANGE photo gif_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DA76ED395 ON post (user_id)');
        $this->addSql('ALTER TABLE user ADD oauth_id VARCHAR(255) DEFAULT NULL, ADD oauth_type VARCHAR(255) DEFAULT NULL, ADD is_restricted TINYINT(1) NOT NULL, ADD google_id VARCHAR(255) DEFAULT NULL, ADD karma_points INT NOT NULL, ADD locale VARCHAR(5) DEFAULT \'en\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE notif DROP FOREIGN KEY FK_C0730D6BA76ED395');
        $this->addSql('ALTER TABLE notif DROP FOREIGN KEY FK_C0730D6B63C5923F');
        $this->addSql('ALTER TABLE notif DROP FOREIGN KEY FK_C0730D6B4B89032C');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY FK_DED1C2924B89032C');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY FK_DED1C292A76ED395');
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D8892622591CC992');
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D8892622A76ED395');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E0A76ED395');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E0F8697D13');
        $this->addSql('ALTER TABLE user_completed_levels DROP FOREIGN KEY FK_5C1B3D2AA76ED395');
        $this->addSql('ALTER TABLE user_completed_levels DROP FOREIGN KEY FK_5C1B3D2A5FB14BA7');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE notif');
        $this->addSql('DROP TABLE post_likes');
        $this->addSql('DROP TABLE prohibited_word');
        $this->addSql('DROP TABLE rating');
        $this->addSql('DROP TABLE reply');
        $this->addSql('DROP TABLE user_completed_levels');
        $this->addSql('ALTER TABLE courses DROP is_premium, DROP tutor_name');
        $this->addSql('ALTER TABLE level DROP FOREIGN KEY FK_9AEACC13405A53A3');
        $this->addSql('DROP INDEX IDX_9AEACC13405A53A3 ON level');
        $this->addSql('ALTER TABLE level DROP previous_level_id, DROP is_complete');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E6B3CA4B');
        $this->addSql('DROP INDEX IDX_B1DC7A1E6B3CA4B ON paiement');
        $this->addSql('ALTER TABLE paiement ADD your_email VARCHAR(255) NOT NULL, ADD card_num VARCHAR(255) NOT NULL, ADD cvv INT NOT NULL, CHANGE date_paiement date_expiration DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395');
        $this->addSql('DROP INDEX IDX_5A8A6C8DA76ED395 ON post');
        $this->addSql('ALTER TABLE post DROP user_id, DROP photos, CHANGE content content VARCHAR(255) NOT NULL, CHANGE gif_url photo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user DROP oauth_id, DROP oauth_type, DROP is_restricted, DROP google_id, DROP karma_points, DROP locale');
    }
}
