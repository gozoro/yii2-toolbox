<?php



namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;


use yii\web\IdentityInterface;

/**
 * Console commands for RBAC management
 *
 */
abstract class RbacController extends Controller
{
    public $defaultAction = 'show';


	/**
	 * Returns rbac manager interface
	 * @return \yii\rbac\ManagerInterface
	 */
	protected static function authManager()
	{
		return Yii::$app->authManager;
	}


	/**
	 * Must return array of permissions (key is permission name, value is permission description).
	 * @return array
	 */
	abstract protected function permissionConfig();


	/**
	 * Must return array of roles
	 * (index 0 - role name, index 1 - array of permission name)
	 * @return array
	 */
	abstract protected function roleConfig();


	/**
	 * Must return identity object by login
	 * @return IdentityInterface
	 */
	abstract public function findIdentityByLogin($login);

	/**
	 * Must return identity object by ID
	 */
	abstract public function findIdentityById($userId);




	/**
	 * Performs the initial RBAC configuration (remembers the roles belonging to users, deletes all data,
	 * adds default roles that are specified in the method, restores the ownership of roles to users).
	 * You can use it after adding new roles to the method, or removing an unnecessary role.
	 */
	public function actionConfigure()
	{
		print "\n";

		$auth = static::authManager();


		print "Get user roles\n";
		$roles = $auth->getRoles();
		$appRoles = [];
		foreach($roles as $role)
		{
			$appRoles[ $role->name ] = $auth->getUserIdsByRole($role->name);
		}
		print "Remove all permissions\n";
		$auth->removeAll();

		print "Configure permissions:\n";

		$permisions = [];
		foreach($this->permissionConfig() as $permissionName => $permissionDescription)
		{
			$permisions[$permissionName] = $auth->createPermission($permissionName);
			$permisions[$permissionName]->description = $permissionDescription;
			$auth->add($permisions[$permissionName]);
			print " - add $permissionName\n";
		}

		print "Configure roles:\n";

		foreach($this->roleConfig() as $roleName => $roleConfig)
		{
			$role = $auth->createRole($roleName);
			if(isset($roleConfig[0]))
			{
				$role->description = $roleConfig[0];
			}
			$auth->add($role);
			print " - add role $roleName\n";

			if(isset($roleConfig[1]) and is_array($roleConfig[1]))
			{
				foreach($roleConfig[1] as $permissionName)
				{
					if(isset($permisions[$permissionName]))
					{
						$auth->addChild($role, $permisions[$permissionName]);
					}
				}
			}
		}

		print "Configure: assign roles to users\n";
		foreach($appRoles as $roleName => $userIds)
		{
			foreach($userIds as $userId)
			{
				$role = $auth->getRole($roleName);

				if(!is_null($role))
				{
					$auth->assign($role, $userId);
				}
			}
		}

		print "\n";

		return ExitCode::OK;
	}


	/**
	 * Binds a role to a user.
	 * @param string $userLogin login of user
	 * @param string $rolename name of role
	 */
	public function actionAssign($rolename, $userLogin)
	{
		print "\n";
		$auth = static::authManager();
		$role = $auth->getRole($rolename);

		if(is_null($role))
		{
			print "Role [$rolename] is not found.\n\n";
			return \yii\console\ExitCode::DATAERR;
		}

		$user = $this->findIdentityByLogin($userLogin);

		if(is_null($user))
		{
			print "User [$userLogin] is not found.\n\n";
			return ExitCode::DATAERR;
		}
		$userId = $user->getId();


		$roleUsers = $auth->getUserIdsByRole($rolename);

		if(in_array($userId, $roleUsers))
		{
			print "User [$userLogin] already has a role [$rolename].\n\n";
			return ExitCode::DATAERR;
		}
		else
		{
			if($auth->assign($role, $userId))
			{
				print "User [$userLogin] assign to role [$rolename].\n\n";
			}
		}
		return ExitCode::OK;
	}

	/**
	 * Removes the binding of the role to the user
	 * @param string $rolename
	 * @param string $userLogin
	 */
	public function actionUnassign($rolename, $userLogin)
	{
		print "\n";


		$identity = $this->findIdentityByLogin($userLogin);

		if(is_null($identity))
		{
			print "User [$userLogin] is not found.\n\n";
			return ExitCode::DATAERR;
		}

		$userId = $identity->getId();

		$auth = static::authManager();
		$role = $auth->getRole($rolename);

		if(is_null($role))
		{
			print "Role [$rolename] is not found.\n\n";
			return ExitCode::DATAERR;
		}

		if($auth->revoke($role, $userId))
		{
			print "Revoked [$rolename] from user [$userLogin].\n";
		}
		else
		{
			print "The user [$userLogin] is not binded with the role [$rolename].\n";
		}

		print "\n";
		return ExitCode::OK;
	}


	/**
	 * Removes the bindings of all the roles to the user
	 * @param string $userLogin
	 */
	public function actionUnassignAll($userLogin)
	{
		print "\n";

		$identity = $this->findIdentityByLogin($userLogin);

		if(is_null($identity))
		{
			print "User [$userLogin] is not found.\n\n";
			return ExitCode::DATAERR;
		}

		$userId = $identity->getId();

		$auth = static::authManager();

		if($auth->revokeAll($userId))
		{
			print "Revoked all roles from user [$userLogin].\n";
		}
		else
		{
			print "Unfound association with user [$userLogin].\n";
		}

		print "\n";
		return ExitCode::OK;
	}


	/**
	 * Displays a list of roles in this application.
	 */
	public function actionRoles()
	{
		$auth = static::authManager();
		$roles = $auth->getRoles();

		print "\nRoles:\n";

		if(empty($roles))
		{
			print "empty\n";
		}
		else
		{
			foreach($roles as $role)
			{
				print $role->name."\t";

				if(!empty($role->description))
				{
					print $role->description;
				}
				print "\n";
			}
		}

		print "\n";

		return ExitCode::OK;
	}


	/**
	 * Role users list
	 * @param string $rolename
	 */
	public function actionRoleUsers($rolename)
	{
		print "\n";

		$auth = static::authManager();
		$role = $auth->getRole($rolename);

		if(is_null($role))
		{
			print "Role [$rolename] is not found.\n\n";
			return ExitCode::DATAERR;
		}

		$usersIds = $auth->getUserIdsByRole($rolename);

		print "\nUsers of role [$rolename]:\n";

		if(empty($usersIds))
		{
			print "...no users\n";
		}
		else
		{
			foreach($usersIds as $userId)
			{
				$user = $this->findIdentityById($userId);

				if($user)
				{
					if(method_exists($user, 'toArray'))
					{
						$userArr = $user->toArray();
						print " - ".implode("\t", $userArr)."\n";
					}
					else
					{
						print " - ".$user->getId()." (add a method ".get_class($user)."::toArray() for more information)\n";
					}
				}
			}
		}

		print "\n";
		return ExitCode::OK;
	}

	/**
	 * Displays permission config and role config
	 */
	public function actionShowConfig()
	{
		print "\n";
		print "Permission config:\n";
		print_r($this->permissionConfig());
		print "\n";
		print "Role config:\n";
		print_r($this->roleConfig());
		print "\n";
	}


	/**
	 * Displays a list of roles and users
	 */
	public function actionShow()
	{
		print "\n";

		$auth = static::authManager();
		$roles = $auth->getRoles();


		if($roles) foreach($roles as $role)
		{
			print $role->name." (".$role->description.")\n";

			$roleUsers = $auth->getUserIdsByRole($role->name);

			if($roleUsers) foreach($roleUsers as $userId)
			{
				$user = $this->findIdentityById($userId);

				if($user)
				{
					if(method_exists($user, 'toArray'))
					{
						$userArr = $user->toArray();
						print " - ".implode("\t", $userArr)."\n";
					}
					else
					{
						print " - ".$user->getId()." (add a method ".get_class($user)."::toArray() for more information)\n";
					}
				}
			}
			else
			{
				print "...no users\n";
			}

			print "\n";
		}
		else
		{
			print "...roles empty\n";
		}

		print "\n";

		return ExitCode::OK;
	}
}
