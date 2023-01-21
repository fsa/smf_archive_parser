CREATE TABLE categories (
    id int PRIMARY KEY GENERATED BY DEFAULT AS IDENTITY,
    category_order int,
    name text NOT NULL,
    description text
);
CREATE INDEX categories_order ON categories USING btree (category_order);

CREATE TABLE boards (
  id int PRIMARY KEY GENERATED BY DEFAULT AS IDENTITY,
  category_id int REFERENCES categories (id),
  parent_id int REFERENCES boards (id),
  board_order int,
  name text NOT NULL,
  description text,
  num_topics int,
	num_posts int
);
CREATE INDEX boards_order ON boards USING btree (board_order);

CREATE TABLE message_icons (
  id int PRIMARY KEY GENERATED BY DEFAULT AS IDENTITY,
  title text,
  filename text,
  icon_order int
);
CREATE INDEX message_icons_order ON message_icons USING btree (icon_order);

INSERT INTO message_icons (filename, title, icon_order)
VALUES
('xx', 'Standard', 0),
('thumbup', 'Thumb Up', 1),
('thumbdown', 'Thumb Down', 2),
('exclamation', 'Exclamation point', 3),
('question', 'Question mark', 4),
('lamp', 'Lamp', 5),
('smiley', 'Smiley', 6),
('angry', 'Angry', 7),
('cheesy', 'Cheesy', 8),
('grin', 'Grin', 9),
('sad', 'Sad', 10),
('wink', 'Wink', 11);

CREATE TABLE members (
  id int PRIMARY KEY GENERATED BY DEFAULT AS IDENTITY,
  name text,
  full_name text
);

CREATE TABLE topics (
  id int PRIMARY KEY GENERATED BY DEFAULT AS IDENTITY,
  is_sticky boolean,
  board_id int REFERENCES boards (id),
  first_msg_id int,
  last_msg_id int,
  started_member_id int REFERENCES members (id),
  started_member_name text,
  updated_member_id int REFERENCES members (id),
  updated_member_name text,
  title text,
  num_replies int,
  num_views int,
  last_modified timestamptz
);

CREATE TABLE messages (
  id int PRIMARY KEY GENERATED BY DEFAULT AS IDENTITY,
  topic_id int REFERENCES topics (id),
  posted timestamptz,
  member_id int REFERENCES members (id),
  subject text,
  poster_name text,
  body text NOT NULL,
  icon varchar(16) NOT NULL DEFAULT 'xx'
);