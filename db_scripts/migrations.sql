DROP DATABASE IF EXISTS converter;

CREATE DATABASE converter;

USE converter;

CREATE TABLE configs (
    id varchar(255) NOT NULL PRIMARY KEY,
    name varchar(255) NOT NULL,
    input_format varchar(255) NOT NULL,
    output_format varchar(255) NOT NULL,
    tabulation varchar(255)
);

CREATE TABLE users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transformations (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    config_id varchar(255) NOT NULL,
    file_name varchar(255) NOT NULL,
    output_file_name varchar(255) NOT NULL,
    input_file_name varchar(255) NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(config_id) REFERENCES configs(id)
);

CREATE TABLE shares (
    user_id INT NOT NULL,
    transformation_id INT NOT NULL,
    PRIMARY KEY(user_id, transformation_id),
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(transformation_id) REFERENCES transformations(id)
);
