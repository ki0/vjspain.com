#!/usr/bin/perl
use warnings;
use strict;
use DBI;

my $host = "localhost";
my $db1 = "webvj";
my $db2 = "vjspain";
my $db3 = "foro";
my $user = "root";
my $pw = "";

my $dbh1 = DBI->connect("dbi:mysql:database=$db1;host=$host", $user, $pw, {RaiseError => 1});
my $dbh2 = DBI->connect("dbi:mysql:database=$db2;host=$host", $user, $pw, {RaiseError => 1});
my $dbh3 = DBI->connect("dbi:mysql:database=$db3;host=$host", $user, $pw, {RaiseError => 1});

my $sth = $dbh1->prepare("SELECT * FROM userlist");
$sth->execute();
while ( my @row = $sth->fetchrow_array ){
  print "@row\n";
}

$sth = $dbh3->prepare("DELETE FROM phpbb_users WHERE user_posts = 0 and user_lastvisit = 0");
$sth->execute();

$sth = $dbh1->prepare("DELETE FROM comunidad WHERE visitas = 0 AND newsletter = 0");
$sth->execute();

$sth = $dbh1->prepare("SELECT fechaAlta, nombre, apellidos, web, email, usuario, salasana  FROM comunidad WHERE 1");
$sth->execute();
while ( my @row = $sth->fetchrow_array ){
  print "@row\n";
}
$dbh1->disconnect;
$dbh2->disconnect;
$dbh3->disconnect;

