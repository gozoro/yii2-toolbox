<?php



namespace gozoro\toolbox\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\web\IdentityInterface;
use yii\helpers\ArrayHelper;


/**
 *
 * RBAC configuration and managment tool in the console.
 *
  * Example RBAC config:<br />
 *
 * [<br />
 *    'permissions' =>[<br />
 *        'read' => 'permissions for read something',   // name => description<br />
 *        'write' => 'permissions for write something', // name => description<br />
 *    ],<br />
 *
 *    'roles' => [<br />
 *        'role_admin' => 'Administrator role', // name => description<br />
 *        'role_manager' => 'Manager role',     // name => description<br />
 *    ],<br />
 *
 *    'rules' => [<br />
 *        'role_admin' => '/./',      // regexp pattern for all permissions<br />
 *        'role_manager' => ['read'], // array of permissions<br />
 *    ],<br />
 * ]<br />
 *
 */
abstract class RbacController extends Controller
{
	public $defaultAction = 'show';


	/**
	 * Must return identity object by username
	 * @param string $username
	 * @return IdentityInterface
	 */
	abstract public function findIdentityByUsername($username);

	/**
	 * Must return identity object by ID
	 * @param int|string $userId
	 * @return IdentityInterface
	 */
	abstract public function findIdentityById($userId);


	/**
	 * Returns rbac manager
	 * @return \yii\rbac\ManagerInterface
	 */
	public function getAuthManager()
	{
		return Yii::$app->authManager;
	}

	/**
	 * Returns path to RBAC-config
	 * @return string
	 */
	public function getConfigPath()
	{
		return '@app/config/rbac.php';
	}


	/**
	 * Returns config array
	 * @return array
	 * @throws \yii\console\Exception
	 */
	public function getConfig()
	{
		$path = $this->getConfigPath();
		$configfile = Yii::getAlias($path);

		if(file_exists($configfile))
		{
			return require($configfile);
		}
		else
		{
			throw new \yii\console\Exception("RBAC config $configfile is not exist.");
		}
	}


	/**
	 * Displays config
	 */
	public function actionShowConfig()
	{
		$config = $this->getConfig();

		print "RBAC config:\n\n";

		print_r($config);

		print "\n\n";
		return ExitCode::OK;
	}


	/**
	 * Performs the initial RBAC configuration (remembers the roles belonging to users, deletes all data,
	 * adds default roles that are specified in the method, restores the ownership of roles to users).
	 * You can use it after adding new roles to the method, or removing an unnecessary role.
	 */
	public function actionInit()
	{
		$config      = $this->getConfig();
		$authManager = $this->getAuthManager();
		$userRoles   = [];

		if(isset($config['permissions']) and is_array($config['permissions']))
		{
			$permissionConfig = [];
			foreach($config['permissions'] as $key => $val)
			{
				if(!is_string($key) or !is_string($val))
					throw new \yii\console\Exception("The permission names must be string (permissions[$key] => $val).");

				$permissionConfig[$key] = $val;
			}
		}
		else
			throw new \yii\console\Exception("The [permissions] is missing in the RBAC config.");

		if(isset($config['roles']) and is_array($config['roles']))
		{
			$roleConfig = [];
			foreach($config['roles'] as $key => $val)
			{
				if(!is_string($key) or !is_string($val))
					throw new \yii\console\Exception("The role names must be string (roles[$key] => $val).");

				$roleConfig[$key] = $val;
			}
		}
		else
			throw new \yii\console\Exception("The [roles] is missing in the RBAC config.");

		if(isset($config['rules']) and is_array($config['rules']))
		{
			$ruleConfig = [];
			foreach($config['rules'] as $key => $val)
			{
				if(!is_string($key))
					throw new \yii\console\Exception("The rule key must be string.");

				if(!is_string($val) and !is_array($val))
					throw new \yii\console\Exception("The rule value must be string (as pattern of permission names) or not associative array permission names.");

				if(is_array($val) and ArrayHelper::isAssociative($val))
				{
					throw new \yii\console\Exception("The rule value must be not associative array");
				}

				$ruleConfig[$key] = $val;
			}
		}
		else
			throw new \yii\console\Exception("The [rules] is missing in the RBAC config.");




		print "Get user roles: ";
		foreach($authManager->getRoles() as $role)
		{
			$userRoles[ $role->name ] = $authManager->getUserIdsByRole($role->name);
		}
		print "OK\n";



		print "Clear RBAC data: ";
		$authManager->removeAll();
		print "OK\n";

		print "Configure permissions:\n";
		$permisions = [];
		foreach($permissionConfig as $permissionName => $permissionDescription)
		{
			$permisions[$permissionName] = $authManager->createPermission($permissionName);
			$permisions[$permissionName]->description = $permissionDescription;
			$authManager->add($permisions[$permissionName]);
			print " + $permissionName ($permissionDescription)\n";
		}


		print "Configure roles:\n";
		$roles = [];
		foreach($roleConfig as $roleName => $roleDescription)
		{
			$roles[$roleName] = $authManager->createRole($roleName);
			$roles[$roleName]->description = $roleDescription;
			$authManager->add($roles[$roleName]);
			print " + $roleName ($roleDescription)\n";
		}

		print "Configure rules:\n";
		foreach($ruleConfig as $roleName => $rule)
		{
			if(isset($roles[$roleName]))
			{
				$role = $roles[$roleName];
				print " Role $roleName:\n";

				if(is_string($rule))
				{
					$pattern = $rule;
					foreach($permisions as $permissionName => $permission)
					{
						if(preg_match($pattern, $permissionName))
						{
							$authManager->addChild($role, $permission);
							print " + permission [$permissionName]\n";

						}
					}
				}
				elseif(is_array($rule))
				{
					foreach($rule as $itemName)
					{
						if(isset($permisions[$itemName]))
						{
							$authManager->addChild($role, $permisions[$itemName]);
							print " + permission [$itemName]\n";
						}
						elseif(isset($roles[$itemName]))
						{
							if($roleName == $itemName)
							{
								print " - error: the rule [$roleName] can't be linked to itself.\n";
							}
							else
							{
								$authManager->addChild($role, $roles[$itemName]);
								print " + role [$itemName]\n";
							}
						}
						else
						{
							print " - error: the rule [$roleName] can't be linked to item [$itemName].\n";
						}
					}
				}
				else
				{
					print " - error: the rule [$roleName] in not valid.\n";
				}
			}
			else
			{
				print " - error: the rule [$roleName] doesn't match any roles.\n";
			}
		}


		print "Configure users: ";
		foreach($userRoles as $roleName => $userIds)
		{
			$role = $authManager->getRole($roleName);

			if(!is_null($role))
			foreach($userIds as $userId)
			{
				$authManager->assign($role, $userId);
			}
		}
		print "OK\n";



		print "\n";
		return ExitCode::OK;
	}


	/**
	 * Displays a list of roles and users
	 */
	public function actionShow()
	{
		$authManager = $this->getAuthManager();
		$roles       = $authManager->getRoles();


		if($roles) foreach($roles as $role)
		{
			print $role->name." (".$role->description.")\n";

			$roleUsers = $authManager->getUserIdsByRole($role->name);

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


	/**
	 * Assigns a role (or a permission) to a user.
	 * @param string $rolename role name or permission name
	 * @param string $username user name
	 */
	public function actionAssign($rolename, $username)
	{
		print "\n";
		$authManager = $this->getAuthManager();
		$perm        = $authManager->getPermission($rolename);
		$role        = $authManager->getRole($rolename);
		$obj         = [];

		if($perm)
		{
			$type = 'permission';
			$item = $perm;
		}

		if($role)
		{
			$type = 'role';
			$item = $role;
		}

		if(empty($item))
		{
				print "Role [$rolename] is not found.\n\n";
				print "Permission [$rolename] is not found.\n\n";
				return \yii\console\ExitCode::DATAERR;
		}


		$identity = $this->findIdentityByUsername($username);

		if(is_null($identity))
		{
			print "User [$username] is not found.\n\n";
			return ExitCode::DATAERR;
		}

		$userId = $identity->getId();


		if($type == 'role')
		{
			$userRoles = $authManager->getRolesByUser($userId);
			if(isset($userRoles[$rolename]))
			{
				print "User [$username] already has a role [$rolename].\n\n";
				return ExitCode::DATAERR;
			}
		}
		else
		{
			$userPermissions = $authManager->getPermissionsByUser($userId);
			if(isset($userPermissions[$rolename]))
			{
				print "User [$username] already has a permission [$rolename].\n\n";
				return ExitCode::DATAERR;
			}
		}



		if($authManager->assign($item, $userId))
		{
			print "The user [$username] was successfully assigned to the $type [$rolename].\n\n";
			return ExitCode::OK;
		}
		else
		{
			print "The $type failed assigned.\n";
			return ExitCode::UNSPECIFIED_ERROR;
		}

		return ExitCode::OK;
	}


	/**
	 * Revokes role or permission from a user
	 * @param string $rolename role name or permission name
	 * @param string $username
	 */
	public function actionUnassign($rolename, $username)
	{
		print "\n";

		$authManager = $this->getAuthManager();
		$perm        = $authManager->getPermission($rolename);
		$role        = $authManager->getRole($rolename);
		$item        = null;
		$type        = null;

		if($perm)
		{
			$type = 'permission';
			$item = $perm;
		}

		if($role)
		{
			$type = 'role';
			$item = $role;
		}

		if(empty($item))
		{
				print "Role [$rolename] is not found.\n\n";
				print "Permission [$rolename] is not found.\n\n";
				return \yii\console\ExitCode::DATAERR;
		}


		$identity = $this->findIdentityByUsername($username);

		if(is_null($identity))
		{
			print "User [$username] is not found.\n\n";
			return ExitCode::DATAERR;
		}

		$userId = $identity->getId();


		if($authManager->revoke($item, $userId))
		{
			print "The $type [$rolename] successfully revoked from user [$username].\n";
			return ExitCode::OK;
		}
		else
		{
			print "The $type failed revoked.\n";
			return ExitCode::UNSPECIFIED_ERROR;
		}
	}


	/**
	 * Revokes all roles from a user
	 * @param string $username
	 */
	public function actionUnassignAll($username)
	{
		$authManager = $this->getAuthManager();
		$identity    = $this->findIdentityByUsername($username);

		if(is_null($identity))
		{
			print "User [$username] is not found.\n\n";
			return ExitCode::DATAERR;
		}

		$userId = $identity->getId();

		if($authManager->revokeAll($userId))
		{
			print "Revoked all roles from user [$username].\n";
		}
		else
		{
			print "Failed revoke from [$username].\n";
		}

		print "\n";
		return ExitCode::OK;
	}


	/**
	 * Displays a list of roles in this application
	 */
	public function actionRoles()
	{
		$authManager = $this->getAuthManager();
		$roles = $authManager->getRoles();

		print "Roles:\n";

		if(empty($roles))
		{
			print "...empty\n";
		}
		else
		{
			foreach($roles as $role)
			{
				print $role->name;

				if(!empty($role->description))
				{
					print "\t".$role->description;
				}
				print "\n";
			}
		}

		print "\n";

		return ExitCode::OK;
	}


	/**
	 * Displays roles and permissions of user
	 * @param string $username
	 */
	public function actionShowUser($username)
	{
		$authManager = $this->getAuthManager();
		$identity    = $this->findIdentityByUsername($username);

		if(is_null($identity))
		{
			print "User [$username] is not found.\n\n";
			return ExitCode::DATAERR;
		}

		$userId = $identity->getId();

		$roles = $authManager->getRolesByUser($userId);
		$permissions = $authManager->getPermissionsByUser($userId);

		print "Roles of [$username]:\n";
		if($roles) foreach($roles as $role)
		{
			print $role->name;

			if(!empty($role->description))
			{
				print "\t".$role->description;
			}
			print "\n";
		}
		else
		{
			print "...empty\n";
		}

		print "\n";

		print "Permissions of [$username]:\n";
		if($permissions) foreach($permissions as $perm)
		{
			print $perm->name;

			if(!empty($perm->description))
			{
				print "\t".$perm->description;
			}
			print "\n";
		}
		else
		{
			print "...empty\n";
		}
		return ExitCode::OK;
	}
}
