CREATE DATABASE student_results;
USE student_results;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL
);

CREATE TABLE results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    module_code VARCHAR(20) NOT NULL,
    CA INT NOT NULL,
    SE INT NOT NULL,
    TOT INT NOT NULL,
    GRD VARCHAR(2) NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
