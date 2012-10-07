#!/usr/bin/perl
use 5.012;
use utf8;
use warnings;
use strict;
use DBI;
use Digest::MD5 qw( md5_hex );
use DateTime;
use Text::Unaccent;
use Unicode::Normalize;

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
  #print "$$row{'id'} $$row{'usuario'} $$row{'clave'} ", md5_hex($$row{'clave'}), " $date  $$row{'email'}\n";
  my $ins = $dbh2->prepare("INSERT INTO wp_users (
                            ID, 
                            user_login, 
                            user_pass, 
                            user_nicename, 
                            user_email, 
                            user_url, 
                            user_registered, 
                            user_activation_key, 
                            user_status, 
                            display_name, 
                            spam, 
                            deleted) 
                            values (?,?,?,?,?,?,?,?,?,?,?,?)");
#  $ins->execute(3, $$row{'usuario'}, md5_hex($$row{'clave'}), $$row{'usuario'}, $$row{'email'}, "http://vjspain.com", $date, " ", 0, $$row{'usuario'}, 0, 0 );
}

my $del = $dbh1->prepare("DELETE FROM comunidad WHERE id = 30");
$del->execute();

$del = $dbh1->prepare("DELETE FROM comunidad WHERE visitas = 0 AND newsletter = 0");
$del->execute();

$sel = $dbh1->prepare("SELECT id, fechaAlta, nombre, apellidos, web, email, usuario, salasana  FROM comunidad WHERE 1");
$sel->execute();
while ( my $row = $sel->fetchrow_hashref ){
  #print "$$row{'id'} $$row{'fechaAlta'} $$row{'nombre'} $$row{'apellidos'} $$row{'web'} $$row{'email'} $$row{'usuario'} $$row{'salasana'} ", md5_hex($$row{'salasana'}), "\n";
  my $ins = $dbh2->prepare("INSERT INTO wp_users (
                            ID, 
                            user_login, 
                            user_pass, 
                            user_nicename, 
                            user_email, 
                            user_url, 
                            user_registered, 
                            user_activation_key, 
                            user_status, 
                            display_name, 
                            spam, 
                            deleted) 
                            values (?,?,?,?,?,?,?,?,?,?,?,?)");
#  $ins->execute($$row{'id'}, $$row{'usuario'}, md5_hex($$row{'salasana'}), $$row{'nombre'} . " " . $$row{'apellidos'}, $$row{'email'}, $$row{'web'}, $$row{'fechaAlta'}, " ", 0, $$row{'usuario'}, 0, 0 );
}

$del = $dbh2->prepare("DELETE FROM wp_terms WHERE 1");
#$del->execute();
$del = $dbh2->prepare("DELETE FROM wp_term_taxonomy WHERE 1");
#$del->execute();

$sel = $dbh1->prepare("SELECT * FROM noticias_categorias WHERE 1");
$sel->execute();
while ( my $row = $sel->fetchrow_hashref ){
  #print "$$row{'id'}";
  my $term = slugify($$row{'categoria'});
  #print " $term\n";
  my $ins = $dbh2->prepare("INSERT INTO wp_terms (term_id, name, slug, term_group) values (?,?,?,?)");
  #$ins->execute($$row{'id'}, $$row{'categoria'}, $term, 0 );
  $ins = $dbh2->prepare("INSERT INTO wp_term_taxonomy (term_taxonomy_id, term_id, taxonomy, description, parent, count) values (?,?,?,?,?,?)");
  #print nextid_wp('wp_term_taxonomy');
  #$ins->execute(nextid_wp('wp_term_taxonomy'), $$row{'id'}, "category", " ", 0, 0);
}

$sel = $dbh1->prepare("SELECT * FROM noticias WHERE 1");
$sel->execute();
while ( my $row = $sel->fetchrow_hashref ){
  
#  my $ins = $dbh2->prepare("INSERT INTO wp_post (
#                            ID, 
#                            post_autor, 
#                            post_date, 
#                            post_date_gmt, 
#                            post_content, 
#                            post_title, 
#                            post_excerpt, 
#                            post_status, 
#                            comment_status, 
#                            ping_status, 
#                            post_password, 
#                            post_name, 
#                            to_ping, 
#                            pinged, 
#                            post_modified, 
#                            post_modified_gmt, 
#                            post_content_filtered, 
#                            post_parent, 
#                            guid, 
#                            menu_order, 
#                            post_type, 
#                            post_mime_type, 
#                            comment_count) 
#                            values (?,?,?,?,?,?,?,?,?,?,?,?)");
#   $ins->execute($$row{'id'}, 1, $$row{'post_date'}, $$row{'post_date_gmt'}, $$row{'post_content'}, $$row{'post_title'}, $$row{'post_excerpt'}, $$row{'post_status4'}, " ", 0, $$row{'usuario'}, 0, 0 );
}
$dbh1->disconnect();
$dbh2->disconnect();
$dbh3->disconnect();

sub slugify {
  my $input = shift(@_);
  
  $input = unac_string('UTF8', $input);
  $input = NFKD($input);
  $input =~ tr/\000-\177//cd;
  $input =~ s/[^\w\s-]//g;
  $input =~ s/^\s+|\s+$//g;
  $input = lc($input);
  $input =~s/[-\s]+/-/g;
  return $input;
}

sub nextid_wp {
  my $input = shift(@_);

  my $sel = $dbh2->prepare("SELECT * FROM " . $input . " WHERE 1");
  $sel->execute();
  return $sel->rows + 1;
}
1;
