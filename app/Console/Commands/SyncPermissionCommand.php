<?php

namespace App\Console\Commands;

use App\Repositories\Permission\PermissionRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class SyncPermissionCommand extends Command
{

    public function __construct(private PermissionRepository $permissionRepository) {
        parent::__construct();
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sync-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for sync permissions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $permissionsFromControllers = [];
        $controllers = \File::allFiles(app_path('Http\Controllers'));

        foreach ($controllers as $controller) {
            $classPath = '\App\Http\Controllers\\' . $controller->getFilenameWithoutExtension();
            $newClass = App::make($classPath);

            foreach ($newClass::$actionPermissionMap as $key => $value) {
                $permissionsFromControllers[] = $value;
            }
        }

        $permissions = $this->permissionRepository->all();

        foreach ($permissions as $permissionModel) {
            $isPermissionExists = false;
            if (array_search($permissionModel->name, $permissionsFromControllers)) {
                $isPermissionExists = true;
            }
            if (!$isPermissionExists){
                $permissionModel->delete();
            }
        }

        foreach ($permissionsFromControllers as $permission) {
            $isPermissionExists = false;

            foreach ($permissions as $permissionModel) {
                if ($permissionModel->name == $permission) {
                    $isPermissionExists = true;
                    break;
                }
            }

            if (!$isPermissionExists){
                $this->permissionRepository->create([
                    'name' => $permission->name
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
