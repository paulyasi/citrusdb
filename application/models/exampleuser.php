<?php
class User extends CI_Model
{
	public function authenticate($username, $password)
	{
		//get salt
		$salt = $this->db->select('salt')->get_where('users', 
			array('username' => $username))->row()->salt;
			
		if ($salt)
		{
			// hash password with salt and find user
			$hash = sha1($salt.sha1($salt.$passw0rd));
			
			$user = $this->db->select('id')->get_where('users', 
				array('username' => $username, 
				'hash' -> $hash))->row();
			
			return $user;
		}
	}
}