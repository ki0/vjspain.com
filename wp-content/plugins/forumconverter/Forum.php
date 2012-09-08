<?php
/*  Copyright 2011 Orson Teodoro (orsonteodoro@yahoo.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	class Forum
	{
		public $hostname;
		public $databasename;
		public $username;
		public $password;
		public $prefix;
		public $type;
		public $version;
		public $uploadpath;
		public $avatarpath;
		public $options;
		
		public function __construct($hostname, $databasename, $username, $password, $prefix, $type, $version, $uploadpath, $avatarpath, $options)
		{
			$this->hostname = $hostname;
			$this->databasename = $databasename; 
			$this->username = $username;
			$this->password = $password;
			$this->prefix = $prefix;
			$this->type = $type;
			$this->version = $version;
			$this->uploadpath = $uploadpath;
			$this->avatarpath = $avatarpath;
			$this->options = $options;
		}
	}
?>
