#!/usr/bin/perl
use warnings;
use strict;
use DBI;

my $host = "localhost";
my $db1 = "webvj";
my $db2 = "vjspain";
my $user = "root";
my $pw = "bellota";

my $dbh = DBI->connect("dbi:mysql:database=$db1;host=$host", $user, $pw, {RaiseError => 1});

my $sql = "SELECT * FROM userlist";
my $sth = $dbh->prepare($sql);
$sth->execute();
my $row = $sth->fetchrow_hashref();

print $row;


