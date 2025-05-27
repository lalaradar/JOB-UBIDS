CREATE DATABASE IF NOT EXISTS ubids_job;
USE ubids_job;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'job_seeker', 'employer') NOT NULL
);

CREATE TABLE jobs (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    city_id INT NOT NULL,
    FOREIGN KEY (city_id) REFERENCES city(city_id),
    country_id INT NOT NULL,
    FOREIGN KEY (country_id) REFERENCES country(country_id),
    job_category_id INT NOT NULL,
    FOREIGN KEY (job_category_id) REFERENCES job_category(job_category_id),
    salary VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    image VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
);

CREATE TABLE personal_record (
    personal_record_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city_id INT NOT NULL,
    FOREIGN KEY (city_id) REFERENCES city(city_id),
    country_id INT NOT NULL,
    FOREIGN KEY (country_id) REFERENCES country(country_id),
    education VARCHAR(255) NOT NULL,
    experience VARCHAR(255) NOT NULL,
    skills VARCHAR(255) NOT NULL,
    job_category_id INT NOT NULL,
    FOREIGN KEY (job_category_id) REFERENCES job_category(job_category_id),
    salary VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    job_id INT NOT NULL,
    FOREIGN KEY (job_id) REFERENCES jobs(job_id),

);

CREATE TABLE apply_job (
    apply_job_id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    FOREIGN KEY (job_id) REFERENCES jobs(job_id),
    personal_record_id INT NOT NULL,
    FOREIGN KEY (personal_record_id) REFERENCES personal_record(personal_record_id),
);

CREATE TABLE job_category (
    job_category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
);

-- เพิ่มข้อมูลหมวดหมู่งาน
INSERT INTO job_category (name, description) VALUES ('IT', 'IT');
INSERT INTO job_category (name, description) VALUES ('Marketing', 'Marketing');
INSERT INTO job_category (name, description) VALUES ('Sales', 'Sales');
INSERT INTO job_category (name, description) VALUES ('HR', 'HR');
INSERT INTO job_category (name, description) VALUES ('Finance', 'Finance');
INSERT INTO job_category (name, description) VALUES ('Engineering', 'Engineering');
INSERT INTO job_category (name, description) VALUES ('Design', 'Design');
INSERT INTO job_category (name, description) VALUES ('Education', 'Education');
INSERT INTO job_category (name, description) VALUES ('Healthcare', 'Healthcare');
INSERT INTO job_category (name, description) VALUES ('Legal', 'Legal');
INSERT INTO job_category (name, description) VALUES ('Other', 'Other');


CREATE TABLE city (
    city_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
);

-- เพิ่มข้อมูลจังหวัดในประเทศไทย
INSERT INTO city (name) VALUES ('กรุงเทพมหานคร');
INSERT INTO city (name) VALUES ('เชียงใหม่');
INSERT INTO city (name) VALUES ('ภูเก็ต');
INSERT INTO city (name) VALUES ('ขอนแก่น');
INSERT INTO city (name) VALUES ('ชลบุรี');
INSERT INTO city (name) VALUES ('นครราชสีมา');
INSERT INTO city (name) VALUES ('อุดรธานี');
INSERT INTO city (name) VALUES ('สงขลา');
INSERT INTO city (name) VALUES ('นครสวรรค์');
INSERT INTO city (name) VALUES ('อุบลราชธานี');
INSERT INTO city (name) VALUES ('พิษณุโลก');
INSERT INTO city (name) VALUES ('สุราษฎร์ธานี');
INSERT INTO city (name) VALUES ('นนทบุรี');
INSERT INTO city (name) VALUES ('ปทุมธานี');
INSERT INTO city (name) VALUES ('เชียงราย');


CREATE TABLE country (
    country_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
);

-- เพิ่มข้อมูลประเทศในเอเชียตะวันออกเฉียงใต้และประเทศสำคัญ
INSERT INTO country (name) VALUES ('ไทย');
INSERT INTO country (name) VALUES ('สิงคโปร์');
INSERT INTO country (name) VALUES ('มาเลเซีย');
INSERT INTO country (name) VALUES ('เวียดนาม');
INSERT INTO country (name) VALUES ('อินโดนีเซีย');
INSERT INTO country (name) VALUES ('ฟิลิปปินส์');
INSERT INTO country (name) VALUES ('กัมพูชา');
INSERT INTO country (name) VALUES ('พม่า');
INSERT INTO country (name) VALUES ('ลาว');
INSERT INTO country (name) VALUES ('บรูไน');
INSERT INTO country (name) VALUES ('ญี่ปุ่น');
INSERT INTO country (name) VALUES ('เกาหลีใต้');
INSERT INTO country (name) VALUES ('จีน');
INSERT INTO country (name) VALUES ('อินเดีย');
INSERT INTO country (name) VALUES ('ออสเตรเลีย');