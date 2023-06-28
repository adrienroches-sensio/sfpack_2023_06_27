#!/usr/bin/env bash

symfony console doctrine:database:drop --force;
symfony console doctrine:database:create;
symfony console doctrine:migration:migrate -n;

symfony console doctrine:query:sql \
  "INSERT INTO movie (title, slug, plot, poster, released_at) VALUES ('Astérix et Obélix: Mission Cléopâtre', '2002-asterix-et-obelix-mission-cleopatre', 'plot', '2002-asterix-et-obelix-mission-cleopatre.png', '2002-01-30 00:00:00')" \
;
symfony console doctrine:query:sql \
  "INSERT INTO movie (title, slug, plot, poster, released_at) VALUES ('Le sens de la fête', '2017-le-sens-de-la-fete', 'plot', '2017-le-sens-de-la-fete.png', '2017-10-04 00:00:00')" \
;
symfony console doctrine:query:sql \
  "INSERT INTO movie (title, slug, plot, poster, released_at) VALUES ('Avatar', '2017-avatar', 'plot', '2009-avatar.png', '2009-12-16 00:00:00')" \
;
symfony console doctrine:query:sql \
  "INSERT INTO movie (title, slug, plot, poster, released_at) VALUES ('Une Merveilleuse Histoire du Temps', '2015-une-merveille-histoire-du-temps', 'plot', '2015-une-merveille-histoire-du-temps.png', '2015-01-21 00:00:00')" \
;

symfony console doctrine:query:sql "INSERT INTO Genre (name) VALUES ('Comedy'), ('Famille'), ('Biopic'), ('Drame')";
