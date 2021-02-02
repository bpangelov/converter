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

CREATE TABLE transformations (
    config_id varchar(255) NOT NULL,
    file_name varchar(255) NOT NULL,
    CONSTRAINT pk_primary_key PRIMARY KEY (config_id,file_name)
);

CREATE TABLE users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);