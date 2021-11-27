<?php

namespace Tiagoandrepro\Lmsquid;

use Tiagoandrepro\Lmsquid\Interfaces\ModuleRegister;

class PackageManager implements ModuleRegister
{

    public function configure(): array
    {
        return [
            'name' => 'Manager',
            'vendor' => 'tiagoandrepro/package-manager',
            'description' => 'Gerenciador de pacotes',
        ];
    }

    public function depends(): null|array
    {
        return null;
    }
}
