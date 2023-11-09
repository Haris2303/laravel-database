CREATE TABLE counters(
    id VARCHAR(100) NOT NULL PRIMARY KEY,
    counter INT NOT NULL DEFAULT 0
) ENGINE innodb;

INSERT INTO counters (id, counter) VALUES('sample', 0);

CREATE TABLE products
(
    id VARCHAR(100) NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    price INT NOT NULL, 
    category_id VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp,
    CONSTRAINT fk_category_id FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE INNODB;

SELECT * FROM products;

SHOW TABLES;

DESC categories;

DELETE FROM products;