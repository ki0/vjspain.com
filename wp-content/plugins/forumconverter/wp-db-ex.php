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

require( dirname(__FILE__) . '../../../../wp-load.php');

class wpdbex extends wpdb 
{
	function checkConnection()
	{
		$tries = 0;
		$disconnected = false;
		
		if (@mysql_ping($this->dbh) === FALSE)
			$disconnected = true;
		
		while (@mysql_ping($this->dbh) === FALSE && $tries < 3)
		{
			@mysql_close($this->dbh);
			sleep(60);
			$this->fc_echo('reconnecting...<br />');
			$this->db_connect();
			$tries++;
		}

		if (@mysql_ping($this->dbh) === TRUE && $disconnected == true)
			$this->fc_echo('reconnect success<br />');
		
		return @mysql_ping($this->dbh) === TRUE;
	}
	
	function disconnect()
	{
		$this->fc_echo('disconnecting<br />');
		@mysql_close($this->dbh);
	}
	
	
	function query($query)
	{
		if ($this->checkConnection() === FALSE)
			$this->fc_die('reconnect failed');
		return parent::query($query);
	}
	
	private function fc_echo($message)
	{
		echo '<script type="text/javascript">jQuery("#fc_message").append("'.str_replace('\\','\\\\',$message).'"); jQuery("#fc_message").scrollTop(jQuery("#fc_message")[0].scrollHeight);</script>';
	}

	public function fc_die($message)
	{
		echo $message;
		echo '<script type="text/javascript">jQuery("#fc_message-status").removeClass("updated"); jQuery("#fc_message-status").addClass("error"); jQuery("#fc_message-status").text("Conversion failed");</script>';
		die();
	}
}
?>
