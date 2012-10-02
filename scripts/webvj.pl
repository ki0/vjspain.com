#!/usr/bin/perl
use warnings;
use strict;
use DBI;
use Digest::MD5 qw( md5_hex );
use DateTime;

my $host = "localhost";
my $db1 = "webvj";
my $db2 = "vjspain";
my $db3 = "foro";
my $user = "root";
my $pw = "";

my $dbh1 = DBI->connect("dbi:mysql:database=$db1;host=$host", $user, $pw, {RaiseError => 1});
my $dbh2 = DBI->connect("dbi:mysql:database=$db2;host=$host", $user, $pw, {RaiseError => 1});
my $dbh3 = DBI->connect("dbi:mysql:database=$db3;host=$host", $user, $pw, {RaiseError => 1});

my $dt = DateTime->now();
my $date = $dt->ymd . " " . $dt->hms;

my $sel = $dbh1->prepare("SELECT id, usuario, clave, email FROM userlist");
$sel->execute();
while ( my $row = $sel->fetchrow_hashref ){
  print "$$row{'id'} $$row{'usuario'} $$row{'clave'} ", md5_hex($$row{'clave'}), " $date  $$row{'email'}\n";
  my $ins = $dbh2->prepare("INSERT INTO 
                            wp_users (ID, user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_activation_key, user_status, display_name, spam, deleted) 
                            values (?,?,?,?,?,?,?,?,?,?,?,?)");
  $ins->execute(3, $$row{'usuario'}, md5_hex($$row{'clave'}), $$row{'usuario'}, $$row{'email'}, "http://vjspain.com", $date, " ", 0, $$row{'usuario'}, 0, 0 );
}

my $del = $dbh1->prepare("DELETE FROM comunidad WHERE id = 30");
$del->execute();

$del = $dbh1->prepare("DELETE FROM comunidad WHERE visitas = 0 AND newsletter = 0");
$del->execute();

$sel = $dbh1->prepare("SELECT id, fechaAlta, nombre, apellidos, web, email, usuario, salasana  FROM comunidad WHERE 1");
$sel->execute();
while ( my $row = $sel->fetchrow_hashref ){
  print "$$row{'id'} $$row{'fechaAlta'} $$row{'nombre'} $$row{'apellidos'} $$row{'web'} $$row{'email'} $$row{'usuario'} $$row{'salasana'} ", md5_hex($$row{'salasana'}), "\n";
  my $ins = $dbh2->prepare("INSERT INTO 
                            wp_users (ID, user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_activation_key, user_status, display_name, spam, deleted) 
                            values (?,?,?,?,?,?,?,?,?,?,?,?)");
  $ins->execute($$row{'id'}, $$row{'usuario'}, md5_hex($$row{'salasana'}), $$row{'nombre'} . " " . $$row{'apellidos'}, $$row{'email'}, $$row{'web'}, $$row{'fechaAlta'}, " ", 0, $$row{'usuario'}, 0, 0 );
}
$dbh1->disconnect();
$dbh2->disconnect();
$dbh3->disconnect();
