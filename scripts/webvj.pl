#!/usr/bin/perl
use 5.012;
use utf8;
use warnings;
use strict;
use DBI;
use Digest::MD5 qw( md5_hex );
use DateTime;
use DateTime::Format::Strptime qw();
use Unicode::Normalize;
use Data::Dumper;
use MIME::Type qw( by_suffix );
binmode STDOUT, 'utf8';

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

my $del = $dbh2->prepare("DELETE FROM wp_posts WHERE 1");
$del->execute() or die $dbh2->errstr;

exportusers();
exportcategories();
exportposts();
exportcomments();
$dbh1->disconnect();
$dbh2->disconnect();
$dbh3->disconnect();

# Functions 

sub exportusers {
  my $sel = $dbh1->prepare("SELECT id, usuario, clave, email FROM userlist");
  $sel->execute() or die $dbh1->errstr;
  while ( my $row = $sel->fetchrow_hashref ){
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
    $ins->execute(3, $$row{'usuario'}, md5_hex($$row{'clave'}), $$row{'usuario'}, $$row{'email'}, "http://vjspain.com", $date, " ", 0, $$row{'usuario'}, 0, 0 );
  }

  my $del = $dbh1->prepare("DELETE FROM comunidad WHERE id = 30");
  $del->execute();
  
  $del = $dbh1->prepare("DELETE FROM comunidad WHERE visitas = 0 AND newsletter = 0");
  $del->execute();

  $del = $dbh1->prepare("DELETE FROM noticias_comentarios WHERE id_comunidad NOT IN (SELECT id FROM comunidad)");
  $del->execute();

  $sel = $dbh1->prepare("SELECT id, fechaAlta, nombre, apellidos, web, email, usuario, salasana  FROM comunidad WHERE 1");
  $sel->execute() or die $dbh1->errstr;
  while ( my $row = $sel->fetchrow_hashref ){
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
    $ins->execute($$row{'id'}, $$row{'usuario'}, md5_hex($$row{'salasana'}), $$row{'nombre'} . " " . $$row{'apellidos'}, $$row{'email'}, $$row{'web'}, $$row{'fechaAlta'}, " ", 0, $$row{'usuario'}, 0, 0 ) or die $dbh2->errstr;
  }
  return 1;
}

sub exportcategories {
  my $del = $dbh2->prepare("DELETE FROM wp_term_taxonomy WHERE 1");
  $del->execute() or die $dbh2->errstr;
  $del = $dbh2->prepare("DELETE FROM wp_terms WHERE 1");
  $del->execute() or die $dbh2->errstr;
  $del = $dbh2->prepare("DELETE FROM wp_term_relationships WHERE 1");
  $del->execute() or die $dbh2->errstr;

  my $sel = $dbh1->prepare("SELECT * FROM noticias_categorias WHERE 1");
  $sel->execute() or die $dbh1->errstr;
  while ( my $row = $sel->fetchrow_hashref ){
    print Dumper($$row{'categoria'});
    my $term = slugify($$row{'categoria'});
    $dbh2->do("REPLACE INTO wp_terms SET 
      term_id=?, 
      name=?, 
      slug=?, 
      term_group=?", 
      undef, $$row{'id'}, normalize($$row{'categoria'}), $term, 0) or die $dbh2->errstr;
    $dbh2->do("REPLACE INTO wp_term_taxonomy SET 
      term_taxonomy_id=?, 
      term_id=?, 
      taxonomy=?, 
      description=?, 
      parent=?, 
      count=?", 
      undef, undef, $$row{'id'}, 'category', '', 0, 0) or die $dbh2->errstr;
  }
  return 1;
}

sub exportposts {
  my $sel = $dbh1->prepare("SELECT * FROM noticias WHERE 1 ORDER BY id");
  $sel->execute();
  while ( my $row = $sel->fetchrow_hashref ){
    $$row{fecha} .= ' ' . '00:00:00';
    my $fecha = DateTime::Format::Strptime->new(
      pattern   => '%Y-%m-%d %T',
      time_zone => 'local',
      on_error  => 'croak',
    );
    my $content = addimg($$row{'id'}, $$row{'foto_url'}) unless !defined($$row{'foto_url'});
    $content .= '<br />' . normalize($$row{'texto'});
    $content .= '<br />' . $$row{'video'} unless ($$row{'video'} eq '' or !defined($$row{'video'}));
    $content .= '<br />' . '<a href="' . $$row{'enlace1'} . '">' . $$row{'enlace1'} unless $$row{'enlace1'} eq '';
    $content .= '<br />' . '<a href="' . $$row{'enlace2'} . '">' . $$row{'enlace2'} unless $$row{'enlace2'} eq '';
    linkcategory($$row{'id'}, $$row{'id_categoria'});
    addtags($$row{'id'}, $$row{'tags'});
    my $guid = $siteurl . "/" . slugify($$row{'titulo'}) . "/";
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
      undef, $$row{'id'}, 1, $fecha->parse_datetime($$row{'fecha'}), $fecha->parse_datetime($$row{'fecha'}), $content, normalize($$row{'titulo'}), '', 'publish', 'open', 'open', '', slugify($$row{'titulo'}), '', '', $date, $date, '', 0, $guid, 0, 'post', '', 0) or die $dbh2->errstr;
  }
  return 1;
}

sub exportcomments {
  my $upd = $dbh2->prepare("UPDATE wp_posts SET comment_count=0");
  $upd->execute() or die $dbh2->errstr;
  my $sel = $dbh1->prepare("SELECT * FROM noticias_comentarios WHERE publicado=1");
  $sel->execute() or die $dbh1->errstr;
  while ( my $row = $sel->fetchrow_hashref ){
    my $user = $dbh2->selectrow_hashref("SELECT * FROM wp_users WHERE ID=" . $$row{'id_comunidad'} . "");
    #print "user: " . Dumper($user);
    $$row{fecha} .= ' ' . '00:00:00';
    my $fecha = DateTime::Format::Strptime->new(
      pattern   => '%Y-%m-%d %T',
      time_zone => 'local',
      on_error  => 'croak',
    );
    my $ins = $dbh2->do("REPLACE INTO wp_comments SET 
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
      undef, $$row{'id'}, $$row{'id_noticia'}, $$user{'display_name'}, $$user{'user_email'}, $$user{'user_url'}, '', $fecha->parse_datetime($$row{'fecha'}), $fecha->parse_datetime($$row{'fecha'}), normalize($$row{'comentario'}), '', 1, '', '', '', $$row{'id_comunidad'} ) or die $dbh2->errstr;
    $upd = $dbh2->prepare("UPDATE wp_posts SET comment_count=comment_count+1 WHERE id = ".$$row{'id_noticia'}."");
    $upd->execute() or die $dbh2->errstr;
  }
  return 1;
}

sub linkcategory {
  my ($id, $category_id) = @_;

  my $term_tax = $dbh2->selectrow_hashref("SELECT term_taxonomy_id FROM wp_term_taxonomy WHERE term_id=". $category_id ."") or die $dbh2->errstr;
  my $ins = $dbh2->prepare("INSERT INTO wp_term_relationships(object_id, term_taxonomy_id, term_order) VALUES (?,?,?)");
  $ins->execute($id, $$term_tax{'term_taxonomy_id'}, 0) or die $dbh2->errstr;
  my $upd = $dbh2->prepare("UPDATE wp_term_taxonomy SET count=count+1 WHERE taxonomy='category' AND term_id = ". $category_id ."") or die $dbh2->errstr;
  $upd->execute();
  return;
}

sub addimg {
  my ($id, $img) = @_;
  my $guid = $siteurl . "/wp-content/uploads/2012/10/" . $img;
  #my $mimetype = by_suffix($filepath);
  #my $ins = $dbh2->do("REPLACE INTO wp_posts SET
  #  ID=?, 
  #  post_author=?, 
  #  post_date=?, 
  #  post_date_gmt=?, 
  #  post_content=?, 
  #  post_title=?, 
  #  post_excerpt=?, 
  #  post_status=?, 
  #  comment_status=?, 
  #  ping_status=?, 
  #  post_password=?, 
  #  post_name=?, 
  #  to_ping=?, 
  #  pinged=?, 
  #  post_modified=?, 
  #  post_modified_gmt=?, 
  #  post_content_filtered=?, 
  #  post_parent=?, 
  #  guid=?, 
  #  menu_order=?, 
  #  post_type=?, 
  #  post_mime_type=?, 
  #  comment_count=?", 
  #  undef, undef, 1, $date, $date, '', $img, '', 'inherit', 'open', 'open', '', $img, '', '', $date, $date, '', $id, $guid, 0, 'attachment', $mimetype, 0) or die $dbh2->errstr;
  my $content .= '<a href="'. $guid .'"><img class="aligncenter size-full wp-image-136" title="'.$img.'" src="'. $guid .'" alt="" width="297" height="297" /></a>';
  #print "Content: " . Dumper($content);
  return $content;
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
        $dbh2->do("REPLACE INTO wp_terms SET term_id=?, name=?, slug=?, term_group=?", undef, undef, normalize($tag), slugify($tag), 0) or die $dbh2->errstr;
        my $term = $dbh2->selectrow_hashref("SELECT term_id FROM wp_terms WHERE name=\'". normalize($tag) ."\' AND slug=\'". slugify($tag) ."\'") or die $dbh2->errstr;
        $dbh2->do("REPLACE INTO wp_term_taxonomy SET term_taxonomy_id=?, term_id=?, taxonomy=?, description=?, parent=?, count=?", undef, undef, $$term{'term_id'}, 'post_tag', '', 0, 1) or die $dbh2->errstr;
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
  #print "output: " . $input . "\n";
  utf8::encode($input);
  #print "output encode: " . $input . "\n";
  return $input;
}

sub normalize {
  my $input = shift(@_);

  utf8::decode($input);
  utf8::decode($input);
  #print "real: " . Dumper($input);
  $input = NFKD($input);
  #print "normalize: " . Dumper($input);
  $input =~ s/Ã3/ó/og;
  $input =~ s/Ã¡/á/og;
  $input =~ s/Ã©/é/og;
  $input =~ s/Ão/ú/og;
  $input =~ s/Ã±/ñ/og;
  $input =~ s/Ã/í/og;
  utf8::encode($input);
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
