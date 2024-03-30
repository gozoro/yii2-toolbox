<?php



namespace gozoro\toolbox\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\console\Exception;

use yii\rbac\Permission;
use yii\rbac\Role;
use yii\rbac\Rule;




/**
 *
 * RBAC configuration and managment tool in the console.
 *
  * Example RBAC config:
 *	```
 * [
 *    // Permission list
 *    'permissions' =>[
 *        'read' => 'permissions for read something',   // name => description
 *        'write' => 'permissions for write something', // name => description
 *    ],
 *
 *    // Role list
 *    'roles' => [
 *        'role_admin' => 'Administrator role', // name => description
 *        'role_manager' => 'Manager role',     // name => description
 *    ],
 *
 *	   // Rule list
 *	  'rules' => [
 *			\WriteRule:class => ['write'], // rule for permission "write"
 *	   ],
 *
 *    // Mapping roles to permissions
 *    'access' => [
 *        'role_admin' => ['read', 'write'], // array of permissions
 *        'role_manager' => ['read'],
 *    ],
 * ]
 * ```
 */
abstract class RbacController extends Controller
{
	public $defaultAction = 'show';



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
			throw new Exception("RBAC config $configfile is not exist.");
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




	protected function initVerify()
	{
 		$config = $this->getConfig();

		if( isset($config['permissions']) )
		{
			if(is_array($config['permissions']))
			{
				foreach($config['permissions'] as $key => $val)
				{
					if(\is_string($key) and \is_string($val))
					{
						// ok
					}
					elseif($val instanceof Permission)
					{
						// ok
					}
					elseif(\is_string($key) and \is_array($val))
					{
						// ok
					}
					elseif(!\is_string($key) and \is_array($val) )
					{
						if(empty($val['name']))
							throw new Exception("The permissions[$key] value array must have the [name] key.");
					}
					else
					{
						throw new Exception("The permissions[$key] value must be string or array or instance of Permission.");
					}
				}
			}
			else
			{
				throw new Exception("The [permissions] must be array.");
			}
		}
		else
			throw new Exception("The [permissions] is missing in the RBAC config.");


		if( isset($config['roles']) )
		{
			if(\is_array($config['roles']))
			{
				foreach($config['roles'] as $key => $val)
				{
					if(\is_string($key) and \is_string($val))
					{
						// ok
					}
					elseif($val instanceof Role)
					{
						// ok
					}
					elseif(\is_string($key) and \is_array($val))
					{
						// ok
					}
					elseif(!\is_string($key) and \is_array($val) )
					{
						if(empty($val['name']))
							throw new Exception("The roles[$key] value array must have the [name] key.");
					}
					else
					{
						throw new Exception("The roles[$key] value must be string or array or instance of Role.");
					}
				}
			}
			else
			{
				throw new Exception("The [roles] must be array.");
			}

		}
		else
			throw new Exception("The [roles] is missing in the RBAC config.");



		if(isset($config['rules']))
		{
			if(\is_array($config['rules']))
			{
				foreach($config['rules'] as $key => $val)
				{
					if(is_string($key))
					{
						if(!\class_exists($key))
						{
							throw new Exception("Class [$key] not found.");
						}
					}
					else
						throw new Exception("The rule keys must be string (rule class name).");


					$val = (array)$val;
					foreach($val as $item)
					{
						if(!\is_string($item))
							throw new Exception("The rules[$key] value must be string or array of strings.");
					}
				}
			}
			else
			{
				throw new Exception("The [rules] must be array (key is rule class name).");
			}
		}


		if( isset($config['access']) )
		{
			if(\is_array($config['access']))
			{
				foreach($config['access'] as $key => $val)
				{
					if(!is_string($key))
						throw new Exception("The access key must be string (permission name or role name).");

					$val = (array)$val;
					foreach($val as $item)
					{
						if(!\is_string($item))
							throw new Exception("The access[$key] value must be string or array of strings (permission name or role name).");
					}
				}
			}
			else
			{
				throw new Exception("The [access] must be array.");
			}
		}
		else
			throw new Exception("The [access] is missing in the RBAC config.");
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

		$this->initVerify();


		print "Get user roles: ";
		foreach($authManager->getRoles() as $role)
		{
			$userRoles[ $role->name ] = $authManager->getUserIdsByRole($role->name);
		}
		print "OK\n";

		print "Clear RBAC data: ";
		$authManager->removeAll();
		print "OK\n";

		$rules       = [];
		$permissions = [];
		$roles       = [];



		if(isset($config['rules']))
		{
			print "Configure rules:\n";
			foreach($config['rules'] as $ruleClass => $items)
			{
				if(\is_string($ruleClass))
				{
					$rule = new $ruleClass();
				}
				elseif($ruleClass instanceof Rule)
				{
					$rule = $ruleClass;
				}
				else
				{
					throw new Exception("The rule key must be string of class name.");
				}


				$authManager->add($rule);
				print " + add rule [".$rule->name."]\n";

				$items = (array)$items;
				foreach($items as $item)
				{
					if(\is_string($item))
					{
						$rules[ $item ] = $rule;
					}
					else
					{
						print " - error: item of rule [".$rule->name."] must be string.\n";
					}
				}
			}
		}



		print "Configure permissions:\n";
		foreach($config['permissions'] as $permissionName => $permission)
		{
			if( \is_string($permissionName) and \is_string($permission))
			{
				$p = $authManager->createPermission($permissionName);
				$p->description = $permission;
				$permission = $p;
			}
			elseif($permission instanceof Permission)
			{
				// ok
			}
			elseif(\is_array($permission))
			{
				$p = new Permission();

				if(!empty($permission['description']))
					$p->description = $permission['description'];

				if(!empty($permission['name']))
					$p->name = $permission['name'];
				else
					$p->name = $permissionName;

				if(!empty($permission['data']))
					$p->data = $permission['data'];


				if( !isset($permission['description']) and !isset($permission['name']) and !isset($permission['data']) )
					$p->data = $permission;

				$permission = $p;
			}
			else
			{
				throw new Exception("The permission value must be string or instance of Permission (permissions[$permissionName]).");
			}

			$withRule = "";
			if(isset($rules[$permissionName]))
			{
				$permission->ruleName = $rules[$permissionName]->name;
				$withRule = " with rule [".$permission->ruleName."]";
			}

			$authManager->add( $permission );
			$permissions[$permissionName] = $permission;
			print " + add permission [".$permission->name."]$withRule - ".$permission->description."\n";
		}


		print "Configure roles:\n";
		foreach($config['roles'] as $roleName => $role)
		{
			if(\is_string($role))
			{
				$r = $authManager->createRole($roleName);
				$r->description = $role;
				$role = $r;
			}
			elseif($role instanceof Role)
			{
				$role->name = $roleName;
			}
			elseif(\is_array($role))
			{
				$r = new Role();

				if(!empty($role['description']))
					$r->description = $role['description'];

				if(!empty($role['name']))
					$r->name = $role['name'];
				else
					$r->name = $roleName;

				if(!empty($role['data']))
					$r->data = $role['data'];


				if( !isset($role['description']) and !isset($role['name']) and !isset($role['data']) )
					$r->data = $role;

				$role = $r;
			}
			else
			{
				throw new Exception("The role value must be string or instance of Role (roles[$roleName]).");
			}

			$withRule = "";
			if(isset($rules[$roleName]))
			{
				$role->ruleName = $rules[$roleName]->name;
				$withRule = " with rule [".$role->ruleName."]";
			}

			$role->name = $roleName;


			$authManager->add( $role );
			$roles[ $roleName ] = $role;
			print " + add role [".$role->name."]$withRule - ".$role->description."\n";
		}



		print "Configure access:\n";
		foreach($config['access'] as $itemName => $access)
		{
			if(isset($permissions[$itemName]))
			{
				$item = $permissions[$itemName];

				$access = (array)$access;

				foreach($access as $accessItemName)
				{
					if(\is_string($accessItemName))
					{
						if(isset($permissions[$accessItemName]))
						{
							$authManager->addChild($item, $permissions[$accessItemName]);
							print " + permission [".$item->name."] has child permission [".$permissions[$accessItemName]->name."]\n";
						}
						elseif(isset($roles[$accessItemName]))
						{
							$authManager->addChild($item, $roles[$accessItemName]);
							print " + permission [".$item->name."] has child role [".$roles[$accessItemName]->name."]\n";
						}
						else
						{
							print " - error: item [$accessItemName] not found in RBAC config.\n";
						}
					}
					else
					{
						print " - error: access item is not a string.";
					}
				}
			}
			elseif(isset($roles[$itemName]))
			{
				$item = $roles[$itemName];

				$access = (array)$access;

				foreach($access as $accessItemName)
				{
					if(\is_string($accessItemName))
					{
						if(isset($permissions[$accessItemName]))
						{
							$authManager->addChild($item, $permissions[$accessItemName]);
							print " + role [".$item->name."] has child permission [".$permissions[$accessItemName]->name."]\n";
						}
						elseif(isset($roles[$accessItemName]))
						{
							$authManager->addChild($item, $roles[$accessItemName]);
							print " + role [".$item->name."] has child role [".$roles[$accessItemName]->name."]\n";
						}
						else
						{
							print " - error: item [$accessItemName] not found in RBAC config.\n";
						}
					}
					else
					{
						print " - error: access item is not a string.";
					}
				}

			}
			else
			{
				print " - error: item name [$itemName] not found.\n";
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
				return ExitCode::DATAERR;
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
				return ExitCode::DATAERR;
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
