CREATE DATABASE IF NOT EXISTS app;
use app;

/*Festivos fijos en Catalunya*/
CREATE TABLE IF NOT EXISTS festivos(
dia date,
primary key(dia)
)
collate=utf8_general_ci,
engine=InnoDB;

/* ---Festivos Catalunya 2014--- */
/*INSERT INTO festivos SET dia = '2014-01-01';
INSERT INTO festivos SET dia = '2014-01-06';
INSERT INTO festivos SET dia = '2014-04-18';
INSERT INTO festivos SET dia = '2014-04-21';
INSERT INTO festivos SET dia = '2014-05-01';
INSERT INTO festivos SET dia = '2014-06-09';
INSERT INTO festivos SET dia = '2014-06-24';
INSERT INTO festivos SET dia = '2014-07-16';
INSERT INTO festivos SET dia = '2014-08-15';
INSERT INTO festivos SET dia = '2014-09-11';
INSERT INTO festivos SET dia = '2014-09-24';
INSERT INTO festivos SET dia = '2014-11-01';
INSERT INTO festivos SET dia = '2014-12-06';
INSERT INTO festivos SET dia = '2014-12-08';
INSERT INTO festivos SET dia = '2014-12-25';
INSERT INTO festivos SET dia = '2014-12-26';
*/

/*Usuarios que accederán a la aplicación*/
CREATE TABLE IF NOT EXISTS users(
id int(5) auto_increment,
name varchar(30),
password varchar(64) not null,
primary key(id)
)
collate=utf8_general_ci,
engine=InnoDB;

/*Nombres de los planes de acción*/
CREATE TABLE IF NOT EXISTS plan(
id int(4) auto_increment,
name varchar(45) not null,
description varchar(140),
str_date datetime not null,
primary key(id)
)
collate=utf8_general_ci,
engine=InnoDB;

/*Calendario de los planes de acción*/
CREATE TABLE IF NOT EXISTS plan_period(
id int(5),
id_period int(3),
str_date datetime not null,
fin_date datetime not null,
goal decimal(5,2),
primary key(id, id_period),
 INDEX fk_id (id),
  FOREIGN KEY (id) 
    REFERENCES plan(id) 
    ON DELETE CASCADE
)
collate=utf8_general_ci,
engine=InnoDB;


