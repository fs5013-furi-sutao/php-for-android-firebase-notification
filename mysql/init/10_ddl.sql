set global local_infile = 1;

create table if not exists m_sample (
  `code` char(3) not null,
  `name` varchar(80) not null,
  primary key(`code`)
) engine=innodb default charset=utf8;

create table if not exists push_notification_history (
  `id` int auto_increment, 
  `push_type` varchar(80) not null,
  `title` varchar(80) not null,
  `message` varchar(80) not null,
  primary key(`id`)
) engine=innodb default charset=utf8;