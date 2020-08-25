<?php
/**
 * @Author:wa.huang
 * @CreateDate: 2020/8/25 4:46 下午
 */

namespace Yicheng\Application\middleware;


use think\facade\Cache;
use think\facade\Env;
use think\Request;
use Yicheng\Framework\exception\AuthorizeException;
use Yicheng\Framework\service\PermissionsService;

class Permissions
{
    /**
     * 类命令空间前缀
     * @var string
     */
    protected $namespacePrefx = '';

    /**
     * 权限缓存前缀
     * @var string
     */
    protected $permissionCacehPrefx = 'permission_';
    /**
     * 角色缓存前缀
     * @var string
     */
    protected $roleCacehPrefx = 'role_';

    protected $adminId = 'adminId';

    public function handle(Request $request, \Closure $next)
    {
        $controller = $this->namespacePrefx . $request->controller();
        $service    = (new PermissionsService($controller));
        // 检查权限
        // 读取缓存（缓存需要登录时自行存储）
        $permissions = Cache::get($this->permissionCacehPrefx . session(Env::get($this->adminId)));
        if (empty($permissions)) {
            $permissions = [];
        }
        $roles = Cache::get($this->roleCacehPrefx . session(Env::get($this->adminId)));
        if (empty($roles)) {
            $roles = [];
        }
        // 设置权限、角色数据
        if (!$service->setPermissions($permissions)->setRole($roles)->checkPermission($request->action())) {
            throw new AuthorizeException();     // 抛出授权异常
        }
    }
}