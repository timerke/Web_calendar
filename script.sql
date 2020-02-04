-- Этот скрипт предназначен для создания и наполнения базы данных веб-календаря
-- Создается база данных calendar. С ней будет работать веб-календарь
drop database if exists calendar;

create database calendar;

use calendar;

-- Создается таблица users для хранения зарегистрированных пользователей
create table users(
    id serial primary key,
    name varchar(50) not null unique comment 'Имя пользователя',
    password varchar(20) not null comment 'Пароль пользователя'
) comment 'Таблица с зарегистрированными пользователями';

-- Создается таблица с личными напоминаниями пользователей
create table user_planner(
    id serial primary key,
    user_id bigint unsigned not null comment 'Вторичный ключ - ссылка на пользователя, кому принадлежит напоминание',
    event_date date not null comment 'Дата какого-то события для напоминания',
    event varchar(255) comment 'Пояснение, о чем напоминание',
    foreign key (user_id) references users (id) on delete cascade on update cascade
) comment 'Таблица с личными напоминаниями пользователей';

-- Создается таблица с группами для пользователей
create table parties(
    id serial primary key,
    name varchar(50) not null unique comment 'Название группы'
) comment 'Таблица с группами';

-- Создается таблица с пользователями, состоящими в группах
create table users_parties(
    user_id bigint unsigned not null comment 'Вторичный ключ - ссылка на пользователя',
    party_id bigint unsigned not null comment 'Вторичный ключ - ссылка на группу, в которой состоит пользователь',
    primary key (user_id, party_id) comment 'Первичный ключ таблицы включает два поля таблицы',
    foreign key (user_id) references users (id) on delete cascade on update cascade,
    foreign key (party_id) references parties (id) on delete cascade on update cascade
) comment 'Таблица с пользователями в группах';

-- Создается таблица с напоминаниями для пользователей в группах
create table parties_planner(
    id serial primary key,
    party_id bigint unsigned not null comment 'Вторичный ключ - ссылка на группу, в которой создано напоминание',
    event_date date not null comment 'Дата какого-то события для напоминания',
    event varchar(255) comment 'Пояснение, что за событие',
    foreign key (party_id) references parties (id) on delete cascade on update cascade
) comment 'Таблица с напоминаниями в группах';

-- Создается представление c id пользователей, id групп и названиями групп
create view user_parties_view as
    select users.id as user_id, parties.id, parties.name from users_parties left join users on users_parties.user_id=users.id
    left join parties on users_parties.party_id=parties.id;

-- Первичное заполнение таблиц, чтобы работать с веб-календарем было интересней
-- Создаются три пользователя
insert into users values
    (null, 'user1', '1'),
    (null, 'user2', '1'),
    (null, 'user3', '3');

-- Создаются три группы
insert into parties values
    (null, 'party1'),
    (null, 'party2'),
    (null, 'party3');

-- Пользователи вступают в группы: user1 в party1 и party2, user2 в party1 и party3, user3 в party2 и party3
insert into users_parties values
    (1, 1),
    (1, 2),
    (2, 1),
    (2, 3),
    (3, 2),
    (3, 3);

-- Создаются личные напоминания для user1, user2 и user3
insert into user_planner values
    (null, 1, '2019-12-01', 'встреча 1'),
    (null, 1, '2019-12-21', 'встреча 2'),
    (null, 2, '2019-12-29', 'встреча 3'),
    (null, 3, '2019-12-11', 'встреча 4');

-- Создаются групповые напоминания для group1, group2 и group3
insert into parties_planner values
    (null, 1, '2019-12-04', 'групповая встреча 1'),
    (null, 2, '2019-12-07', 'групповая встреча 2'),
    (null, 3, '2019-12-15', 'групповая встреча 3'),
    (null, 3, '2019-12-02', 'групповая встреча 4');

-- Создается учетная запись для администратора веб-календаря, который сможет делать запросы к таблицам базы данных calendar.
-- Имя учетной записи calendar_admin, пароль 1234
drop user if exists 'calendar_admin'@'localhost';
create user 'calendar_admin'@'localhost' identified by '1234';
-- Учетной записи позволено делать запросы select, вставлять данные в таблицы, изменять и удалять их из таблиц базы данных calendar
grant select, insert, update, delete
    on calendar.* to 'calendar_admin'@'localhost';