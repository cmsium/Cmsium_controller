create table files_users
(
  file_id char(32) not null,
  user_id char(32) not null,
  constraint files_users_pk
    primary key (file_id)
);
