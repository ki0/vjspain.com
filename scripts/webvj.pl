#!/usr/bin/perl
use 5.012;
use utf8;
use warnings;
use strict;
use DBI;
use Digest::MD5 qw( md5_hex );
use DateTime;
use Unicode::Normalize;
use Data::Dumper;
binmode STDOUT, ':utf8';

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
our $date = $dt->ymd . " " . $dt->hms;

our $siteurl = $dbh2->selectrow_array("SELECT option_value FROM wp_options WHERE option_id = 1 AND option_name = 'siteurl'");

my $sel = $dbh1->prepare("SELECT id, usuario, clave, email FROM userlist");
$sel->execute() or die $dbh1->errstr;
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
$sel->execute() or die $dbh1->errstr;
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
#  $ins->execute($$row{'id'}, $$row{'usuario'}, md5_hex($$row{'salasana'}), $$row{'nombre'} . " " . $$row{'apellidos'}, $$row{'email'}, $$row{'web'}, $$row{'fechaAlta'}, " ", 0, $$row{'usuario'}, 0, 0 ) or die $dbh2->errstr;
}

$del = $dbh2->prepare("DELETE FROM wp_term_taxonomy WHERE 1");
$del->execute() or die $dbh2->errstr;

$sel = $dbh1->prepare("SELECT * FROM noticias_categorias WHERE 1");
$sel->execute() or die $dbh1->errstr;
while ( my $row = $sel->fetchrow_hashref ){
  print Dumper($$row{'categoria'}) . " " ;
  my $term = slugify($$row{'categoria'});
  #print  Dumper($term) . "\n";
  $dbh2->do("REPLACE INTO wp_terms SET term_id=?, name=?, slug=?, term_group=?", undef, $$row{'id'}, $$row{'categoria'}, $term, 0) or die $dbh2->errstr;
  $dbh2->do("REPLACE INTO wp_term_taxonomy SET term_taxonomy_id=?, term_id=?, taxonomy=?, description=?, parent=?, count=?", undef, undef, $$row{'id'}, 'category', '', 0, 0) or die $dbh2->errstr;
  #print nextid_wp('wp_term_taxonomy');
}

$sel = $dbh1->prepare("SELECT * FROM noticias WHERE 1 ORDER BY id");
$sel->execute();
while ( my $row = $sel->fetchrow_hashref ){
  my $content = addimg($$row{'id'}, $$row{'foto_url'}) unless !defined($$row{'foto_url'});
  #$content .= '<br />' . normalize($$row{'texto'});
  $content .= '<br />' .$$row{'texto'};
  $content .= '<br />' . $$row{'video'} unless ($$row{'video'} eq '' or !defined($$row{'video'}));
  $content .= '<br />' . '<a href="' . $$row{'enlace1'} . '">' . $$row{'enlace1'} unless $$row{'enlace1'} eq '';
  $content .= '<br />' . '<a href="' . $$row{'enlace2'} . '">' . $$row{'enlace2'} unless $$row{'enlace2'} eq '';
  addtags($$row{'id'}, $$row{'tags'});
  my $guid = $siteurl . "/" . slugify($$row{'titulo'}) . "/";
  print "guid: " . Dumper($guid);
  my $ins = $dbh2->do("REPLACE INTO wp_posts SET
    ID=?, 
    post_author=?, 
    post_date=?, 
    post_date_gmt=?, 
    post_content=?, 
    post_title=?, 
    post_excerpt=?, 
    post_status=?, 
    comment_status=?, 
    ping_status=?, 
    post_password=?, 
    post_name=?, 
    to_ping=?, 
    pinged=?, 
    post_modified=?, 
    post_modified_gmt=?, 
    post_content_filtered=?, 
    post_parent=?, 
    guid=?, 
    menu_order=?, 
    post_type=?, 
    post_mime_type=?, 
    comment_count=?", 
    undef, $$row{'id'}, 1, $date, $date, $content, normalize($$row{'titulo'}), '', 'publish', 'open', 'open', '', slugify($$row{'titulo'}), '', '', $date, $date, '', 0, $guid, 0, 'post', '', 0) or die $dbh2->errstr;
}

$sel = $dbh1->prepare("SELECT * FROM noticias_comentarios WHERE 1");
$sel->execute();
while ( my $row = $sel->fetchrow_hashref ){
  $sel = $dbh2->selectrow_hashref("SELECT * from wp_users WHERE ID=\'" . $$row{'id_comunidad'} . "\'") or die $dbh2->errstr;
  $ins = $dbh2->do("REPLACE INTO wp_comments SET 
    comment_ID=?, 
    comment_post_ID=?, 
    comment_author=?, 
    comment_author_email=?, 
    comment_author_url=?,
    comment_author_IP=?,
    comment_date=?,
    comment_date_gmt=?,
    comment_content=?,
    comment_karma=?,
    comment_approved=?,
    comment_agent=?,
    comment_type=?,
    comment_parent=?,
    user_id=?", 
    undef, undef, $$row{'id_noticia'}, $$sel{'user_nicename'}, $$sel{'user_mail'}, $$sel{'user_url'}, undef, $date, $date, $$row{'comentario'}, undef, undef, undef, undef, undef, $$row{'id_comunidad'} ) or die $dbh2->errstr;
}

$sel->finish();
$dbh1->disconnect();
$dbh2->disconnect();
$dbh3->disconnect();

sub addimg {
  my ($id, $img) = shift(@_);
#  print "$id $img\n";
  return;
}

sub addtags {
  my ($id, $input) = @_;

  if ( $input =~ /\r/ ){
    $input =~ tr/\r\n//d;
    $input =~ s/\s+/,/g;
    $input =~ s/.$//g;
  }
  my @values = split(',', $input);
  foreach my $tag (@values){
    $tag =~ s/^\s+|\s+$//g;
    if ( $tag gt '' ){
      #print "$id " . slugify($tag) ." /// $tag /// ". Dumper($tag);
      if (exit_wp( slugify($tag), "wp_terms", "slug")){
        my $sel = $dbh2->selectrow_hashref("SELECT term_id FROM wp_terms WHERE slug = \'". slugify($tag) ."\'") or die $dbh2->errstr;
        my $upd = $dbh2->prepare("UPDATE wp_term_taxonomy SET count=count+1 WHERE taxonomy='post_tag' AND term_id = ". $$sel{'term_id'} ."") or die $dbh2->errstr;
        $upd->execute();
      } else {
        $dbh2->do("REPLACE INTO wp_terms SET term_id=?, name=?, slug=?, term_group=?", undef, undef, $tag, slugify($tag), 0) or die $dbh2->errstr;
        my $term = $dbh2->selectrow_hashref("SELECT term_id FROM wp_terms WHERE name=\'". $tag ."\' AND slug=\'". slugify($tag) ."\'") or die $dbh2->errstr;
        $dbh2->do("REPLACE INTO wp_term_taxonomy SET term_taxonomy_id=?, term_id=?, taxonomy=?, description=?, parent=?, count=?", undef, undef, $$term{'term_id'}, 'post_tag', '', 0, 0) or die $dbh2->errstr;
        my $term_tax = $dbh2->selectrow_hashref("SELECT term_taxonomy_id FROM wp_term_taxonomy WHERE term_id=". $$term{'term_id'} ."") or die $dbh2->errstr;
        my $ins = $dbh2->prepare("INSERT INTO wp_term_relationships(object_id, term_taxonomy_id, term_order) VALUES (?,?,?)");
        $ins->execute($id, $$term_tax{'term_taxonomy_id'}, 0) or die $dbh2->errstr;
      }
    }
  }
  return 1;
}

sub slugify {
  my $input = shift(@_);
  
  utf8::decode($input);
  #print "real:" . Dumper($input);
  $input = NFKD($input);
  #print "normalize: " . Dumper($input);
  $input =~ s/\pM//og;
  #print "subtitute: " . Dumper($input);
  $input =~ s/[^\w\s-]//g;
  $input =~ s/^\s+|\s+$//g;
  $input = lc($input);
  $input =~ s/[-\s]+/-/g;
  return $input;
}

sub normalize {
  my $input = shift(@_);

  utf8::decode($input);
  print "real: " . Dumper($input);
  $input = NFD($input);
  print "normalize: " . Dumper($input);
  utf8::encode($input);
  print "encode: " . Dumper($input);
  return $input;
}

sub nextid_wp {
  my ($id, $table) = @_;

  my $sel = $dbh2->prepare("SELECT MAX(" . $id . ") FROM " . $table . " WHERE 1") or die $dbh2->errstr;
  $sel->execute();
  return $sel->rows + 1;
}

sub exit_wp {
  my ($tag, $table, $col) = @_;

  my $sel = $dbh2->prepare("SELECT * FROM " . $table . " WHERE " . $col . " = \'" . $tag ."\'") or die $dbh2->errstr;
  $sel->execute();
  return 1 if $sel->rows >= 1;
  return 0;
}
1;
