-- Adminer 3.7.1 SQLite 3 dump

DROP TABLE IF EXISTS "entangled";
CREATE TABLE "entangled" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user_id" integer NOT NULL,
  "title" text NOT NULL,
  "created" text NOT NULL,
  "updated" text NULL,
  FOREIGN KEY ("user_id") REFERENCES "user" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "entangled_timeline";
CREATE TABLE "entangled_timeline" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "entangled_id" integer NOT NULL,
  "timeline_id" integer NOT NULL,
  "created" text NOT NULL,
  FOREIGN KEY ("entangled_id") REFERENCES "entangled" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("timeline_id") REFERENCES "timeline" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "event";
CREATE TABLE "event" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "timeline_id" integer NOT NULL,
  "location_id" integer NULL,
  "title" text NOT NULL,
  "description" text NULL,
  "date_from" text NOT NULL,
  "duration" numeric NULL,
  "duration_unit" text NULL,
  "date_to" text NULL,
  "anniversary" text NULL,
  "created" text NULL,
  "updated" text NULL,
  FOREIGN KEY ("location_id") REFERENCES "location" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("timeline_id") REFERENCES "timeline" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "location";
CREATE TABLE "location" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "title" text NOT NULL,
  "longitude" real NULL,
  "latitude" real NULL,
  "created" integer NOT NULL,
  "updated" integer NULL
);


DROP TABLE IF EXISTS "timeline";
CREATE TABLE "timeline" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user_id" integer NOT NULL,
  "name" text NOT NULL,
  "title" text NOT NULL,
  "timelines" text NULL,
  "created" text NOT NULL,
  "updated" text NULL,
  FOREIGN KEY ("user_id") REFERENCES "user" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("user_id") REFERENCES "user" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "user";
CREATE TABLE "user" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "username" text NOT NULL,
  "email" text NULL,
  "password" text NOT NULL,
  "realname" text NOT NULL,
  "created" integer NOT NULL,
  "updated" integer NULL
);


-- 
