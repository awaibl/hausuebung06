<?php

class User extends Database
{
	public $name = '';
	public $id = '';

	public $isLoggedIn = false;

	public function __construct()
	{
		parent::__construct();

		if($_SESSION[get_class($this).'Ship'] != '')
		{
			$ship = $_SESSION[get_class($this)."Ship"];
			$this->fillIt($ship);
		}
	}

	public function __destruct()
	{
		parent::__destruct();

		$_SESSION[get_class($this).'Ship'] = $this->shipIt();
	}

	public function authenticate()
	{
		//checks if the user is logged in - if not - redirect to login!
		if(!$this->isLoggedIn)
		{
			define('LOGGED_IN', false);

			$this->redirectToLogin();

			//return false;
		}

		define('LOGGED_IN', true);

		return true;
	}

	public function redirectToLogin()
	{
		header('Location: '.LOGIN_URL);
		header('Status: 303');
		exit();
	}

	public function redirectToIndex()
	{
		header('Location: '.INDEX_URL);
		header('Status: 303');
		exit();
	}

	public function login($username, $password)
	{
		$sql = "SELECT ´id´,´password´ FROM ´user´ WHERE ´name´='" . $this->escapeString($username) . "'";
		$result = $this->query($sql);


		if($this->numRows($result) == 0)
		{
			$this->isLoggedIn = false;
			return false; //username not found!
		}

		//now lets check for the password
		$row = $this->fetchObject($result);

		if(password_verify($password, $row->password) == $password)
		{
			$this->name = $username;
			$this->id = $row->id;
			$this->isLoggedIn = true;

			return true;
		}

		$this->isLoggedIn = false;
		return false;
	}

	public static function getById($id)
	{
		$id = intval($id);
		$sql = "SELECT * FROM user WHERE id=".$id;

		$db = new Database();
		$result = $db->query($sql);

		if($db->numRows($result) > 0)
		{
			//get the data
			$data = $db->fetchObject($result);
			$user = new User();

			$user->name = $data['name'];
			$user->id = $id;

			return $user;
		}

		return null;
	}

	public function logout()
	{
		$this->name = null;
		$this->id = null;
		$this->isLoggedIn = false;
		$this->shipIt();

		$this->redirectToLogin();

		return true;
	}

	/**
	 * Gets all attributes from this class, serializes it adds slahes to save this string in the session
	 * @return string
	 */
	protected function shipIt()
	{
		$ship = serialize($this);
		$ship = addslashes($ship);
		return $ship;
	}

	/**
	 * Fills this class with the data from the session which was previously saved
	 * @param $ship
	 */
	protected function fillIt($ship)
	{
		$ship = stripslashes($ship);
		$thiz = unserialize($ship);
		$ro = new reflectionObject($thiz);
		foreach ($ro->getProperties() as $propObj)
		{
			$this->{$propObj->name} = $thiz->{$propObj->name};
		}
	}

	public static function insertUser($username, $password)
	{
		$db = new Database();

		//check if user exists...
		$sql = "SELECT COUNT(ID) AS num FROM user WHERE name='".$db->escapeString($username)."'";
		$result = $db->query($sql);

		$row = $db->fetchAssoc($result);

		if($row->num == 0)
		{
			//execute insert...
		}
		else
		{
			return null; //user exists already!
		}
	}

	public static function deleteUser($id)
	{
		//@TODO
	}

	public static function updateUser($id, $data)
	{
		//@TODO
	}

	public function delete()
	{
		self::deleteUser($this->id);
	}

	public function update($data)
	{
		self::updateUser($this->id, $data);
	}
}

$user = new User();

if($loginRequired)
{
	$user->authenticate();
}